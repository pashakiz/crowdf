<?php
/**
 * @package      Crowdfunding
 * @subpackage   Plugins
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.plugin.plugin');

/**
 * Crowdfunding Manager Plugin
 *
 * @package      Crowdfunding
 * @subpackage   Plugins
 */
class plgContentCrowdfundingManager extends JPlugin
{
    /**
     * Affects constructor behavior. If true, language files will be loaded automatically.
     *
     * @var    boolean
     * @since  3.1
     */
    protected $autoloadLanguage = true;

    /**
     * @var Joomla\Registry\Registry
     */
    public $params;

    protected $currentOption;
    protected $currentView;
    protected $currentTask;

    /**
     * Prepare a code that will be included after content.
     *
     * @param string    $context
     * @param object    $project
     * @param Joomla\Registry\Registry    $params
     * @param int $page
     *
     * @return null|string
     */
    public function onContentAfterDisplay($context, &$project, &$params, $page = 0)
    {
        if ($this->isRestricted($context, $project)) {
            return null;
        }

        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        // Get request data
        $this->currentOption = $app->input->getCmd("option");
        $this->currentView   = $app->input->getCmd("view");
        $this->currentTask   = $app->input->getCmd("task");

        // Include the script that display a dialog for action confirmation.
        $question = (!$project->published) ? JText::_("PLG_CONTENT_CROWDFUNDINGMANAGER_QUESTION_LAUNCH") : JText::_("PLG_CONTENT_CROWDFUNDINGMANAGER_QUESTION_STOP");
        $js = '
jQuery(document).ready(function() {
    jQuery("#js-cfmanager-launch").on("click", function(event) {
        event.preventDefault();

        if (window.confirm("'.$question.'")) {
            window.location = jQuery(this).attr("href");
        }
    });
});';
        $doc = JFactory::getDocument();
        $doc->addScriptDeclaration($js);

        // Generate content
        $content = '<div class="panel panel-default">';

        if ($this->params->get("display_title", 0)) {
            $content .= '<div class="panel-heading">' . JText::_("PLG_CONTENT_CROWDFUNDINGMANAGER_PROJECT_MANAGER") . '</div>';
        }

        if ($this->params->get("display_toolbar", 0)) {
            $content .= $this->getToolbar($project);
        }

        $content .= '<div class="panel-body">';

        if ($this->params->get("display_statistics", 0)) {
            $content .= $this->getStatistics($project);
        }

        $content .= '</div>';
        $content .= '</div>';

        return $content;
    }

    private function isRestricted($context, $project)
    {
        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        if ($app->isAdmin()) {
            return true;
        }

        $doc = JFactory::getDocument();
        /**  @var $doc JDocumentHtml */

        // Check document type
        $docType = $doc->getType();
        if (strcmp("html", $docType) != 0) {
            return true;
        }

        if (strcmp("com_crowdfunding.details", $context) != 0) {
            return true;
        }

        $userId = JFactory::getUser()->id;
        if ($userId != $project->user_id) {
            return true;
        }

        return false;
    }

