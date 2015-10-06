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
 * Crowdfunding update controller
 *
 * @package     ITPrism Components
 * @subpackage  Crowdfunding
 */
class CrowdfundingControllerUpdate extends JControllerLegacy
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
    public function getModel($name = 'UpdateItem', $prefix = 'CrowdfundingModel', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);

        return $model;
    }

    /**
     * Method to load data via AJAX
     */
    public function getData()
    {
        // Create response object.
        $response = new Prism\Response\Json();

        // Get the input
        $itemId = $this->input->get->get('id', 0, 'int');
        $userId = JFactory::getUser()->id;

        // Get the model
        $model = $this->getModel();
        /** @var $model CrowdfundingModelUpdateItem */

        try {

            $item = $model->getItem($itemId);

            if ($item->user_id != $userId) {
                $response
                    ->setTitle(JText::_("COM_CROWDFUNDING_FAIL"))
                    ->setText(JText::_("COM_CROWDFUNDING_RECORD_CANNOT_EDIT"))
                    ->failure();

                echo $response;
                JFactory::getApplication()->close();
            }

        } catch (Exception $e) {
            JLog::add($e->getMessage());
            $response
                ->setTitle(JText::_("COM_CROWDFUNDING_FAIL"))
                ->setText(JText::_("COM_CROWDFUNDING_ERROR_SYSTEM"))
                ->failure();

            echo $response;
            JFactory::getApplication()->close();
        }

        if (isset($item) and is_object($item)) {
            $response
                ->setData(Joomla\Utilities\ArrayHelper::fromObject($item))
                ->success();
        } else {
            $response->failure();
        }

        echo $response;
        JFactory::getApplication()->close();
    }

    /**
     * Method to remove records via AJAX.
     *
     * @throws Exception
     * @return  void
     */
    public function remove()
    {
        // Get the input
        $app    = JFactory::getApplication();
        /** $app JApplicationSite */

        // Create response object.
        $response = new Prism\Response\Json();

        $itemId = $app->input->post->get('id', 0, 'int');
        $userId = JFactory::getUser()->get("id");

        // Get the model
        $model = $this->getModel();
        /** @var $model CrowdfundingModelUpdateItem */

        try {

            $item = $model->getItem($itemId);

            if ($item->user_id != $userId) {
                $response
                    ->setTitle(JText::_("COM_CROWDFUNDING_FAIL"))
                    ->setText(JText::_("COM_CROWDFUNDING_RECORD_CANNOT_REMOVED"))
                    ->failure();

                echo $response;
                JFactory::getApplication()->close();
            }

            $model->remove($itemId, $userId);

        } catch (Exception $e) {
            JLog::add($e->getMessage());
            $response
                ->setTitle(JText::_("COM_CROWDFUNDING_FAIL"))
                ->setText(JText::_("COM_CROWDFUNDING_ERROR_SYSTEM_CANNOT_REMOVED"))
                ->failure();

            echo $response;
            JFactory::getApplication()->close();
        }

        $response
            ->setTitle(JText::_("COM_CROWDFUNDING_SUCCESS"))
            ->setText(JText::_("COM_CROWDFUNDING_RECORD_REMOVED_SUCCESSFULLY"))
            ->success();

        echo $response;
        JFactory::getApplication()->close();
    }
}
