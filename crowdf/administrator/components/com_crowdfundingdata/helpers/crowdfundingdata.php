<?php
/**
 * @package      CrowdfundingData
 * @subpackage   Component
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2014 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// no direct access
defined('_JEXEC') or die;

/**
 * It is Crowdfunding data helper class
 */
class CrowdfundingDataHelper
{
    protected static $extension = "com_crowdfundingdata";

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
            JText::_('COM_CROWDFUNDINGDATA_DASHBOARD'),
            'index.php?option=' . self::$extension . '&view=dashboard',
            $vName == 'dashboard'
        );

        JHtmlSidebar::addEntry(
            JText::_('COM_CROWDFUNDINGDATA_RECORDS'),
            'index.php?option=' . self::$extension . '&view=records',
            $vName == 'records'
        );

        JHtmlSidebar::addEntry(
            JText::_('COM_CROWDFUNDINGDATA_PLUGINS'),
            'index.php?option=com_plugins&view=plugins&filter_folder=crowdfundingpayment&filter_search=' . rawurlencode("Data"),
            $vName == 'plugins'
        );
    }
}
