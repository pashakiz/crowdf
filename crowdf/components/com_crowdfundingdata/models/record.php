<?php
/**
 * @package      CrowdfundingData
 * @subpackage   Component
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2014 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// no direct access
defined('_JEXEC') or die;

/**
 * Get a list of items
 */
class CrowdfundingDataModelRecord extends JModelForm
{
    /**
     * Returns a reference to the a Table object, always creating it.
     *
     * @param   string $type    The table type to instantiate
     * @param   string $prefix A prefix for the table class name. Optional.
     * @param   array  $config Configuration array for model. Optional.
     *
     * @return  JTable  A database object
     * @since   1.6
     */
    public function getTable($type = 'Record', $prefix = 'CrowdfundingDataTable', $config = array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }

    /**
     * Method to get the record form.
     *
     * @param   array   $data     An optional array of data for the form to interrogate.
     * @param   boolean $loadData True if the form is to load its own data (default case), false if not.
     *
     * @return  JForm   A JForm object on success, false on failure
     * @since   1.6
     */
    public function getForm($data = array(), $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm($this->option . '.record', 'record', array('control' => 'jform', 'load_data' => $loadData));
        if (empty($form)) {
            return false;
        }

        return $form;
    }

    /**
     * Method to get the data that should be injected in the form.
     *
     * @return  mixed   The data for the form.
     * @since   1.6
     */
    protected function loadFormData()
    {
        // Check the session for previously entered form data.
        $data = JFactory::getApplication()->getUserState($this->option . '.edit.record.data', array());
        if (empty($data)) {
            $data = $this->getItem();
        }

        return $data;
    }

    public function getItem()
    {
        return array();
    }


    /**
     * Save data in the database
     *
     * @param array $data   The data of item
     *
     * @return    int      Item ID
     */
    public function save($data)
    {
        $name      = JArrayHelper::getValue($data, "name");
        $email     = JArrayHelper::getValue($data, "email");
        $address   = JArrayHelper::getValue($data, "address");
        $countryId = JArrayHelper::getValue($data, "country_id");
        $projectId = JArrayHelper::getValue($data, "project_id");
        $sessionId = JArrayHelper::getValue($data, "session_id");

        if (!$address) {
            $address = null;
        }

        // Load a record from the database
        $row = $this->getTable();

        $row->set("name", $name);
        $row->set("email", $email);
        $row->set("address", $address);
        $row->set("country_id", $countryId);
        $row->set("project_id", $projectId);
        $row->set("session_id", $sessionId);

        $row->store(true);

        return $row->get("id");
    }
}
