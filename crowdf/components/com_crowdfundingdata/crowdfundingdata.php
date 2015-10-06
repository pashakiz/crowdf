<?php
/**
 * @package      CrowdfundingData
 * @subpackage   Component
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2014 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// No direct access.
defined('_JEXEC') or die;

jimport("Prism.init");
jimport("Crowdfunding.init");
jimport("crowdfundingdata.init");

$controller = JControllerLEgacy::getInstance('CrowdfundingData');
$controller->execute(JFactory::getApplication()->input->getCmd('task'));
$controller->redirect();
