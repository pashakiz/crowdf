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

jimport('Prism.init');
jimport('Crowdfunding.init');

/**
 * Crowdfunding Modules plugin
 *
 * @package        Crowdfunding
 * @subpackage     Plugins
 */
class plgSystemCrowdfundingModules extends JPlugin
{
    /**
     * @var Joomla\Registry\Registry
     */
    public $params;
    
    public function onAfterDispatch()
    {
        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        if ($app->isAdmin()) {
            return;
        }

        $document = JFactory::getDocument();
        /** @var $document JDocumentHtml */

        $type = $document->getType();
        if (strcmp("html", $type) != 0) {
            return;
        }

        // It works only for GET and POST requests.
        $method = JString::strtolower($app->input->getMethod());
        if (!in_array($method, array("get", "post"))) {
            return;
        }

        // Check component enabled
        if (!JComponentHelper::isEnabled('com_crowdfunding', true)) {
            return;
        }

        $view   = $app->input->getCmd("view");
        $option = $app->input->getCmd("option");

        $isCrowdfundingComponent = (strcmp($option, "com_crowdfunding") == 0);
        $isDetailsPage           = (strcmp($option, "com_crowdfunding") == 0 and strcmp($view, "details") == 0);

        // Allowed views for the module Crowdfunding Details
        $allowedViewsModuleDetails = array("backing", "embed", "report");
        $allowedViewsModuleFilters = array("discover", "category");

        // Hide some modules if it is not details page.
        if (!$isDetailsPage) {
            $this->hideModule("mod_crowdfundinginfo");
            $this->hideModule("mod_crowdfundingprofile");
            $this->hideModule("mod_crowdfundingreporting");
        }

        // Module Crowdfunding Rewards (mod_crowdfundingrewards).
        if (!$isDetailsPage) {
            $this->hideModule("mod_crowdfundingrewards");
        } else { // Check project type. If the rewards are disable, hide the module.

            $projectId = $app->input->getInt("id");
            if (!empty($projectId)) {

                // Hide the module Crowdfunding Rewards, if rewards are disabled.
                if (!CrowdfundingHelper::isRewardsEnabled($projectId)) {
                    $this->hideModule("mod_crowdfundingrewards");
                }
            }
        }

        // Module Crowdfunding Details (mod_crowdfundingdetails) on backing and embed pages.
        if (!$isCrowdfundingComponent or (strcmp($option, "com_crowdfunding") == 0 and !in_array($view, $allowedViewsModuleDetails))) {
            $this->hideModule("mod_crowdfundingdetails");
        }

        // Module Crowdfunding Filters (mod_crowdfundingfilters).
        if (!$isCrowdfundingComponent or (strcmp($option, "com_crowdfunding") == 0 and !in_array($view, $allowedViewsModuleFilters))) {
            $this->hideModule("mod_crowdfundingfilters");
        }

    }

    protected function hideModule($moduleName)
    {
        $module           = JModuleHelper::getModule($moduleName);
        if (!empty($module->id)) {
            $seed             = substr(md5(uniqid(time() * rand(), true)), 0, 10);
            $module->position = "fp" . JApplicationHelper::getHash($seed);
        }
    }
}
