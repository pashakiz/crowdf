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
 * Crowdfunding reward controller class.
 *
 * @package        Crowdfunding
 * @subpackage     Components
 */
class CrowdfundingControllerReward extends Prism\Controller\Form\Backend
{

    /**
     * Method to get a model object, loading it if required.
     *
     * @param    string $name   The model name. Optional.
     * @param    string $prefix The class prefix. Optional.
     * @param    array  $config Configuration array for model. Optional.
     *
     * @return    CrowdfundingModelReward    The model.
     * @since    1.5
     */
    public function getModel($name = 'Reward', $prefix = 'CrowdfundingModel', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);
        return $model;
    }

    public function save($key = null, $urlVar = null)
    {
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        $data   = $this->input->post->get('jform', array(), 'array');
        $itemId = JArrayHelper::getValue($data, "id");

        $dataFile  = $this->input->files->get('jform', array(), 'array');
        $image     = JArrayHelper::getValue($dataFile, "image", array(), "array");
        $imageName = Joomla\String\String::trim(JArrayHelper::getValue($image, 'name'));

        $redirectOptions = array(
            "task" => $this->getTask(),
            "id"   => $itemId
        );

        // Parse formatted amount.
        $data["amount"] = CrowdfundingHelper::parseAmount($data["amount"]);

        $model = $this->getModel();
        /** @var $model CrowdfundingModelReward */

        $form = $model->getForm($data, false);
        /** @var $form JForm */

        if (!$form) {
            throw new Exception(JText::_("COM_Crowdfunding_ERROR_FORM_CANNOT_BE_LOADED"), 500);
        }

        // Validate the form
        $validData = $model->validate($form, $data);

        // Check for errors.
        if ($validData === false) {
            $this->displayNotice($form->getErrors(), $redirectOptions);
            return;
        }

        $params = JComponentHelper::getParams("com_crowdfunding");
        /** @var  $params Joomla\Registry\Registry */

        try {

            $itemId = $model->save($validData);

            $redirectOptions["id"] = $itemId;

            // Upload an image
            $imagesAllowed = $params->get("rewards_images", 0);
            
            // Upload images.
            if ($imagesAllowed and !empty($imageName) and !empty($itemId)) {

                $reward = new Crowdfunding\Reward(JFactory::getDbo());
                $reward->load($itemId);

                // Get the folder where the images will be stored
                $imagesFolder = CrowdfundingHelper::getImagesFolder($reward->getUserId());

                jimport("joomla.filesystem.folder");
                if (!JFolder::exists($imagesFolder)) {
                    CrowdfundingHelper::createFolder($imagesFolder);
                }

                $images = $model->uploadImage($image, $imagesFolder);
                if (!empty($images)) {
                    $model->storeImage($images, $imagesFolder, $itemId);
                }
            }

        } catch (RuntimeException $e) {

            $this->displayError($e->getMessage(), $redirectOptions);
            return;

        } catch (Exception $e) {

            JLog::add($e->getMessage());
            throw new Exception(JText::_('COM_Crowdfunding_ERROR_SYSTEM'));

        }

        $this->displayMessage(JText::_('COM_Crowdfunding_REWARD_SAVED'), $redirectOptions);
    }

    /**
     * Delete image
     */
    public function removeImage()
    {
        // Check for request forgeries.
        JSession::checkToken("get") or jexit(JText::_('JINVALID_TOKEN'));

        // Get item id
        $itemId    = $this->input->get->getInt("id");

        $redirectOptions = array(
            "view" => "reward",
            "layout" => "edit",
            "id" => $itemId
        );

        // Create an reward object.
        $reward = new Crowdfunding\Reward(JFactory::getDbo());
        $reward->load($itemId);

        // Check for registered user
        if (!$reward->getId()) {
            $this->displayNotice(JText::_('COM_Crowdfunding_ERROR_INVALID_IMAGE'), $redirectOptions);
            return;
        }

        $imagesFolder = CrowdfundingHelper::getImagesFolder($reward->getUserId());

        try {

            jimport('joomla.filesystem.folder');
            jimport('joomla.filesystem.file');
            jimport('joomla.filesystem.path');

            $model = $this->getModel();

            $model->removeImage($itemId, $imagesFolder);

        } catch (Exception $e) {
            JLog::add($e->getMessage());
            throw new Exception(JText::_('COM_Crowdfunding_ERROR_SYSTEM'));
        }

        $this->displayMessage(JText::_('COM_Crowdfunding_IMAGE_DELETED'), $redirectOptions);
    }

    /**
     * Method to change state of reward.
     *
     * @throws Exception
     * @return  void
     */
    public function changeState()
    {
        // Check for request forgeries.
        JSession::checkToken("get") or jexit(JText::_('JINVALID_TOKEN'));

        // Get item id
        $return    = $this->input->get->get("return");

        $redirectOptions = array(
            "force_direction" => base64_decode($return)
        );

        $transactionId = $this->input->get->getInt("txn_id");
        $state         = $this->input->get->getInt('state');

        $state = (!$state) ? Prism\Constants::UNPUBLISHED : Prism\Constants::PUBLISHED;

        if (!$transactionId) {
            $this->displayWarning(JText::_("COM_Crowdfunding_ERROR_INVALID_TRANSACTION"), $redirectOptions);
            return;
        }

        try {

            $model = $this->getModel();
            /** @var $model CrowdfundingModelReward */

            $model->updateRewardState($transactionId, $state);

        } catch (Exception $e) {
            JLog::add($e->getMessage());
            throw new Exception(JText::_('COM_Crowdfunding_ERROR_SYSTEM'));
        }

        if (!$state) {
            $msg = JText::_("COM_Crowdfunding_REWARD_HAS_BEEN_SET_AS_NOT_SENT");
        } else {
            $msg = JText::_("COM_Crowdfunding_REWARD_HAS_BEEN_SET_AS_SENT");
        }

        $this->displayMessage($msg, $redirectOptions);
    }
}
