<?php
/**
 * @package      Crowdfunding
 * @subpackage   Component
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// No direct access
defined('_JEXEC') or die;

/**
 * Crowdfunding email controller class.
 *
 * @package        Crowdfunding
 * @subpackage     Component
 * @since          1.6
 */
class CrowdfundingControllerEmail extends Prism\Controller\Form\Backend
{
    public function save($key = null, $urlVar = null)
    {
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        $data   = $this->input->post->get('jform', array(), 'array');
        $itemId = JArrayHelper::getValue($data, "id");

        $responseOptions = array(
            "task" => $this->getTask(),
            "id"   => $itemId
        );

        $model = $this->getModel();
        /** @var $model CrowdfundingModelEmail */

        $form = $model->getForm($data, false);
        /** @var $form JForm */

        if (!$form) {
            throw new Exception(JText::_("COM_Crowdfunding_ERROR_FORM_CANNOT_BE_LOADED"), 500);
        }

        // Validate the form data
        $validData = $model->validate($form, $data);

        // Check for errors
        if ($validData === false) {
            $this->displayNotice($form->getErrors(), $responseOptions);

            return;
        }

        try {

            $itemId = $model->save($validData);

            $responseOptions["id"] = $itemId;

        } catch (Exception $e) {
            JLog::add($e->getMessage());
            throw new Exception(JText::_('COM_Crowdfunding_ERROR_SYSTEM'));
        }

        $this->displayMessage(JText::_('COM_Crowdfunding_EMAIL_SAVED_SUCCESSFULLY'), $responseOptions);
    }
}
