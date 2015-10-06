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
 * It is CrowdfundingPartners helper class
 */
class CrowdfundingPartnersHelper
{
    protected static $extension = "com_crowdfundingpartners";

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
            JText::_('COM_CROWDFUNDINGPARTNERS_DASHBOARD'),
            'index.php?option=' . self::$extension . '&view=dashboard',
            $vName == 'dashboard'
        );

        JHtmlSidebar::addEntry(
            JText::_('COM_CROWDFUNDINGPARTNERS_PARTNERS'),
            'index.php?option=' . self::$extension . '&view=partners',
            $vName == 'partners'
        );

        JHtmlSidebar::addEntry(
            JText::_('COM_CROWDFUNDINGPARTNERS_PLUGINS'),
            'index.php?option=com_plugins&view=plugins&filter_search=' . rawurlencode("Crowdfunding Partners"),
            $vName == 'plugins'
        );
    }
}
