<?php
/**
 * @package      Crowdfunding
 * @subpackage   Modules
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// no direct access
defined("_JEXEC") or die;

jimport("Prism.init");
jimport("Crowdfunding.init");
JLoader::register(
    "CrowdfundingRewardsModuleHelper",
    JPATH_ROOT . DIRECTORY_SEPARATOR . "modules" . DIRECTORY_SEPARATOR . "mod_crowdfundingrewards" . DIRECTORY_SEPARATOR . "helper.php"
);

$moduleclassSfx = htmlspecialchars($params->get('moduleclass_sfx'));

$option = $app->input->get("option");
$view   = $app->input->get("view");

// If option is not "com_crowdfunding" and view is not "details",
// do not display anything.
if ((strcmp($option, "com_crowdfunding") != 0) or (strcmp($view, "details") != 0)) {
    echo JText::_("MOD_CROWDFUNDINGREWARDS_ERROR_INVALID_VIEW");
    return;
}

$projectId = $app->input->getInt("id");
if (!$projectId) {
    echo JText::_("MOD_CROWDFUNDINGREWARDS_ERROR_INVALID_PROJECT");
    return;
}
$componentParams = JComponentHelper::getParams("com_crowdfunding");
/** @var  $componentParams Joomla\Registry\Registry */

// Get currency
$currency   = Crowdfunding\Currency::getInstance(JFactory::getDbo(), $componentParams->get("project_currency"));
$amount     = new Crowdfunding\Amount($componentParams);
$amount->setCurrency($currency);

$project = Crowdfunding\Project::getInstance(JFactory::getDbo(), $projectId);

$rewards = $project->getRewards(array("state" => 1));

// Calculate the number of funders.
if ($params->get("display_funders", 0)) {
    $rewards->countReceivers();
}

$additionalInfo = false;
if ($params->get("display_funders", 0) or $params->get("display_claimed", 0) or $params->get("display_delivery_date", 0)) {
    $additionalInfo = true;
}

$layout  = $params->get('layout', 'default');

switch ($layout) {

    case "_:square":
    case "_:thumbnail":

        // Get the folder where the images are saved.
        $userId           = $project->getUserId();
        $rewardsImagesUri = CrowdfundingHelper::getImagesFolderUri($userId);

        JHtml::_("crowdfunding.jqueryFancybox");

        $js  = '
jQuery(document).ready(function() {
    jQuery("a.js-rewards-images-gallery").fancybox();
});';
        $doc = JFactory::getDocument();
        $doc->addScriptDeclaration($js);

        break;

    default:
        break;
}

require JModuleHelper::getLayoutPath('mod_crowdfundingrewards', $layout);