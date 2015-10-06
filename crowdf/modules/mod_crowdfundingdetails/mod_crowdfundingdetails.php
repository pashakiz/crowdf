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

$moduleclassSfx = htmlspecialchars($params->get('moduleclass_sfx'));

$option = $app->input->get("option");
$view   = $app->input->get("view");

$allowedViews = array("backing", "embed", "report");

// If option is not "com_crowdfunding" and view is not one of allowed,
// do not display anything.
if ((strcmp($option, "com_crowdfunding") != 0) or (!in_array($view, $allowedViews))) {
    echo JText::_("MOD_CROWDFUNDINGDETAILS_ERROR_INVALID_VIEW");
    return;
}

$projectId = $app->input->getInt("id");
if (!$projectId) {
    return;
}

// Get project
$project = Crowdfunding\Project::getInstance(JFactory::getDbo(), $projectId);
if (!$project->getId()) {
    return;
}

// Get component params
$componentParams = JComponentHelper::getParams("com_crowdfunding");
/** @var  $componentParams Joomla\Registry\Registry */

$socialPlatform  = $componentParams->get("integration_social_platform");
$imageFolder     = $componentParams->get("images_directory", "images/crowdfunding");
$imageWidth      = $componentParams->get("image_width", 200);
$imageHeight     = $componentParams->get("image_height", 200);

// Get currency
$currencyId = $componentParams->get("project_currency");
$currency     = Crowdfunding\Currency::getInstance(JFactory::getDbo(), $componentParams->get("project_currency"));

$amount = new Crowdfunding\Amount($componentParams);
$amount->setCurrency($currency);

// Get social platform and a link to the profile
$socialBuilder     = new Prism\Integration\Profile\Builder(array("social_platform" => $socialPlatform, "user_id" => $project->getUserId()));
$socialBuilder->build();

$socialProfile     = $socialBuilder->getProfile();
$socialProfileLink = (!$socialProfile) ? null : $socialProfile->getLink();

// Get amounts
$fundedAmount = $amount->setValue($project->getGoal())->formatCurrency();
$raised       = $amount->setValue($project->getFunded())->formatCurrency();

// Prepare the value that I am going to display
$fundedPercents = JHtml::_("crowdfunding.funded", $project->getFundedPercent());

$user = JFactory::getUser($project->getUserId());

require JModuleHelper::getLayoutPath('mod_crowdfundingdetails', $params->get('layout', 'default'));