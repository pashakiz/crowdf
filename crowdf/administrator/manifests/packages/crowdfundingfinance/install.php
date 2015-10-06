<?php
/**
 * @package      Crowdfunding
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

/**
 * Script file of the component
 */
class pkg_crowdfundingfinanceInstallerScript
{
    /**
     * Method to install the component.
     *
     * @param string $parent
     *
     * @return void
     */
    public function install($parent)
    {
    }

    /**
     * Method to uninstall the component.
     *
     * @param string $parent
     *
     * @return void
     */
    public function uninstall($parent)
    {
    }

    /**
     * Method to update the component.
     *
     * @param string $parent
     *
     * @return void
     */
    public function update($parent)
    {
    }

    /**
     * Method to run before an install/update/uninstall method.
     *
     * @param string $type
     * @param string $parent
     *
     * @return void
     */
    public function preflight($type, $parent)
    {
    }

    /**
     * Method to run after an install/update/uninstall method.
     *
     * @param string $type
     * @param string $parent
     *
     * @return void
     */
    public function postflight($type, $parent)
    {
        if (!defined("COM_CROWDFUNDINGFINANCE_PATH_COMPONENT_ADMINISTRATOR")) {
            define("COM_CROWDFUNDINGFINANCE_PATH_COMPONENT_ADMINISTRATOR", JPATH_ADMINISTRATOR . "/components/com_crowdfundingfinance");
        }

        jimport("Prism.init");

        // Register Component helpers
        JLoader::register(
            "CrowdfundingFinanceInstallHelper",
            COM_CROWDFUNDINGFINANCE_PATH_COMPONENT_ADMINISTRATOR . "/helpers/install.php"
        );

        // Start table with the information
        CrowdfundingFinanceInstallHelper::startTable();

        // Requirements
        CrowdfundingFinanceInstallHelper::addRowHeading(JText::_("COM_CROWDFUNDINGFINANCE_MINIMUM_REQUIREMENTS"));

        // Display result about verification for GD library
        $title = JText::_("COM_CROWDFUNDINGFINANCE_GD_LIBRARY");
        $info  = "";
        if (!extension_loaded('gd') and function_exists('gd_info')) {
            $result = array("type" => "important", "text" => JText::_("COM_CROWDFUNDINGFINANCE_WARNING"));
        } else {
            $result = array("type" => "success", "text" => JText::_("JON"));
        }
        CrowdfundingFinanceInstallHelper::addRow($title, $result, $info);

        // Display result about verification for cURL library
        $title = JText::_("COM_CROWDFUNDINGFINANCE_CURL_LIBRARY");
        $info  = "";
        if (!extension_loaded('curl')) {
            $info   = JText::_("COM_CROWDFUNDINGFINANCE_CURL_INFO");
            $result = array("type" => "important", "text" => JText::_("JOFF"));
        } else {
            $result = array("type" => "success", "text" => JText::_("JON"));
        }
        CrowdfundingFinanceInstallHelper::addRow($title, $result, $info);

        // Display result about verification Magic Quotes
        $title = JText::_("COM_CROWDFUNDINGFINANCE_MAGIC_QUOTES");
        $info  = "";
        if (get_magic_quotes_gpc()) {
            $info   = JText::_("COM_CROWDFUNDINGFINANCE_MAGIC_QUOTES_INFO");
            $result = array("type" => "important", "text" => JText::_("JON"));
        } else {
            $result = array("type" => "success", "text" => JText::_("JOFF"));
        }
        CrowdfundingFinanceInstallHelper::addRow($title, $result, $info);

        // Display result about PHP version.
        $title = JText::_("COM_CROWDFUNDINGFINANCE_PHP_VERSION");
        $info  = "";
        if (version_compare(PHP_VERSION, '5.3.0') < 0) {
            $result = array("type" => "important", "text" => JText::_("COM_CROWDFUNDINGFINANCE_WARNING"));
        } else {
            $result = array("type" => "success", "text" => JText::_("JYES"));
        }
        CrowdfundingFinanceInstallHelper::addRow($title, $result, $info);

        // Display result about verification of installed Prism Library
        $title = JText::_("COM_CROWDFUNDINGFINANCE_PRISM_LIBRARY");
        $info  = "";
        if (!class_exists("Prism\\Version")) {
            $info   = JText::_("COM_CROWDFUNDINGFINANCE_PRISM_LIBRARY_DOWNLOAD");
            $result = array("type" => "important", "text" => JText::_("JNO"));
        } else {
            $result = array("type" => "success", "text" => JText::_("JYES"));
        }
        CrowdfundingFinanceInstallHelper::addRow($title, $result, $info);

        // Installed extensions

        CrowdfundingFinanceInstallHelper::addRowHeading(JText::_("COM_CROWDFUNDINGFINANCE_INSTALLED_EXTENSIONS"));

        // Crowdfunding Library
        $result = array("type" => "success", "text" => JText::_("COM_CROWDFUNDINGFINANCE_INSTALLED"));
        CrowdfundingFinanceInstallHelper::addRow(JText::_("COM_CROWDFUNDINGFINANCE_CROWDFUNDINGFINANCE_LIBRARY"), $result, JText::_("COM_CROWDFUNDINGFINANCE_LIBRARY"));

        // End table
        CrowdfundingFinanceInstallHelper::endTable();

        echo JText::sprintf("COM_CROWDFUNDINGFINANCE_MESSAGE_REVIEW_SAVE_SETTINGS", JRoute::_("index.php?option=com_crowdfundingfinance"));

        if (!class_exists("Prism\\Version")) {
            echo JText::_("COM_CROWDFUNDINGFINANCE_MESSAGE_INSTALL_PRISM_LIBRARY");
        }
    }
}
