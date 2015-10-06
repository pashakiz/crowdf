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
 * Crowdfunding friend mail controller
 *
 * @package     Crowdfunding
 * @subpackage  Components
 */
class CrowdfundingControllerFriendMail extends Prism\Controller\Form\Frontend
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
    public function getModel($name = 'FriendMail', $prefix = 'CrowdfundingModel', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);
        return $model;
    }

    public function send()
    {
        // Check for request forgeries.
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        $redirectOptions = array(
            "view" => "discover"
        );

        $params = JComponentHelper::getParams("com_crowdfunding");
        /** @var  $params Joomla\Registry\Registry */

        if (!$params->get("security_display_friend_form", 0)) {
            $this->displayNotice(JText::_('COM_CROWDFUNDING_ERROR_CANT_SEND_MAIL'), $redirectOptions);
            return;
        }

        // Get the data from the form POST
        $data   = $this->input->post->get('jform', array(), 'array');
        $itemId = Joomla\Utilities\ArrayHelper::getValue($data, "id", 0, "uint");

        // Get project
        $item = Crowdfunding\Project::getInstance(JFactory::getDbo(), $itemId);

        // Prepare redirect link
        $link            = CrowdfundingHelperRoute::getEmbedRoute($item->getSlug(), $item->getCatSlug(), "email");
        $redirectOptions = array(
            "force_direction" => $link
        );

        $model = $this->getModel();
        /** @var $model CrowdfundingModelFriendMail */

        $form = $model->getForm($data, false);
        /** @var $form JForm */

        if (!$form) {
            throw new Exception(JText::_("COM_CROWDFUNDING_ERROR_FORM_CANNOT_BE_LOADED"));
        }

        // Test if the data is valid.
        $validData = $model->validate($form, $data);

        // Check for validation errors.
        if ($validData === false) {
            $this->displayNotice($form->getErrors(), $redirectOptions);
            return;
        }

        try {

            $model->send($validData);

        } catch (Exception $e) {
            JLog::add($e->getMessage());
            throw new Exception(JText::_('COM_CROWDFUNDING_ERROR_SYSTEM'));
        }

        // Redirect to next page
        $this->displayMessage(JText::_("COM_CROWDFUNDING_FRIEND_MAIL_SUCCESSFULLY_SEND"), $redirectOptions);
    }
}
