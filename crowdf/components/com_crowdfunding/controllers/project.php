<?php
/**
 * @package      Crowdfunding
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// no direct access
defined('_JEXEC') or die;

/**
 * Crowdfunding project controller
 *
 * @package     Crowdfunding
 * @subpackage  Components
 */
class CrowdfundingControllerProject extends Prism\Controller\Form\Frontend
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
        // Check for request forgeries.
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        $userId = JFactory::getUser()->get("id");
        if (!$userId) {
            $redirectOptions = array(
                "force_direction" => "index.php?option=com_users&view=login"
            );
            $this->displayNotice(JText::_('COM_CROWDFUNDING_ERROR_NOT_LOG_IN'), $redirectOptions);
            return;
        }

        // Get the data from the form POST
        $data   = $this->input->post->get('jform', array(), 'array');
        $itemId = Joomla\Utilities\ArrayHelper::getValue($data, "id");
        $terms  = Joomla\Utilities\ArrayHelper::getValue($data, "terms", false, "bool");

        $redirectOptions = array(
            "view" => "project",
            "id"   => $itemId
        );

        $model = $this->getModel();
        /** @var $model CrowdfundingModelProject */

        // Get component parameters
        $params = JComponentHelper::getParams($this->option);
        /** @var  $params Joomla\Registry\Registry */

        $form = $model->getForm($data, false);
        /** @var $form JForm */

        if (!$form) {
            throw new Exception(JText::_("COM_CROWDFUNDING_ERROR_FORM_CANNOT_BE_LOADED"));
        }

        // Test if the data is valid.
        $validData = $model->validate($form, $data);
        if ($validData === false) {
            $this->displayNotice($form->getErrors(), $redirectOptions);
            return;
        }

        if (!empty($itemId)) { // Validate owner if the item is not new.

            $userId = JFactory::getUser()->get("id");

            $validator = new Crowdfunding\Validator\Project\Owner(JFactory::getDbo(), $itemId, $userId);
            if (!$validator->isValid()) {
                $this->displayWarning(JText::_('COM_CROWDFUNDING_ERROR_INVALID_PROJECT'), $redirectOptions);
                return;
            }

        } else { // Verify terms of use during the process of creating a project.

            if ($params->get("project_terms", 0) and !$terms) {
                $redirectOptions = array("view" => "project");
                $this->displayWarning(JText::_("COM_CROWDFUNDING_ERROR_TERMS_NOT_ACCEPTED"), $redirectOptions);
                return;
            }

        }

        // Include plugins to validate content.
        $dispatcher = JEventDispatcher::getInstance();
        JPluginHelper::importPlugin('content');

        // Trigger onContentValidate event.
        $context = $this->option . ".basic";
        $results = $dispatcher->trigger("onContentValidate", array($context, &$validData, &$params));

        // If there is an error, redirect to current step.
        foreach ($results as $result) {
            if ($result["success"] == false) {
                $this->displayWarning(Joomla\Utilities\ArrayHelper::getValue($result, "message"), $redirectOptions);
                return;
            }
        }

        try {

            // Store the project data.
            $itemId = $model->save($validData);

            // Set the project ID to redirect options.
            $redirectOptions["id"] = $itemId;

            // Get the images from the session.
            $images = $app->getUserState(Crowdfunding\Constants::CROPPED_IMAGES_CONTEXT);

            // Store the images to the project record.
            if (!empty($images) and !empty($itemId)) {

                // Get the folder where the images will be stored
                $temporaryFolder = CrowdfundingHelper::getTemporaryImagesFolder();

                // Move the pictures from the temporary folder to the images folder.
                // Store the names of the pictures in project record.
                $model->updateImages($itemId, $images, $temporaryFolder);

                // Remove the pictures from the session.
                $app->setUserState(Crowdfunding\Constants::CROPPED_IMAGES_CONTEXT, null);
            }

        } catch (RuntimeException $e) {
            $this->displayWarning($e->getMessage(), $redirectOptions);
            return;
        } catch (InvalidArgumentException $e) {
            $this->displayWarning($e->getMessage(), $redirectOptions);
            return;
        } catch (Exception $e) {
            JLog::add($e->getMessage());
            throw new Exception(JText::_('COM_CROWDFUNDING_ERROR_SYSTEM'));
        }

        // Redirect to next page
        $redirectOptions = array(
            "view"   => "project",
            "layout" => "funding",
            "id"     => $itemId
        );

        $this->displayMessage(JText::_("COM_CROWDFUNDING_PROJECT_SUCCESSFULLY_SAVED"), $redirectOptions);
    }

    /**
     * Delete image
     */
    public function removeImage()
    {
        // Check for request forgeries.
        JSession::checkToken("get") or jexit(JText::_('JINVALID_TOKEN'));

        // Check for registered user
        $userId = JFactory::getUser()->get("id");
        if (!$userId) {
            $redirectOptions = array(
                "force_direction" => "index.php?option=com_users&view=login"
            );
            $this->displayNotice(JText::_('COM_CROWDFUNDING_ERROR_NOT_LOG_IN'), $redirectOptions);
            return;
        }

        // Get item id
        $itemId          = $this->input->get->getInt("id");
        $redirectOptions = array(
            "view" => "project"
        );

        // Validate project owner.
        $validator = new Crowdfunding\Validator\Project\Owner(JFactory::getDbo(), $itemId, $userId);
        if (!$itemId or !$validator->isValid()) {
            $this->displayWarning(JText::_('COM_CROWDFUNDING_ERROR_INVALID_IMAGE'), $redirectOptions);
            return;
        }

        try {

            $model = $this->getModel();
            $model->removeImage($itemId, $userId);

        } catch (Exception $e) {
            JLog::add($e->getMessage());
            throw new Exception(JText::_('COM_CROWDFUNDING_ERROR_SYSTEM'));
        }

        $redirectOptions["id"] = $itemId;
        $this->displayMessage(JText::_('COM_CROWDFUNDING_IMAGE_DELETED'), $redirectOptions);
    }
}
