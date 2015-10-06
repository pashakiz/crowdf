<?php
/**
 * @package      Crowdfunding
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// no direct access
defined('_JEXEC') or die;

/**
 * Script file of the component
 */
class pkg_crowdfundingInstallerScript
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
        if (!defined("COM_CROWDFUNDING_PATH_COMPONENT_ADMINISTRATOR")) {
            define("COM_CROWDFUNDING_PATH_COMPONENT_ADMINISTRATOR", JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . "components" . DIRECTORY_SEPARATOR . "com_crowdfunding");
        }

        // Register Component helpers
        JLoader::register("CrowdfundingInstallHelper", COM_CROWDFUNDING_PATH_COMPONENT_ADMINISTRATOR . DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR . "install.php");

        jimport('joomla.filesystem.path');
        jimport('joomla.filesystem.folder');
        jimport('joomla.filesystem.file');
        jimport('Prism.init');
        jimport('Crowdfunding.init');

        $params             = JComponentHelper::getParams("com_crowdfunding");
        /** @var $params Joomla\Registry\Registry */

        // Prepare images folders.
        $imagesFolder = JFolder::makeSafe($params->get("images_directory", "images/crowdfunding"));
        $temporaryImagesFolder = $imagesFolder . "/temporary";

        // Create images folder.
        $imagesPath   = JPath::clean(JPATH_SITE . DIRECTORY_SEPARATOR . $imagesFolder);
        if (!is_dir($imagesPath)) {
            CrowdfundingInstallHelper::createFolder($imagesPath);
        }

        // Create temporary images folder
        $temporaryImagesPath  = JPath::clean(JPATH_SITE . DIRECTORY_SEPARATOR . $temporaryImagesFolder);
        if (!is_dir($temporaryImagesPath)) {
            CrowdfundingInstallHelper::createFolder($temporaryImagesPath);
        }

        // Start table with the information
        CrowdfundingInstallHelper::startTable();

        // Requirements
        CrowdfundingInstallHelper::addRowHeading(JText::_("COM_CROWDFUNDING_MINIMUM_REQUIREMENTS"));

        // Display result about verification for existing folder
        $title = JText::_("COM_CROWDFUNDING_IMAGE_FOLDER");
        $info  = $imagesFolder;
        if (!is_dir($imagesPath)) {
            $result = array("type" => "important", "text" => JText::_("JNO"));
        } else {
            $result = array("type" => "success", "text" => JText::_("JYES"));
        }
        CrowdfundingInstallHelper::addRow($title, $result, $info);

        // Display result about verification for writable folder
        $title = JText::_("COM_CROWDFUNDING_IMAGE_WRITABLE_FOLDER");
        $info  = $imagesFolder;
        if (!is_writable($imagesPath)) {
            $result = array("type" => "important", "text" => JText::_("JNO"));
        } else {
            $result = array("type" => "success", "text" => JText::_("JYES"));
        }
        CrowdfundingInstallHelper::addRow($title, $result, $info);

        // Display result about verification for existing folder
        $title = JText::_("COM_CROWDFUNDING_TEMPORARY_IMAGE_FOLDER");
        $info  = $temporaryImagesFolder;
        if (!is_dir($temporaryImagesPath)) {
            $result = array("type" => "important", "text" => JText::_("JNO"));
        } else {
            $result = array("type" => "success", "text" => JText::_("JYES"));
        }
        CrowdfundingInstallHelper::addRow($title, $result, $info);

        // Display result about verification for writable folder
        $title = JText::_("COM_CROWDFUNDING_TEMPORARY_IMAGE_WRITABLE_FOLDER");
        $info  = $temporaryImagesFolder;
        if (!is_writable($temporaryImagesPath)) {
            $result = array("type" => "important", "text" => JText::_("JNO"));
        } else {
            $result = array("type" => "success", "text" => JText::_("JYES"));
        }
        CrowdfundingInstallHelper::addRow($title, $result, $info);

        // Display result about verification for GD library
        $title = JText::_("COM_CROWDFUNDING_GD_LIBRARY");
        $info  = "";
        if (!extension_loaded('gd') and function_exists('gd_info')) {
            $result = array("type" => "important", "text" => JText::_("COM_CROWDFUNDING_WARNING"));
        } else {
            $result = array("type" => "success", "text" => JText::_("JON"));
        }
        CrowdfundingInstallHelper::addRow($title, $result, $info);

        // Display result about verification for cURL library
        $title = JText::_("COM_CROWDFUNDING_CURL_LIBRARY");
        $info  = "";
        if (!extension_loaded('curl')) {
            $info   = JText::_("COM_CROWDFUNDING_CURL_INFO");
            $result = array("type" => "important", "text" => JText::_("JOFF"));
        } else {
            $result = array("type" => "success", "text" => JText::_("JON"));
        }
        CrowdfundingInstallHelper::addRow($title, $result, $info);

        // Display result about verification Magic Quotes
        $title = JText::_("COM_CROWDFUNDING_MAGIC_QUOTES");
        $info  = "";
        if (get_magic_quotes_gpc()) {
            $info   = JText::_("COM_CROWDFUNDING_MAGIC_QUOTES_INFO");
            $result = array("type" => "important", "text" => JText::_("JON"));
        } else {
            $result = array("type" => "success", "text" => JText::_("JOFF"));
        }
        CrowdfundingInstallHelper::addRow($title, $result, $info);

        // Display result about verification FileInfo
        $title = JText::_("COM_CROWDFUNDING_FILEINFO");
        $info  = "";
        if (!function_exists('finfo_open')) {
            $info   = JText::_("COM_CROWDFUNDING_FILEINFO_INFO");
            $result = array("type" => "important", "text" => JText::_("JOFF"));
        } else {
            $result = array("type" => "success", "text" => JText::_("JON"));
        }
        CrowdfundingInstallHelper::addRow($title, $result, $info);

        // Display result about verification of PHP Version.
        $title = JText::_("COM_CROWDFUNDING_PHP_VERSION");
        $info  = "";
        if (version_compare(PHP_VERSION, '5.3.0') < 0) {
            $result = array("type" => "important", "text" => JText::_("COM_CROWDFUNDING_WARNING"));
        } else {
            $result = array("type" => "success", "text" => JText::_("JYES"));
        }
        CrowdfundingInstallHelper::addRow($title, $result, $info);

        // Display result about verification of installed Prism Library
        $title = JText::_("COM_CROWDFUNDING_PRISM_LIBRARY");
        $info  = "";
        if (!class_exists("Prism\\Version")) {
            $info   = JText::_("COM_CROWDFUNDING_PRISM_LIBRARY_DOWNLOAD");
            $result = array("type" => "important", "text" => JText::_("JNO"));
        } else {
            $result = array("type" => "success", "text" => JText::_("JYES"));
        }
        CrowdfundingInstallHelper::addRow($title, $result, $info);

        // Installed extensions

        CrowdfundingInstallHelper::addRowHeading(JText::_("COM_CROWDFUNDING_INSTALLED_EXTENSIONS"));

        // Crowdfunding Library
        $result = array("type" => "success", "text" => JText::_("COM_CROWDFUNDING_INSTALLED"));
        CrowdfundingInstallHelper::addRow(JText::_("COM_CROWDFUNDING_CROWDFUNDING_LIBRARY"), $result, JText::_("COM_CROWDFUNDING_LIBRARY"));

        // Plugins

        // Content - Crowdfunding - Navigation
        $result = array("type" => "success", "text" => JText::_("COM_CROWDFUNDING_INSTALLED"));
        CrowdfundingInstallHelper::addRow(JText::_("COM_CROWDFUNDING_CONTENT_CROWDFUNDING_NAVIGATION"), $result, JText::_("COM_CROWDFUNDING_PLUGIN"));

        // Content - Crowdfunding - Share
        $result = array("type" => "success", "text" => JText::_("COM_CROWDFUNDING_INSTALLED"));
        CrowdfundingInstallHelper::addRow(JText::_("COM_CROWDFUNDING_CONTENT_CROWDFUNDING_SHARE"), $result, JText::_("COM_CROWDFUNDING_PLUGIN"));

        // Content - Crowdfunding - Admin Mail
        $result = array("type" => "success", "text" => JText::_("COM_CROWDFUNDING_INSTALLED"));
        CrowdfundingInstallHelper::addRow(JText::_("COM_CROWDFUNDING_CONTENT_CROWDFUNDING_ADMIN_MAIL"), $result, JText::_("COM_CROWDFUNDING_PLUGIN"));

        // Content - Crowdfunding - User Mail
        $result = array("type" => "success", "text" => JText::_("COM_CROWDFUNDING_INSTALLED"));
        CrowdfundingInstallHelper::addRow(JText::_("COM_CROWDFUNDING_CONTENT_CROWDFUNDING_USER_MAIL"), $result, JText::_("COM_CROWDFUNDING_PLUGIN"));

        // Content - Crowdfunding - Validator
        $result = array("type" => "success", "text" => JText::_("COM_CROWDFUNDING_INSTALLED"));
        CrowdfundingInstallHelper::addRow(JText::_("COM_CROWDFUNDING_CONTENT_CROWDFUNDING_VALIDATOR"), $result, JText::_("COM_CROWDFUNDING_PLUGIN"));

        // System - Crowdfunding - Modules
        $result = array("type" => "success", "text" => JText::_("COM_CROWDFUNDING_INSTALLED"));
        CrowdfundingInstallHelper::addRow(JText::_("COM_CROWDFUNDING_SYSTEM_CROWDFUNDINGMODULES"), $result, JText::_("COM_CROWDFUNDING_PLUGIN"));

        // Crowdfunding Payment - PayPal
        $result = array("type" => "success", "text" => JText::_("COM_CROWDFUNDING_INSTALLED"));
        CrowdfundingInstallHelper::addRow(JText::_("COM_CROWDFUNDING_CROWDFUNDINGPAYMENT_PAYPAL"), $result, JText::_("COM_CROWDFUNDING_PLUGIN"));

        // Modules

        // Crowdfunding Info
        $result = array("type" => "success", "text" => JText::_("COM_CROWDFUNDING_INSTALLED"));
        CrowdfundingInstallHelper::addRow(JText::_("COM_CROWDFUNDING_CROWDFUNDING_MODULE_INFO"), $result, JText::_("COM_CROWDFUNDING_MODULE"));

        // Crowdfunding Details
        $result = array("type" => "success", "text" => JText::_("COM_CROWDFUNDING_INSTALLED"));
        CrowdfundingInstallHelper::addRow(JText::_("COM_CROWDFUNDING_CROWDFUNDING_MODULE_DETAILS"), $result, JText::_("COM_CROWDFUNDING_MODULE"));

        // Crowdfunding Rewards
        $result = array("type" => "success", "text" => JText::_("COM_CROWDFUNDING_INSTALLED"));
        CrowdfundingInstallHelper::addRow(JText::_("COM_CROWDFUNDING_CROWDFUNDING_MODULE_REWARDS"), $result, JText::_("COM_CROWDFUNDING_MODULE"));

        // End table
        CrowdfundingInstallHelper::endTable();

        echo JText::sprintf("COM_CROWDFUNDING_MESSAGE_REVIEW_SAVE_SETTINGS", JRoute::_("index.php?option=com_crowdfunding"));

        if (!class_exists("Prism\\Version")) {
            echo JText::_("COM_CROWDFUNDING_MESSAGE_INSTALL_PRISM_LIBRARY");
        } else {

            if (class_exists("Crowdfunding\\Version")) {
                $prismVersion        = new Prism\Version();
                $crowdfundingVersion = new Crowdfunding\Version();
                if (version_compare($prismVersion->getShortVersion(), $crowdfundingVersion->requiredPrismVersion)) {
                    echo JText::_("COM_CROWDFUNDING_MESSAGE_INSTALL_PRISM_LIBRARY");
                }
            }
        }

        // Remove the files that the system does not use anymore.
        $this->removeUnusedFiles();
    }

    private function removeUnusedFiles()
    {
        $files = array(
            "/components/com_crowdfunding/helpers/category.php",
            "/components/com_crowdfunding/views/project/tmpl/story_extraimages.php",
            "/administrator/components/com_crowdfunding/views/project/tmpl/edit_extraimages.php",
            "/administrator/components/com_crowdfunding/models/fields/projects.php",
            "/administrator/components/com_crowdfunding/models/fields/goal.php",
            "/administrator/components/com_crowdfunding/models/fields/fundingtype.php",
            "/administrator/components/com_crowdfunding/models/fields/currencies.php",
            "/administrator/components/com_crowdfunding/models/fields/cfemails.php",
            "/administrator/components/com_crowdfunding/layouts/project_wizard_six_steps.php",
            "/administrator/components/com_crowdfunding/layouts/project_wizard.php",
            "/administrator/components/com_crowdfunding/layouts/payment_wizard_four_steps.php",
            "/administrator/components/com_crowdfunding/layouts/payment_wizard.php",
            "/administrator/components/com_crowdfunding/layouts/items_grid.php"
        );

        foreach ($files as $file) {
            $file = JPath::clean(JPATH_ROOT . $file);

            if (JFile::exists($file)) {
                JFile::delete($file);
            }
        }
    }
}
