<?php
/**
* @package      CrowdfundingData
* @subpackage   Library
* @author       Todor Iliev
* @copyright    Copyright (C) 2014 Todor Iliev <todor@itprism.com>. All rights reserved.
* @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
*/

defined('JPATH_PLATFORM') or die;

if (!defined("CROWDFUNDINGDATA_PATH_COMPONENT_ADMINISTRATOR")) {
    define("CROWDFUNDINGDATA_PATH_COMPONENT_ADMINISTRATOR", JPATH_ADMINISTRATOR . "/components/com_crowdfundingdata");
}

if (!defined("CROWDFUNDINGDATA_PATH_COMPONENT_SITE")) {
    define("CROWDFUNDINGDATA_PATH_COMPONENT_SITE", JPATH_SITE . "/components/com_crowdfundingdata");
}

if (!defined("CROWDFUNDINGDATA_PATH_LIBRARY")) {
    define("CROWDFUNDINGDATA_PATH_LIBRARY", JPATH_LIBRARIES . "/CrowdfundingData");
}

JLoader::registerNamespace('CrowdfundingData', JPATH_LIBRARIES);

// Register helpers
JLoader::register("CrowdfundingDataHelper", CROWDFUNDINGDATA_PATH_COMPONENT_ADMINISTRATOR . "/helpers/crowdfundingdata.php");

// Register HTML helpers
JHtml::addIncludePath(CROWDFUNDINGDATA_PATH_COMPONENT_SITE . "/helpers/html");
JLoader::register('JHtmlString', JPATH_LIBRARIES . "/joomla/html/html/string.php");
