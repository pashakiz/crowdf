<?php
/**
 * @package      CrowdfundingPartners
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2014 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// No direct access
defined('_JEXEC') or die;

/**
 * CrowdfundingPartners Partner controller class.
 *
 * @package      CrowdfundingPartners
 * @subpackage   Components
 */
class CrowdfundingPartnersControllerPartner extends Prism\Controller\Form\Backend
{
    public function save($key = null, $urlVar = null)
    {
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        $data   = $this->input->post->get('jform', array(), 'array');
        $itemId = Joomla\Utilities\ArrayHelper::getValue($data, "id", 0, "int");
        $projectId = Joomla\Utilities\ArrayHelper::getValue($data, "project_id", 0, "int");
        $partnerId = Joomla\Utilities\ArrayHelper::getValue($data, "partner_id", 0, "int");

        $redirectOptions = array(
            "task" => $this->getTask(),
            "id"   => $itemId
        );

        $model = $this->getModel();
        /** @var $model CrowdfundingPartnersModelPartner */

        $form = $model->getForm($data, false);
        /** @var $form JForm */

        if (!$form) {
            throw new Exception(JText::_("COM_CROWDFUNDINGPARTNERS_ERROR_FORM_CANNOT_BE_LOADED"));
        }

        // Validate the form data
        $validData = $model->validate($form, $data);

        // Check for errors
        if ($validData === false) {
            $this->displayNotice($form->getErrors(), $redirectOptions);
            return;
        }

        $project = new Crowdfunding\Project(JFactory::getDbo());
        $project->load($projectId);

        if (!$project->getId()) {
            $this->displayError(JText::_('COM_CROWDFUNDINGPARTNERS_ERROR_INVALID_PROJECT'), $redirectOptions);
            return;
        }

        if ($partnerId == $project->getUserId()) {
            $this->displayError(JText::_('COM_CROWDFUNDINGPARTNERS_ERROR_CANNOT_ASSIGN_PARTNER'), $redirectOptions);
            return;
        }

        try {

            $itemId = $model->save($validData);

            $redirectOptions["id"] = $itemId;

        } catch (Exception $e) {
            JLog::add($e->getMessage());
            throw new Exception(JText::_('COM_CROWDFUNDINGPARTNERS_ERROR_SYSTEM'));
        }

        $this->displayMessage(JText::_('COM_CROWDFUNDINGPARTNERS_PARTNER_SAVED'), $redirectOptions);
    }
}
