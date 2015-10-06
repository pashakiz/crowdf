<?php
/**
 * @package      CrowdfundingFiles
 * @subpackage   Component
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2014 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// no direct access
defined('_JEXEC') or die;

/**
 * Script file of the component
 */
class pkg_crowdfundingfilesInstallerScript
{
    /**
     * Method to install the component.
     *
     * @param $parent
     *
     * @return void
     */
    public function install($parent)
    {
    }

    /**
     * Method to uninstall the component.
     *
     * @param $parent
     *
     * @return void
     */
    public function uninstall($parent)
    {
    }

    /**
     * Method to update the component.
     *
     * @param $parent
     *
     * @return void
     */
    public function update($parent)
    {
    }

    /**
     * Method to run before an install/update/uninstall method
     *
     * @param $type
     * @param $parent
     *
     * @return void
     */
    public function preflight($type, $parent)
    {
    }

    /**
     * Method to run after an install/update/uninstall method
     *
     * @param $type
     * @param $parent
     *
     * @return void
     */
    public function postflight($type, $parent)
    {
        if (!defined("CROWDFUNDINGFILES_PATH_COMPONENT_ADMINISTRATOR")) {
            define("CROWDFUNDINGFILES_PATH_COMPONENT_ADMINISTRATOR", JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . "components" . DIRECTORY_SEPARATOR . "com_crowdfundingfiles");
        }

        jimport("Prism.init");

        // Register Component helpers
        JLoader::register("CrowdfundingFilesInstallHelper", CROWDFUNDINGFILES_PATH_COMPONENT_ADMINISTRATOR . DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR . "install.php");

        // Start table with the information
        CrowdfundingFilesInstallHelper::startTable();

        // Requirements
        CrowdfundingFilesInstallHelper::addRowHeading(JText::_("COM_CROWDFUNDINGFILES_MINIMUM_REQUIREMENTS"));

        // Display result about verification for GD library
        $title = JText::_("COM_CROWDFUNDINGFILES_GD_LIBRARY");
        $info  = "";
        if (!extension_loaded('gd') and function_exists('gd_info')) {
            $result = array("type" => "important", "text" => JText::_("COM_CROWDFUNDINGFILES_WARNING"));
        } else {
            $result = array("type" => "success", "text" => JText::_("JON"));
        }
        CrowdfundingFilesInstallHelper::addRow($title, $result, $info);

        // Display result about verification for cURL library
        $title = JText::_("COM_CROWDFUNDINGFILES_CURL_LIBRARY");
        $info  = "";
        if (!extension_loaded('curl')) {
            $info   = JText::_("COM_CROWDFUNDINGFILES_CURL_INFO");
            $result = array("type" => "important", "text" => JText::_("JOFF"));
        } else {
            $result = array("type" => "success", "text" => JText::_("JON"));
        }
        CrowdfundingFilesInstallHelper::addRow($title, $result, $info);

        // Display result about verification Magic Quotes
        $title = JText::_("COM_CROWDFUNDINGFILES_MAGIC_QUOTES");
        $info  = "";
        if (get_magic_quotes_gpc()) {
            $info   = JText::_("COM_CROWDFUNDINGFILES_MAGIC_QUOTES_INFO");
            $result = array("type" => "important", "text" => JText::_("JON"));
        } else {
            $result = array("type" => "success", "text" => JText::_("JOFF"));
        }
        CrowdfundingFilesInstallHelper::addRow($title, $result, $info);

        // Display result about verification FileInfo
        $title = JText::_("COM_CROWDFUNDINGFILES_FILEINFO");
        $info  = "";
        if (!function_exists('finfo_open')) {
            $info   = JText::_("COM_CROWDFUNDINGFILES_FILEINFO_INFO");
            $result = array("type" => "important", "text" => JText::_("JOFF"));
        } else {
            $result = array("type" => "success", "text" => JText::_("JON"));
        }
        CrowdfundingFilesInstallHelper::addRow($title, $result, $info);

        // Display result about verification PHP version.
        $title = JText::_("COM_CROWDFUNDINGFILES_PHP_VERSION");
        $info  = "";
        if (version_compare(PHP_VERSION, '5.3.0') < 0) {
            $result = array("type" => "important", "text" => JText::_("COM_CROWDFUNDINGFILES_WARNING"));
        } else {
            $result = array("type" => "success", "text" => JText::_("JYES"));
        }
        CrowdfundingFilesInstallHelper::addRow($title, $result, $info);

        // Display result about verification of installed Prism Library
        $title = JText::_("COM_CROWDFUNDINGFILES_PRISM_LIBRARY");
        $info  = "";
        if (!class_exists("Prism\\Version")) {
            $info   = JText::_("COM_CROWDFUNDINGFILES_PRISM_LIBRARY_DOWNLOAD");
            $result = array("type" => "important", "text" => JText::_("JNO"));
        } else {
            $result = array("type" => "success", "text" => JText::_("JYES"));
        }
        CrowdfundingFilesInstallHelper::addRow($title, $result, $info);

        // Installed extensions

        CrowdfundingFilesInstallHelper::addRowHeading(JText::_("COM_CROWDFUNDINGFILES_INSTALLED_EXTENSIONS"));

        // Crowdfunding Library
        $result = array("type" => "success", "text" => JText::_("COM_CROWDFUNDINGFILES_INSTALLED"));
        CrowdfundingFilesInstallHelper::addRow(JText::_("COM_CROWDFUNDINGFILES_CROWDFUNDINGFILES_LIBRARY"), $result, JText::_("COM_CROWDFUNDINGFILES_LIBRARY"));

        // Plugins

        // Crowdfunding - Files
        $result = array("type" => "success", "text" => JText::_("COM_CROWDFUNDINGFILES_INSTALLED"));
        CrowdfundingFilesInstallHelper::addRow(JText::_("COM_CROWDFUNDINGFILES_CROWDFUNDING_FILES"), $result, JText::_("COM_CROWDFUNDINGFILES_PLUGIN"));

        // Content - Crowdfunding Files
        $result = array("type" => "success", "text" => JText::_("COM_CROWDFUNDINGFILES_INSTALLED"));
        CrowdfundingFilesInstallHelper::addRow(JText::_("COM_CROWDFUNDINGFILES_CONTENT_CROWDFUNDINGFILES"), $result, JText::_("COM_CROWDFUNDINGFILES_PLUGIN"));

        // End table
        CrowdfundingFilesInstallHelper::endTable();

        echo JText::sprintf("COM_CROWDFUNDINGFILES_MESSAGE_REVIEW_SAVE_SETTINGS", JRoute::_("index.php?option=com_crowdfundingfiles"));

        if (!class_exists("Prism\\Version")) {
            echo JText::_("COM_CROWDFUNDINGFILES_MESSAGE_INSTALL_PRISM_LIBRARY");
        }
    }
}
