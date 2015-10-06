<?php
/**
 * @package      CrowdfundingFiles
 * @subpackage   Component
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2014 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// No direct access.
defined('_JEXEC') or die;

jimport("Prism.init");
jimport("Crowdfunding.init");
jimport("CrowdfundingFiles.init");

$controller = JControllerLEgacy::getInstance('CrowdfundingFiles');
$controller->execute(JFactory::getApplication()->input->getCmd('task'));
$controller->redirect();
