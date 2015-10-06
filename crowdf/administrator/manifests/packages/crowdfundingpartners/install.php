<?php
/**
 * @package      CrowdfundingPartners
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
class pkg_crowdfundingpartnersInstallerScript
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
        if (!defined("CROWDFUNDINGPARTNERS_PATH_COMPONENT_ADMINISTRATOR")) {
            define("CROWDFUNDINGPARTNERS_PATH_COMPONENT_ADMINISTRATOR", JPATH_ADMINISTRATOR . "/components/com_crowdfundingpartners");
        }

        jimport("Prism.init");

        // Register Component helpers
        JLoader::register("CrowdfundingPartnersInstallHelper", CROWDFUNDINGPARTNERS_PATH_COMPONENT_ADMINISTRATOR . "/helpers/install.php");

        // Start table with the information
        CrowdfundingPartnersInstallHelper::startTable();

        // Requirements
        CrowdfundingPartnersInstallHelper::addRowHeading(JText::_("COM_CROWDFUNDINGPARTNERS_MINIMUM_REQUIREMENTS"));

        // Display result about verification for GD library
        $title = JText::_("COM_CROWDFUNDINGPARTNERS_GD_LIBRARY");
        $info  = "";
        if (!extension_loaded('gd') and function_exists('gd_info')) {
            $result = array("type" => "important", "text" => JText::_("COM_CROWDFUNDINGPARTNERS_WARNING"));
        } else {
            $result = array("type" => "success", "text" => JText::_("JON"));
        }
        CrowdfundingPartnersInstallHelper::addRow($title, $result, $info);

        // Display result about verification for cURL library
        $title = JText::_("COM_CROWDFUNDINGPARTNERS_CURL_LIBRARY");
        $info  = "";
        if (!extension_loaded('curl')) {
            $info   = JText::_("COM_CROWDFUNDINGPARTNERS_CURL_INFO");
            $result = array("type" => "important", "text" => JText::_("JOFF"));
        } else {
            $result = array("type" => "success", "text" => JText::_("JON"));
        }
        CrowdfundingPartnersInstallHelper::addRow($title, $result, $info);

        // Display result about verification Magic Quotes
        $title = JText::_("COM_CROWDFUNDINGPARTNERS_MAGIC_QUOTES");
        $info  = "";
        if (get_magic_quotes_gpc()) {
            $info   = JText::_("COM_CROWDFUNDINGPARTNERS_MAGIC_QUOTES_INFO");
            $result = array("type" => "important", "text" => JText::_("JON"));
        } else {
            $result = array("type" => "success", "text" => JText::_("JOFF"));
        }
        CrowdfundingPartnersInstallHelper::addRow($title, $result, $info);

        // Display result about verification FileInfo
        $title = JText::_("COM_CROWDFUNDINGPARTNERS_FILEINFO");
        $info  = "";
        if (!function_exists('finfo_open')) {
            $info   = JText::_("COM_CROWDFUNDINGPARTNERS_FILEINFO_INFO");
            $result = array("type" => "important", "text" => JText::_("JOFF"));
        } else {
            $result = array("type" => "success", "text" => JText::_("JON"));
        }
        CrowdfundingPartnersInstallHelper::addRow($title, $result, $info);

        // Display result about verification PHP version.
        $title = JText::_("COM_CROWDFUNDINGPARTNERS_PHP_VERSION");
        $info  = "";
        if (version_compare(PHP_VERSION, '5.3.0') < 0) {
            $result = array("type" => "important", "text" => JText::_("COM_CROWDFUNDINGPARTNERS_WARNING"));
        } else {
            $result = array("type" => "success", "text" => JText::_("JYES"));
        }
        CrowdfundingPartnersInstallHelper::addRow($title, $result, $info);

        // Display result about verification of installed Prism Library
        $title = JText::_("COM_CROWDFUNDINGPARTNERS_PRISM_LIBRARY");
        $info  = "";
        if (!class_exists("Prism\\Version")) {
            $info   = JText::_("COM_CROWDFUNDINGPARTNERS_PRISM_LIBRARY_DOWNLOAD");
            $result = array("type" => "important", "text" => JText::_("JNO"));
        } else {
            $result = array("type" => "success", "text" => JText::_("JYES"));
        }
        CrowdfundingPartnersInstallHelper::addRow($title, $result, $info);

        // Installed extensions

        CrowdfundingPartnersInstallHelper::addRowHeading(JText::_("COM_CROWDFUNDINGPARTNERS_INSTALLED_EXTENSIONS"));

        // Crowdfunding Library
        $result = array("type" => "success", "text" => JText::_("COM_CROWDFUNDINGPARTNERS_INSTALLED"));
        CrowdfundingPartnersInstallHelper::addRow(JText::_("COM_CROWDFUNDINGPARTNERS_CROWDFUNDINGPARTNERS_LIBRARY"), $result, JText::_("COM_CROWDFUNDINGPARTNERS_LIBRARY"));

        // Plugins

        // Crowdfunding - Partners
        $result = array("type" => "success", "text" => JText::_("COM_CROWDFUNDINGPARTNERS_INSTALLED"));
        CrowdfundingPartnersInstallHelper::addRow(JText::_("COM_CROWDFUNDINGPARTNERS_CROWDFUNDING_PARTNERS"), $result, JText::_("COM_CROWDFUNDINGPARTNERS_PLUGIN"));

        // Content - Crowdfunding Partners
        $result = array("type" => "success", "text" => JText::_("COM_CROWDFUNDINGPARTNERS_INSTALLED"));
        CrowdfundingPartnersInstallHelper::addRow(JText::_("COM_CROWDFUNDINGPARTNERS_CONTENT_CROWDFUNDINGPARTNERS"), $result, JText::_("COM_CROWDFUNDINGPARTNERS_PLUGIN"));

        // End table
        CrowdfundingPartnersInstallHelper::endTable();

        echo JText::sprintf("COM_CROWDFUNDINGPARTNERS_MESSAGE_REVIEW_SAVE_SETTINGS", JRoute::_("index.php?option=com_crowdfundingpartners"));

        if (!class_exists("Prism\\Version")) {
            echo JText::_("COM_CROWDFUNDINGPARTNERS_MESSAGE_INSTALL_PRISM_LIBRARY");
        }
    }
}
