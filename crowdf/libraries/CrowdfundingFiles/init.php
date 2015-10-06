<?php
/**
* @package      CrowdfundingFiles
* @subpackage   Library
* @author       Todor Iliev
* @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
* @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
*/

defined('JPATH_PLATFORM') or die;

if (!defined("CROWDFUNDINGFILES_PATH_COMPONENT_ADMINISTRATOR")) {
    define("CROWDFUNDINGFILES_PATH_COMPONENT_ADMINISTRATOR", JPATH_ADMINISTRATOR . "/components/com_crowdfundingfiles");
}

if (!defined("CROWDFUNDINGFILES_PATH_COMPONENT_SITE")) {
    define("CROWDFUNDINGFILES_PATH_COMPONENT_SITE", JPATH_SITE . "/components/com_crowdfundingfiles");
}

if (!defined("CROWDFUNDINGFILES_PATH_LIBRARY")) {
    define("CROWDFUNDINGFILES_PATH_LIBRARY", JPATH_LIBRARIES . "/crowdfundingfiles");
}

JLoader::registerNamespace('CrowdfundingFiles', JPATH_LIBRARIES);

// Register helpers
JLoader::register("CrowdfundingFilesHelper", CROWDFUNDINGFILES_PATH_COMPONENT_ADMINISTRATOR . "/helpers/crowdfundingfiles.php");

// Register HTML helpers
JHtml::addIncludePath(CROWDFUNDINGFILES_PATH_COMPONENT_SITE . "/helpers/html");
JLoader::register(
    'JHtmlString',
    JPATH_LIBRARIES . "/joomla/html/html/string.php"
);

// Register Observers
JLoader::register(
    "CrowdfundingFilesObserverFile",
    CROWDFUNDINGFILES_PATH_COMPONENT_ADMINISTRATOR .DIRECTORY_SEPARATOR. "tables" .DIRECTORY_SEPARATOR. "observers" .DIRECTORY_SEPARATOR. "file.php"
);
JObserverMapper::addObserverClassToClass('CrowdfundingFilesObserverFile', 'CrowdfundingFilesTableFile', array('typeAlias' => 'com_crowdfundingfiles.file'));