    private function getToolbar($project)
    {
        // Get current URL.
        $returnUrl = JUri::current();

        // Filter the URL.
        $filter    = JFilterInput::getInstance();
        $returnUrl = $filter->clean($returnUrl);

        $html   = array();
        $html[] = '<div class="cf-pm-toolbar">';

        if ($project->published and !$project->approved) {
            $html[] = '<p class="bg-info">' . JText::_("PLG_CONTENT_CROWDFUNDINGMANAGER_NOT_APPROVED_NOTIFICATION") . '</p>';
        }

        // Edit
        $html[] = '<a href="' . JRoute::_(CrowdfundingHelperRoute::getFormRoute($project->id)) . '" class="btn btn-default" role="button">';
        $html[] = '<span class="glyphicon glyphicon-edit"></span>';
        $html[] = JText::_("PLG_CONTENT_CROWDFUNDINGMANAGER_EDIT");
        $html[] = '</a>';

        if (!$project->published) { // Display "Publish" button
            $html[] = '<a href="' . JRoute::_("index.php?option=com_crowdfunding&task=projects.savestate&id=" . $project->id . "&state=1&" . JSession::getFormToken() . "=1&return=".base64_encode($returnUrl)) . '" class="btn btn-default" role="button" id="js-cfmanager-launch">';
            $html[] = '<span class="glyphicon glyphicon-ok-sign"></span>';
            $html[] = JText::_("PLG_CONTENT_CROWDFUNDINGMANAGER_LAUNCH");
            $html[] = '</a>';

        } else { // Display "Unpublish" button

            $html[] = '<a href="' . JRoute::_("index.php?option=com_crowdfunding&task=projects.savestate&id=" . $project->id . "&state=0&" . JSession::getFormToken() . "=1&return=".base64_encode($returnUrl)) . '" class="btn btn-danger" role="button" id="js-cfmanager-launch">';
            $html[] = '<span class="glyphicon glyphicon-remove-sign"></span>';
            $html[] = JText::_("PLG_CONTENT_CROWDFUNDINGMANAGER_STOP");
            $html[] = '</a>';

        }

        // Manager
        $html[] = '<a href="' . JRoute::_(CrowdfundingHelperRoute::getFormRoute($project->id, "manager")) . '" class="btn btn-default" role="button">';
        $html[] = '<span class="glyphicon glyphicon-wrench"></span>';
        $html[] = JText::_("PLG_CONTENT_CROWDFUNDINGMANAGER_MANAGER");
        $html[] = '</a>';

        $html[] = '</div>';

        return implode("\n", $html);
    }

    public function getStatistics($project)
    {
        $params     = JComponentHelper::getParams("com_crowdfunding");
        /** @var  $params Joomla\Registry\Registry */

        $currency   = Crowdfunding\Currency::getInstance(JFactory::getDbo(), $params->get("project_currency"));
        $amount     = new Crowdfunding\Amount($params, $project->funded);
        $amount->setCurrency($currency);

        $projectData = CrowdfundingHelper::getProjectData($project->id);

        $html = array();

        $html[] = '<div class="panel panel-default">';

        $html[] = '<div class="panel-heading"><h5>' . JText::_("PLG_CONTENT_CROWDFUNDINGMANAGER_STATISTICS") . '</h5></div>';


        $html[] = '         <table class="table table-bordered">';

        // Hits
        $html[] = '             <tr>';
        $html[] = '                 <td>' . JText::_("PLG_CONTENT_CROWDFUNDINGMANAGER_HITS") . '</td>';
        $html[] = '                 <td>' . (int)$project->hits . '</td>';
        $html[] = '             </tr>';

        // Updates
        $html[] = '             <tr>';
        $html[] = '                 <td>' . JText::_("PLG_CONTENT_CROWDFUNDINGMANAGER_UPDATES") . '</td>';
        $html[] = '                 <td>' . JArrayHelper::getValue($projectData, "updates", 0, "integer") . '</td>';
        $html[] = '             </tr>';

        // Comments
        $html[] = '             <tr>';
        $html[] = '                 <td>' . JText::_("PLG_CONTENT_CROWDFUNDINGMANAGER_COMMENTS") . '</td>';
        $html[] = '                 <td>' . JArrayHelper::getValue($projectData, "comments", 0, "integer") . '</td>';
        $html[] = '             </tr>';

        // Funders
        $html[] = '             <tr>';
        $html[] = '                 <td>' . JText::_("PLG_CONTENT_CROWDFUNDINGMANAGER_FUNDERS") . '</td>';
        $html[] = '                 <td>' . JArrayHelper::getValue($projectData, "funders", 0, "integer") . '</td>';
        $html[] = '             </tr>';

        // Raised
        $html[] = '             <tr>';
        $html[] = '                 <td>' . JText::_("PLG_CONTENT_CROWDFUNDINGMANAGER_RAISED") . '</td>';
        $html[] = '                 <td>' . $amount->formatCurrency() . '</td>';
        $html[] = '             </tr>';

        $html[] = '         </table>';

        $html[] = '</div>';

        return implode("\n", $html);
    }
}
