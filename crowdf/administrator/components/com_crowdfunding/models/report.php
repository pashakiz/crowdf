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

class CrowdfundingModelReport extends JModelAdmin
{
    protected $items = array();

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
    public function getTable($type = 'Report', $prefix = 'CrowdfundingTable', $config = array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }

    /**
     * Method to get the record form.
     *
     * @param   array   $data     An optional array of data for the form to interogate.
     * @param   boolean $loadData True if the form is to load its own data (default case), false if not.
     *
     * @return  JForm   A JForm object on success, false on failure
     * @since   1.6
     */
    public function getForm($data = array(), $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm($this->option . '.report', 'report', array('control' => 'jform', 'load_data' => $loadData));
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
        $data = JFactory::getApplication()->getUserState($this->option . '.edit.report.data', array());
        if (empty($data)) {
            $data = $this->getItem();
            $data->title = CrowdfundingHelper::getProjectTitle($data->project_id);
        }

        return $data;
    }

    /**
     * Save data into the DB
     *
     * @param array $data   The data of item
     *
     * @return    int      Item ID
     */
    public function save($data)
    {
        $id          = JArrayHelper::getValue($data, "id");
        $subject     = JArrayHelper::getValue($data, "subject");
        $description = JArrayHelper::getValue($data, "description");
        $email       = JArrayHelper::getValue($data, "email");
        $userId      = JArrayHelper::getValue($data, "user_id");

        if (!$email) {
            $email = null;
        }
        if (!$description) {
            $description = null;
        }

        // Load a record from the database
        $row = $this->getTable();
        $row->load($id);

        $row->set("subject", $subject);
        $row->set("description", $description);
        $row->set("email", $email);
        $row->set("user_id", $userId);

        $row->store(true);

        return $row->get("id");
    }
}
