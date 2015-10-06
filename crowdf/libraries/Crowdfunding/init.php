<?php
/**
 * @package      Crowdfunding
 * @subpackage   Library
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined('JPATH_PLATFORM') or die;

if (!defined("CROWDFUNDING_PATH_COMPONENT_ADMINISTRATOR")) {
    define("CROWDFUNDING_PATH_COMPONENT_ADMINISTRATOR", JPATH_ADMINISTRATOR . "/components/com_crowdfunding");
}

if (!defined("CROWDFUNDING_PATH_COMPONENT_SITE")) {
    define("CROWDFUNDING_PATH_COMPONENT_SITE", JPATH_SITE . "/components/com_crowdfunding");
}

if (!defined("CROWDFUNDING_PATH_LIBRARY")) {
    define("CROWDFUNDING_PATH_LIBRARY", JPATH_LIBRARIES . "/Crowdfunding");
}

JLoader::registerNamespace('Crowdfunding', JPATH_LIBRARIES);

// Register some helpers
JLoader::register("CrowdfundingHelper", CROWDFUNDING_PATH_COMPONENT_ADMINISTRATOR . "/helpers/crowdfunding.php");
JLoader::register("CrowdfundingHelperRoute", CROWDFUNDING_PATH_COMPONENT_SITE . "/helpers/route.php");

// Register some Joomla! classes
JLoader::register('JHtmlString', JPATH_LIBRARIES . "/joomla/html/html/string.php");
JLoader::register("JHtmlCategory", JPATH_LIBRARIES . "/joomla/html/html/category.php");

// Include HTML helpers path
JHtml::addIncludePath(CROWDFUNDING_PATH_COMPONENT_SITE . '/helpers/html');

// Register Observers
JLoader::register("CrowdfundingObserverReward", CROWDFUNDING_PATH_COMPONENT_ADMINISTRATOR . "/tables/observers/reward.php");
JObserverMapper::addObserverClassToClass('CrowdfundingObserverReward', 'CrowdfundingTableReward', array('typeAlias' => 'com_crowdfunding.reward'));

// Prepare logger
$registry = Joomla\Registry\Registry::getInstance("com_crowdfunding");
/** @var  $registry Joomla\Registry\Registry */

$registry->set("logger.table", "#__crowdf_logs");
$registry->set("logger.file", "com_crowdfunding.php");

// Load library language
$lang = JFactory::getLanguage();
$lang->load('lib_crowdfunding', CROWDFUNDING_PATH_LIBRARY);

// Register class aliases.
JLoader::registerAlias('CrowdfundingCategories', '\\Crowdfunding\\Categories');
