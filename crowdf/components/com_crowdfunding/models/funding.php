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

JLoader::register("CrowdfundingModelProject", CROWDFUNDING_PATH_COMPONENT_SITE . "/models/project.php");

class CrowdfundingModelFunding extends CrowdfundingModelProject
{
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
        $form = $this->loadForm($this->option . '.funding', 'funding', array('control' => 'jform', 'load_data' => $loadData));
        /** @var $form JForm */

        if (empty($form)) {
            return false;
        }

        // Prepare date format for the calendar.
        $dateFormat = CrowdfundingHelper::getDateFormat();
        $form->setFieldAttribute("funding_end", "format", $dateFormat);

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

        $data = $app->getUserState($this->option . '.edit.funding.data', array());
        if (!$data) {

            $itemId = (int)$this->getState($this->getName() . '.id');
            $userId = JFactory::getUser()->get("id");

            $data = $this->getItem($itemId, $userId);

            // Prepare date format.
            $dateFormat = CrowdfundingHelper::getDateFormat();

            $dateValidator = new Prism\Validator\Date($data->funding_end);

            // Validate end date. If the date is not valid, generate a valid one.
            // Use minimum allowed days to generate end funding date.
            if (!$dateValidator->isValid()) {

                // Get minimum days.
                $params  = $this->getState("params");
                $minDays = $params->get("project_days_minimum", 30);

                // Generate end date.
                $today   = new Crowdfunding\Date();
                $fundingEndDate = $today->calculateEndDate($minDays);

                $data->funding_end = $fundingEndDate->format("Y-m-d");
            }

            $date              = new JDate($data->funding_end);
            $data->funding_end = $date->format($dateFormat);

        }

        return $data;
    }

    /**
     * Method to save the form data.
     *
     * @param    array    $data    The form data.
     *
     * @return    mixed        The record id on success, null on failure.
     * @since    1.6
     */
    public function save($data)
    {
        $id           = Joomla\Utilities\ArrayHelper::getValue($data, "id");
        $goal         = Joomla\Utilities\ArrayHelper::getValue($data, "goal");
        $fundingType  = Joomla\Utilities\ArrayHelper::getValue($data, "funding_type");
        $fundingEnd   = Joomla\Utilities\ArrayHelper::getValue($data, "funding_end", "0000-00-00");
        $fundingDays  = Joomla\Utilities\ArrayHelper::getValue($data, "funding_days", 0);
        $durationType = Joomla\Utilities\ArrayHelper::getValue($data, "funding_duration_type");

        $keys = array(
            "id" => $id,
            "user_id" => JFactory::getUser()->get("id"),
        );

        // Load a record from the database
        /** @var $row CrowdfundingTableProject */
        $row = $this->getTable();
        $row->load($keys);

        $row->set("goal", $goal);
        $row->set("funding_type", $fundingType);

        $data = array(
            "duration_type" => $durationType,
            "funding_end"   => $fundingEnd,
            "funding_days"  => $fundingDays,
        );

        $this->prepareTable($row, $data);

        $row->store();

        // Trigger the event onContentAfterSave.
        $this->triggerEventAfterSave($row, "funding");

        return $row->get("id");

    }

    /**
     * Prepare and sanitise the table prior to saving.
     *
     * @param CrowdfundingTableProject $table
     * @param array $data
     *
     * @throws RuntimeException
     *
     * @since    1.6
     */
    protected function prepareTable(&$table, $data)
    {
        $durationType = Joomla\Utilities\ArrayHelper::getValue($data, "duration_type");
        $fundingEnd   = Joomla\Utilities\ArrayHelper::getValue($data, "funding_end");
        $fundingDays  = Joomla\Utilities\ArrayHelper::getValue($data, "funding_days");

        switch ($durationType) {

            case "days":

                $table->funding_days = ($fundingDays < 0) ? 0 : (int)$fundingDays;

                // Calculate end date
                if (!empty($table->funding_start)) {
                    $fundingStartDate   = new Crowdfunding\Date($table->funding_start);
                    $fundingEndDate     = $fundingStartDate->calculateEndDate($table->funding_days);
                    $table->funding_end = $fundingEndDate->format("Y-m-d");
                } else {
                    $table->funding_end = "0000-00-00";
                }

                break;

            case "date":

                $dateValidator = new Prism\Validator\Date($fundingEnd);
                if (!$dateValidator->isValid($fundingEnd)) {
                    throw new RuntimeException(JText::_("COM_CROWDFUNDING_ERROR_INVALID_DATE"));
                }

                $date = new JDate($fundingEnd);

                $table->funding_days = 0;
                $table->funding_end  = $date->toSql();

                break;

            default:
                $table->funding_days = 0;
                $table->funding_end  = "0000-00-00";
                break;
        }

    }
}
