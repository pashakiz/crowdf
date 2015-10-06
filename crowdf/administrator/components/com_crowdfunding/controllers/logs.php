<?php
/**
 * @package      Crowdfunding
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// No direct access
defined('_JEXEC') or die;

/**
 * Crowdfunding logs controller class
 *
 * @package      Crowdfunding
 * @subpackage   Components
 */
class CrowdfundingControllerLogs extends Prism\Controller\Admin
{
    public function getModel($name = 'Log', $prefix = 'CrowdfundingModel', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);

        return $model;
    }

    public function removeAll()
    {
        // Check for request forgeries
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        $redirectOptions = array(
            "view" => $this->view_list
        );

        // Get the model.
        $model = $this->getModel();
        /** @var $model CrowdfundingModelLog * */

        try {

            $model->removeAll();

        } catch (Exception $e) {
            JLog::add($e->getMessage());
            throw new Exception(JText::_('COM_Crowdfunding_ERROR_SYSTEM'));
        }

        $this->displayMessage(JText::_("COM_Crowdfunding_ALL_ITEMS_REMOVED_SUCCESSFULLY"), $redirectOptions);
    }
}
