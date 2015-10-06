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
 * Default Controller
 *
 * @package        CrowdfundingData
 * @subpackage     Component
 */
class CrowdfundingDataController extends JControllerLegacy
{
    public function display($cachable = false, $urlparams = array())
    {
        $app = JFactory::getApplication();
        /** @var $app JApplicationAdministrator */

        $viewName = $app->input->getCmd('view', 'dashboard');
        $app->input->set("view", $viewName);

        parent::display($cachable = false, $urlparams = array());

        return $this;
    }
}
