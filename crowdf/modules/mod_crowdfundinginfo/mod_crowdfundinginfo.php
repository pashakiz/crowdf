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

// If option is not "com_crowdfunding" and view is not "details",
// do not display anything.
if ((strcmp($option, "com_crowdfunding") != 0) or (strcmp($view, "details") != 0)) {
    echo JText::_("MOD_CROWDFUNDINGINFO_ERROR_INVALID_VIEW");
    return;
}

$projectId = $app->input->getInt("id");
if (!$projectId) {
    echo JText::_("MOD_CROWDFUNDINGINFO_ERROR_INVALID_PROJECT");
    return;
}

$componentParams = JComponentHelper::getParams("com_crowdfunding");
/** @var  $componentParams Joomla\Registry\Registry */

$currency     = Crowdfunding\Currency::getInstance(JFactory::getDbo(), $componentParams->get("project_currency"));

$project      = Crowdfunding\Project::getInstance(JFactory::getDbo(), $projectId);

$amount = new Crowdfunding\Amount($componentParams);
$amount->setCurrency($currency);

$fundedAmount = $amount->setValue($project->getGoal())->formatCurrency();

require JModuleHelper::getLayoutPath('mod_crowdfundinginfo', $params->get('layout', 'default'));