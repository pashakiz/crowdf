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
 * Crowdfunding project controller class.
 *
 * @package        ITPrism Components
 * @subpackage     Crowdfunding
 * @since          1.6
 */
class CrowdfundingControllerProject extends Prism\Controller\Form\Backend
{
    /**
     * Method to get a model object, loading it if required.
     *
     * @param    string $name   The model name. Optional.
     * @param    string $prefix The class prefix. Optional.
     * @param    array  $config Configuration array for model. Optional.
     *
     * @return    object    The model.
     * @since    1.5
     */
    public function getModel($name = 'Project', $prefix = 'CrowdfundingModel', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);

        return $model;
    }

    public function save($key = null, $urlVar = null)
    {
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        $data   = $this->input->post->get('jform', array(), 'array');
        $itemId = JArrayHelper::getValue($data, "id");

        $redirectOptions = array(
            "task" => $this->getTask(),
            "id"   => $itemId
        );

        // Parse formatted goal and funded amounts.
        $data["goal"] = CrowdfundingHelper::parseAmount($data["goal"]);
        $data["funded"] = CrowdfundingHelper::parseAmount($data["funded"]);

        $model = $this->getModel();
        /** @var $model CrowdfundingModelProject */

        $form = $model->getForm($data, false);
        /** @var $form JForm */

        if (!$form) {
            throw new Exception(JText::_("COM_Crowdfunding_ERROR_FORM_CANNOT_BE_LOADED"), 500);
        }

        // Validate the form
        $validData = $model->validate($form, $data);

        $validData["duration_type"] = JArrayHelper::getValue($data, "funding_duration_type");

        // Check for errors.
        if ($validData === false) {
            $this->displayNotice($form->getErrors(), $redirectOptions);
            return;
        }

        try {

            // Get image
            $files = $this->input->files->get('jform', array(), 'array');
            $image = JArrayHelper::getValue($files, "image");

            $pitchImage = JArrayHelper::getValue($files, "pitch_image");

            // Upload image
            if (!empty($image['name'])) {

                $imageNames = $model->uploadImage($image);
                if (!empty($imageNames["image"])) {
                    $validData = array_merge($validData, $imageNames);
                }

            }

            // Upload pitch image
            if (!empty($pitchImage['name'])) {

                $pitchImageName = $model->uploadPitchImage($pitchImage);
                if (!empty($pitchImageName)) {
                    $validData["pitch_image"] = $pitchImageName;
                }

            }

            $itemId = $model->save($validData);

            $redirectOptions["id"] = $itemId;

        } catch (Exception $e) {

            JLog::add($e->getMessage());
            throw new Exception(JText::_('COM_Crowdfunding_ERROR_SYSTEM'));

        }

        $this->displayMessage(JText::_('COM_Crowdfunding_PROJECT_SAVED'), $redirectOptions);
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
        $imageType = $this->input->get->getCmd("image_type");

        $redirectOptions = array(
            "view" => "projects",
        );

        // Check for registered user
        if (!$itemId) {
            $this->displayNotice(JText::_('COM_Crowdfunding_ERROR_INVALID_IMAGE'), $redirectOptions);
            return;
        }

        try {

            jimport('joomla.filesystem.folder');
            jimport('joomla.filesystem.file');
            jimport('joomla.filesystem.path');

            $model = $this->getModel();

            switch ($imageType) {

                case "main":
                    $model->removeImage($itemId);
                    break;

                case "pitch":
                    $model->removePitchImage($itemId);
                    break;
            }

        } catch (Exception $e) {
            JLog::add($e->getMessage());
            throw new Exception(JText::_('COM_Crowdfunding_ERROR_SYSTEM'));
        }

        $redirectOptions = array(
            "view"   => "project",
            "layout" => "edit",
            "id"     => $itemId
        );

        $this->displayMessage(JText::_('COM_Crowdfunding_IMAGE_DELETED'), $redirectOptions);
    }
}
