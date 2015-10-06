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
 * It is CrowdfundingFiles helper class
 */
class CrowdfundingFilesHelper
{
    protected static $extension = "com_crowdfundingfiles";

    /**
     * Configure the Linkbar.
     *
     * @param    string  $vName  The name of the active view.
     *
     * @since    1.6
     */
    public static function addSubmenu($vName = 'dashboard')
    {
        JHtmlSidebar::addEntry(
            JText::_('COM_CROWDFUNDINGFILES_DASHBOARD'),
            'index.php?option=' . self::$extension . '&view=dashboard',
            $vName == 'dashboard'
        );

        JHtmlSidebar::addEntry(
            JText::_('COM_CROWDFUNDINGFILES_FILES'),
            'index.php?option=' . self::$extension . '&view=files',
            $vName == 'files'
        );

        JHtmlSidebar::addEntry(
            JText::_('COM_CROWDFUNDINGFILES_PLUGINS'),
            'index.php?option=com_plugins&view=plugins&filter_search=' . rawurlencode("Crowdfunding Files"),
            $vName == 'plugins'
        );
    }

    /**
     * Generate a path to the folder, where the files are stored.
     *
     * @param int    $userId User Id.
     * @param string $path   A base path to the folder. It can be JPATH_BASE, JPATH_ROOT, JPATH_SITE,... Default is JPATH_ROOT.
     *
     * @return string
     */
    public static function getMediaFolder($userId = 0, $path = JPATH_ROOT)
    {
        jimport('joomla.filesystem.path');
        jimport('joomla.filesystem.folder');

        $params = JComponentHelper::getParams(self::$extension);
        /** @var $params Joomla\Registry\Registry */

        $folder = $path . DIRECTORY_SEPARATOR . $params->get("media_directory", "/media/crowdfundingfiles");

        if (!empty($userId)) {
            $folder .= DIRECTORY_SEPARATOR . "user" . (int)$userId;
        }

        return JPath::clean($folder);
    }

    /**
     * Generate a URI path to the folder, where the files are stored.
     *
     * @param int $userId User Id.
     *
     * @return string
     */
    public static function getMediaFolderUri($userId = 0)
    {
        $params = JComponentHelper::getParams(self::$extension);
        /** @var $params Joomla\Registry\Registry */

        $uri = $params->get("media_directory", "/media/crowdfundingfiles");

        if (!empty($userId)) {
            $uri .= "/user" . (int)$userId;
        }

        return $uri;
    }
}
