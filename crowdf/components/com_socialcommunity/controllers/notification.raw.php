<?php
/**
 * @package      SocialCommunity
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// no direct access
defined('_JEXEC') or die;

/**
 * SocialCommunity notification controller.
 *
 * @package     SocialCommunity
 * @subpackage  Components
 */
class SocialCommunityControllerNotification extends JControllerLegacy
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
    public function getModel($name = 'Notification', $prefix = 'SocialCommunityModel', $config = array('ignore_request' => false))
    {
        $model = parent::getModel($name, $prefix, $config);
        return $model;
    }

    /**
     * This method removes a notification.
     */
    public function remove()
    {
        $itemId = $this->input->getUint("id");
        $userId = JFactory::getUser()->get("id");

        $response = new Prism\Response\Json();

        $validatorOwner = new SocialCommunity\Validator\Notification\Owner(JFactory::getDbo(), $itemId, $userId);
        if (!$validatorOwner->isValid()) {
            $response
                ->setTitle(JText::_('COM_SOCIALCOMMUNITY_FAILURE'))
                ->setText(JText::_('COM_SOCIALCOMMUNITY_ERROR_INVALID_NOTIFICATION'))
                ->failure();

            echo $response;
            JFactory::getApplication()->close();
        }

        try {

            $notification = new SocialCommunity\Notification(JFactory::getDbo());
            $notification->load($itemId);
            $notification->remove();

        } catch (Exception $e) {
            JLog::add($e->getMessage());
            throw new Exception(JText::_('COM_SOCIALCOMMUNITY_ERROR_SYSTEM'));
        }

        $response
            ->setTitle(JText::_('COM_SOCIALCOMMUNITY_SUCCESS'))
            ->setText(JText::_('COM_SOCIALCOMMUNITY_NOTIFICATION_REMOVED_SUCCESSFULLY'))
            ->success();

        echo $response;
        JFactory::getApplication()->close();
    }
}
