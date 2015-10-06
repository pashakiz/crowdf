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

class CrowdfundingDataModelRecord extends JModelAdmin
{
    /**
     * Returns a reference to the a Table object, always creating it.
     *
     * @param   string $type   The table type to instantiate
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

    /**
     * Method to get a single record.
     *
     * @param   integer  $pk  The id of the primary key.
     *
     * @return  mixed    Object on success, false on failure.
     *
     * @since   12.2
     */
    public function getItem($pk = null)
    {
        $pk = (!empty($pk)) ? $pk : (int) $this->getState($this->getName() . '.id');

        $db = $this->getDbo();

        $query = $db->getQuery(true);

        $query
            ->select(
                "a.id, a.name, a.email, a.address, a.country_id, a.project_id, a.transaction_id, a.user_id, a.session_id, " .
                "b.title AS project, " .
                "c.txn_id, c.txn_amount, c.txn_currency, " .
                "d.name AS country, " .
                "e.name AS username, e.registerDate"
            )
            ->from($db->quoteName("#__cfdata_records", "a"))
            ->leftJoin($db->quoteName("#__crowdf_projects", "b") . " ON a.project_id = b.id")
            ->leftJoin($db->quoteName("#__crowdf_transactions", "c") . " ON a.transaction_id = c.id")
            ->leftJoin($db->quoteName("#__crowdf_countries", "d") . " ON a.country_id = d.id")
            ->leftJoin($db->quoteName("#__users", "e") . " ON a.user_id = e.id")
            ->where("a.id = " . (int)$pk);

        $db->setQuery($query);
        $result = $db->loadObject();

        return $result;
    }

    /**
     * Save data into the DB
     *
     * @param array $data The data of item
     *
     * @return    int      Item ID
     */
    public function save($data)
    {
        $id          = Joomla\Utilities\ArrayHelper::getValue($data, "id");
        $email       = Joomla\Utilities\ArrayHelper::getValue($data, "email");
        $name        = Joomla\Utilities\ArrayHelper::getValue($data, "name");
        $address     = Joomla\Utilities\ArrayHelper::getValue($data, "address");
        $countryId   = Joomla\Utilities\ArrayHelper::getValue($data, "country_id");

        if (!$address) {
            $address = null;
        }

        // Load a record from the database
        $row = $this->getTable();
        $row->load($id);

        $row->set("name", $name);
        $row->set("email", $email);
        $row->set("address", $address);
        $row->set("country_id", $countryId);

        $row->store(true);

        return $row->get("id");
    }
}
