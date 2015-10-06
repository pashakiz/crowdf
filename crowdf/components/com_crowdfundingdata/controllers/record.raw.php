<?php
/**
 * @package      CrowdfundingData
 * @subpackage   Component
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2014 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// No direct access
defined('_JEXEC') or die;

/**
 * Data controller class.
 *
 * @package        CrowdfundingData
 * @subpackage     Component
 * @since          1.6
 */
class CrowdfundingDataControllerRecord extends JControllerForm
{
    public function save($key = null, $urlVar = null)
    {
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        $response = new Prism\Response\Json();

        $data   = $this->input->post->get('jform', array(), 'array');
        $itemId = Joomla\Utilities\ArrayHelper::getValue($data, "project_id", 0, "int");
        $terms  = $this->input->post->getInt("terms", 0);

        $project = new Crowdfunding\Project(JFactory::getDbo());
        $project->load($itemId);

        $returnUrl = CrowdfundingHelperRoute::getBackingRoute($project->getSlug(), $project->getCatSlug());

        if (!$project->getId()) {

            // Send response to the browser
            $response
                ->setTitle(JText::_("COM_CROWDFUNDINGDATA_FAIL"))
                ->setText(JText::_("COM_CROWDFUNDINGDATA_INVALID_ITEM"))
                ->setRedirectUrl($returnUrl)
                ->failure();

            echo $response;
            JFactory::getApplication()->close();

        }

        $model = $this->getModel();
        /** @var $model CrowdfundingDataModelRecord */

        $form = $model->getForm($data, false);
        /** @var $form JForm */

        if (!$form) {
            throw new Exception(JText::_("COM_CROWDFUNDINGDATA_ERROR_FORM_CANNOT_BE_LOADED"), 500);
        }

        // Validate the form data
        $validData = $model->validate($form, $data);

        // Check for errors
        if ($validData === false) {

            $errors_ = $form->getErrors();
            $errors  = array();
            /** @var $error RuntimeException */

            foreach ($errors_ as $error) {
                $errors[] = $error->getMessage();
            }

            // Send response to the browser
            $response
                ->setTitle(JText::_("COM_CROWDFUNDINGDATA_FAIL"))
                ->setText(implode("\n", $errors))
                ->setRedirectUrl($returnUrl)
                ->failure();

            echo $response;
            JFactory::getApplication()->close();
        }

        // Get the payment session object and session ID.
        $paymentSessionContext    = Crowdfunding\Constants::PAYMENT_SESSION_CONTEXT . $project->getId();
        $paymentSession           = $app->getUserState($paymentSessionContext);

        try {

            $validData["session_id"] = $paymentSession->session_id;

            $model->save($validData);

        } catch (Exception $e) {

            $response
                ->setTitle(JText::_("COM_CROWDFUNDINGDATA_FAIL"))
                ->setText(JText::_('COM_CROWDFUNDINGDATA_ERROR_SYSTEM'))
                ->setRedirectUrl($returnUrl)
                ->failure();

            echo $response;
            JFactory::getApplication()->close();

        }

        $componentParams = JComponentHelper::getParams("com_crowdfunding");
        /** @var  $componentParams Joomla\Registry\Registry */

        $processUrl = JUri::base()."index.php?option=com_crowdfunding&task=backing.process&id=".(int)$project->getId()."&rid=".(int)$paymentSession->rewardId."&amount=".rawurldecode($paymentSession->amount)."&".JSession::getFormToken(). "=1";

        // Set the value of terms of use condition.
        if ($componentParams->get("backing_terms", 0) and !empty($terms)) {
            $processUrl .= "&terms=1";
        }

        $filter     = JFilterInput::getInstance();
        $processUrl = $filter->clean($processUrl);

        $response
            ->setTitle(JText::_("COM_CROWDFUNDINGDATA_SUCCESS"))
            ->setText(JText::_("COM_CROWDFUNDINGDATA_DATA_SAVED_SUCCESSFULLY"))
            ->setRedirectUrl($processUrl)
            ->success();

        echo $response;
        JFactory::getApplication()->close();
    }
}
