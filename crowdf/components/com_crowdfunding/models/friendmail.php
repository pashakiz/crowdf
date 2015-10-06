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

class CrowdfundingModelFriendMail extends JModelForm
{
    /**
     * Method to auto-populate the model state.
     * Note. Calling getState in this method will result in recursion.
     * @since    1.6
     */
    protected function populateState()
    {
        parent::populateState();

        $app = JFactory::getApplication("Site");
        /** @var $app JApplicationSite * */

        // Get the pk of the record from the request.
        $value = $app->input->getInt("id");
        $this->setState($this->getName() . '.id', $value);

    }

    /**
     * Method to get the profile form.
     *
     * The base form is loaded from XML and then an event is fired
     * for users plugins to extend the form with extra fields.
     *
     * @param    array   $data     An optional array of data for the form to interogate.
     * @param    boolean $loadData True if the form is to load its own data (default case), false if not.
     *
     * @return    JForm    A JForm object on success, false on failure
     * @since    1.6
     */
    public function getForm($data = array(), $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm($this->option . '.friendmail', 'friendmail', array('control' => 'jform', 'load_data' => $loadData));
        if (empty($form)) {
            return false;
        }

        $form->bind($data);

        return $form;
    }

    /**
     * Method to get the data that should be injected in the form.
     *
     * @return    mixed    The data for the form.
     * @since    1.6
     */
    protected function loadFormData()
    {
        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        $data = $app->getUserState($this->option . '.edit.friendmail.data', array());

        return $data;
    }

    /**
     * Method to send mail to friend.
     *
     * @param    array    $data    The form data.
     */
    public function send($data)
    {
        // Send email to the administrator
        $subject   = Joomla\Utilities\ArrayHelper::getValue($data, "subject");
        $body      = Joomla\Utilities\ArrayHelper::getValue($data, "message");
        $from      = Joomla\Utilities\ArrayHelper::getValue($data, "sender");
        $fromName  = Joomla\Utilities\ArrayHelper::getValue($data, "sender_name");
        $recipient = Joomla\Utilities\ArrayHelper::getValue($data, "receiver");

        $return = JFactory::getMailer()->sendMail($from, $fromName, $recipient, $subject, $body);

        // Check for an error.
        if ($return !== true) {
            $error = JText::sprintf("COM_CROWDFUNDING_ERROR_MAIL_SENDING_FRIEND");
            JLog::add($error);
        }
    }
}
