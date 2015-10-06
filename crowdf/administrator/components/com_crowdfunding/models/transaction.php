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

class CrowdfundingModelTransaction extends JModelAdmin
{
    protected $event_transaction_change_state = null;

    public function __construct($config = array())
    {

        parent::__construct($config);

        if (isset($config['event_transaction_change_state'])) {
            $this->event_transaction_change_state = $config['event_transaction_change_state'];
        } elseif (empty($this->event_transaction_change_state)) {
            $this->event_transaction_change_state = 'onTransactionChangeState';
        }
    }

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
    public function getTable($type = 'Transaction', $prefix = 'CrowdfundingTable', $config = array())
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
        $form = $this->loadForm($this->option . '.transaction', 'transaction', array('control' => 'jform', 'load_data' => $loadData));
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
        $data = JFactory::getApplication()->getUserState($this->option . '.edit.transaction.data', array());
        if (empty($data)) {
            $data = $this->getItem();
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
        $id              = JArrayHelper::getValue($data, "id");
        $txnAmount       = JArrayHelper::getValue($data, "txn_amount");
        $txnCurrency     = JArrayHelper::getValue($data, "txn_currency");
        $txnStatus       = JArrayHelper::getValue($data, "txn_status");
        $txnId           = JArrayHelper::getValue($data, "txn_id");
        $parentTxnId     = JArrayHelper::getValue($data, "parent_txn_id");
        $serviceProvider = JArrayHelper::getValue($data, "service_provider");
        $investorId      = JArrayHelper::getValue($data, "investor_id");

        // Load a record from the database.
        $row = $this->getTable();
        $row->load($id);

        $this->prepareStatus($row, $txnStatus);

        // Store the transaction data.
        $row->set("txn_amount", $txnAmount);
        $row->set("txn_currency", $txnCurrency);
        $row->set("txn_status", $txnStatus);
        $row->set("txn_id", $txnId);
        $row->set("parent_txn_id", $parentTxnId);
        $row->set("service_provider", $serviceProvider);
        $row->set("investor_id", $investorId);

        $row->store();

        return $row->get("id");
    }

    protected function prepareStatus(&$row, $newStatus)
    {
        // Check for changed transaction status.
        $oldStatus = $row->txn_status;

        if ((strcmp($oldStatus, $newStatus) != 0)) {

            // Include the content plugins for the on save events.
            JPluginHelper::importPlugin('crowdfundingpayment');

            // Trigger the onTransactionChangeStatus event.
            $dispatcher = JEventDispatcher::getInstance();
            $dispatcher->trigger($this->event_transaction_change_state, array($this->option . '.' . $this->name, &$row, $oldStatus, $newStatus));
        }
    }
}
