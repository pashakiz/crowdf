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
 * Crowdfunding projects controller
 *
 * @package      Crowdfunding
 * @subpackage   Components
 */
class CrowdfundingControllerProjects extends Prism\Controller\Admin
{
    public function __construct($config = array())
    {
        parent::__construct($config);

        // Define task mappings.

        // Value = 0
        $this->registerTask('disapprove', 'approve');

        // Value = 0
        $this->registerTask('unfeatured', 'featured');
    }

    public function getModel($name = 'Project', $prefix = 'CrowdfundingModel', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);

        return $model;
    }

    public function approve()
    {
        // Check for request forgeries
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        // Get items to publish from the request.
        $cid  = $this->input->get('cid', array(), 'array');
        $data = array(
            'approve'    => Prism\Constants::APPROVED,
            'disapprove' => Prism\Constants::NOT_APPROVED
        );

        $task  = $this->getTask();
        $value = JArrayHelper::getValue($data, $task, 0, 'int');

        $redirectOptions = array(
            "view" => "projects"
        );

        // Make sure the item ids are integers
        JArrayHelper::toInteger($cid);
        if (empty($cid)) {
            $this->displayNotice(JText::_($this->text_prefix . '_NO_ITEM_SELECTED'), $redirectOptions);

            return;
        }

        // Get the model.
        $model = $this->getModel();

        try {

            $model->approve($cid, $value);

        } catch (RuntimeException $e) {

            $this->displayWarning($e->getMessage(), $redirectOptions);
            return;

        } catch (Exception $e) {
            JLog::add($e->getMessage());
            throw new Exception(JText::_('COM_Crowdfunding_ERROR_SYSTEM'));
        }

        if ($value == 1) {
            $msg = $this->text_prefix . '_N_ITEMS_APPROVED';
        } else {
            $msg = $this->text_prefix . '_N_ITEMS_DISAPPROVED';
        }

        $this->displayMessage(JText::plural($msg, count($cid)), $redirectOptions);
    }

    /**
     * Method to toggle the featured setting of a list of items.
     *
     * @throws Exception
     * @return  void
     * @since   1.6
     */
    public function featured()
    {
        // Check for request forgeries
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        $ids = $this->input->get('cid', array(), 'array');

        $values = array(
            'featured'   => Prism\Constants::FEATURED,
            'unfeatured' => Prism\Constants::NOT_FEATURED
        );

        $task  = $this->getTask();
        $value = JArrayHelper::getValue($values, $task, 0, 'int');

        $redirectOptions = array(
            "view" => "projects"
        );

        // Make sure the item ids are integers
        JArrayHelper::toInteger($ids);
        if (!$ids) {
            $this->displayNotice(JText::_($this->text_prefix . '_NO_ITEM_SELECTED'), $redirectOptions);

            return;
        }

        // Get the model.
        $model = $this->getModel();

        try {

            $model->featured($ids, $value);

        } catch (Exception $e) {
            JLog::add($e->getMessage());
            throw new Exception(JText::_('COM_Crowdfunding_ERROR_SYSTEM'));
        }

        if ($value == 1) {
            $msg = $this->text_prefix . '_N_ITEMS_SET_AS_FEATURED';
        } else {
            $msg = $this->text_prefix . '_N_ITEMS_SET_AS_NOT_FEATURED';
        }

        $this->displayMessage(JText::plural($msg, count($ids)), $redirectOptions);
    }

    /**
     * Method to toggle the publish setting of a list of items.
     *
     * @throws Exception
     * @return  void
     * @since   1.6
     */
    public function publish()
    {
        // Check for request forgeries
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        $ids = $this->input->get('cid', array(), 'array');

        $values = array(
            'publish'   => Prism\Constants::PUBLISHED,
            'unpublish' => Prism\Constants::UNPUBLISHED,
            'trash'     => Prism\Constants::TRASHED,
        );

        $task  = $this->getTask();
        $value = JArrayHelper::getValue($values, $task, 0, 'int');

        $redirectOptions = array(
            "view" => "projects"
        );

        // Make sure the item ids are integers
        JArrayHelper::toInteger($ids);
        if (!$ids) {
            $this->displayNotice(JText::_($this->text_prefix . '_NO_ITEM_SELECTED'), $redirectOptions);

            return;
        }

        // Get the model.
        $model = $this->getModel();

        try {

            $model->publish($ids, $value);

        } catch (RuntimeException $e) {
            $this->displayWarning($e->getMessage(), $redirectOptions);
            return;
        } catch (Exception $e) {
            JLog::add($e->getMessage());
            throw new Exception(JText::_('COM_Crowdfunding_ERROR_SYSTEM'));
        }

        if ($value == 1) {
            $msg = $this->text_prefix . '_N_ITEMS_PUBLISHED';
        } else {
            $msg = $this->text_prefix . '_N_ITEMS_UNPUBLISHED';
        }

        $this->displayMessage(JText::plural($msg, count($ids)), $redirectOptions);
    }
}
