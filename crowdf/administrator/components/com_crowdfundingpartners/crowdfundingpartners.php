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

jimport("Prism.init");
jimport("Crowdfunding.init");
jimport("CrowdfundingPartners.init");

// Get an instance of the controller prefixed by HelloWorld
$controller = JControllerLegacy::getInstance("CrowdfundingPartners");

// Perform the Request task
$controller->execute(JFactory::getApplication()->input->getCmd('task'));
$controller->redirect();
