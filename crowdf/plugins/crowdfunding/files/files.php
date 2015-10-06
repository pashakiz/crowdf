<?php
/**
 * @package         CrowdfundingFiles
 * @subpackage      Plugins
 * @author          Todor Iliev
 * @copyright       Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license         http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// no direct access
defined('_JEXEC') or die;

jimport("Crowdfunding.init");
jimport("CrowdfundingFiles.init");

/**
 * Crowdfunding Files Plugin
 *
 * @package        CrowdfundingFiles
 * @subpackage     Plugins
 */
class plgCrowdfundingFiles extends JPlugin
{
    protected $autoloadLanguage = true;

    /**
     * @var JApplicationSite
     */
    protected $app;

    /**
     * @var Joomla\Registry\Registry
     */
    public $params;

    protected $version = "2.0";

    /**
     * This method prepares a code that will be included to step "Extras" on project wizard.
     *
     * @param string    $context This string gives information about that where it has been executed the trigger.
     * @param object    $item    A project data.
     * @param Joomla\Registry\Registry $params  The parameters of the component
     *
     * @return null|string
     */
    public function onExtrasDisplay($context, &$item, &$params)
    {
        if (strcmp("com_crowdfunding.project.extras", $context) != 0) {
            return null;
        }

        if ($this->app->isAdmin()) {
            return null;
        }

        $doc = JFactory::getDocument();
        /**  @var $doc JDocumentHtml */

        // Check document type
        $docType = $doc->getType();
        if (strcmp("html", $docType) != 0) {
            return null;
        }
        
        if (empty($item->user_id)) {
            return null;
        }

        // Create a media folder.
        $mediaFolder = CrowdfundingFilesHelper::getMediaFolder();
        if (!JFolder::exists($mediaFolder)) {
            CrowdfundingHelper::createFolder($mediaFolder);
        }

        // Create a media folder for a user.
        $mediaFolder = CrowdfundingFilesHelper::getMediaFolder($item->user_id);
        if (!JFolder::exists($mediaFolder)) {
            CrowdfundingHelper::createFolder($mediaFolder);
        }

        $componentParams = JComponentHelper::getParams("com_crowdfundingfiles");
        /** @var  $componentParams Joomla\Registry\Registry */

        $mediaUri = CrowdfundingFilesHelper::getMediaFolderUri($item->user_id);

        $options = array(
            "project_id" => $item->id,
            "user_id" => $item->user_id
        );

        $files = new CrowdfundingFiles\Files(JFactory::getDbo());
        $files->load($options);

        // Load jQuery
        JHtml::_("jquery.framework");
        JHtml::_("prism.ui.pnotify");
        JHtml::_('prism.ui.fileupload');
        JHtml::_('prism.ui.joomlaHelper');

        // Include the translation of the confirmation question.
        JText::script('PLG_CROWDFUNDING_FILES_DELETE_QUESTION');

        // Get the path for the layout file
        $path = JPath::clean(JPluginHelper::getLayoutPath('crowdfunding', 'files'));

        // Render the login form.
        ob_start();
        include $path;
        $html = ob_get_clean();

        return $html;
    }
}
