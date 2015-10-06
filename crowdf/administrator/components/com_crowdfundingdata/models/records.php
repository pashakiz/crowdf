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

class CrowdfundingDataModelRecords extends JModelList
{
    /**
     * Constructor.
     *
     * @param   array $config  An optional associative array of configuration settings.
     *
     * @see     JController
     * @since   1.6
     */
    public function __construct($config = array())
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'id', 'a.id',
                'name', 'a.name',
                'project', 'b.title',
                'txn_amount', 'c.txn_amount',
                'txn_id', 'c.txn_id',
                'country', 'd.name',
            );
        }

        parent::__construct($config);
    }

    protected function populateState($ordering = null, $direction = null)
    {
        // Load the component parameters.
        $params = JComponentHelper::getParams($this->option);
        $this->setState('params', $params);

        // Load the filter state.
        $value = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
        $this->setState('filter.search', $value);

        // Load the transaction state filter.
        $value = $this->getUserStateFromRequest($this->context . '.filter.transaction_state', 'filter_transaction_state');
        $this->setState('filter.transaction_state', $value);

        // List state information.
        parent::populateState('a.name', 'asc');
    }

    /**
     * Method to get a store id based on model configuration state.
     *
     * This is necessary because the model is used by the component and
     * different modules that might need different sets of data or different
     * ordering requirements.
     *
     * @param   string $id A prefix for the store id.
     *
     * @return  string      A store id.
     * @since   1.6
     */
    protected function getStoreId($id = '')
    {
        // Compile the store id.
        $id .= ':' . $this->getState('filter.search');

        return parent::getStoreId($id);
    }

    /**
     * Build an SQL query to load the list data.
     *
     * @return  JDatabaseQuery
     * @since   1.6
     */
    protected function getListQuery()
    {
        $db = $this->getDbo();
        /** @var $db JDatabaseDriver */

        // Create a new query object.
        $query = $db->getQuery(true);

        // Select the required fields from the table.
        $query->select(
            $this->getState(
                'list.select',
                'a.id, a.name, a.email, a.country_id, a.user_id, a.project_id, a.transaction_id, ' .
                'b.title AS project, ' .
                'c.txn_id, c.txn_amount, c.txn_currency, c.txn_status, ' .
                'd.name AS country '
            )
        );
        $query->from($db->quoteName('#__cfdata_records', 'a'));
        $query->leftJoin($db->quoteName('#__crowdf_projects', 'b') . ' ON a.project_id = b.id');
        $query->leftJoin($db->quoteName('#__crowdf_transactions', 'c') . ' ON a.transaction_id = c.id');
        $query->leftJoin($db->quoteName('#__crowdf_countries', 'd') . ' ON a.country_id = d.id');

        // Filter by search in title
        $txnState = $this->getState('filter.transaction_state');
        if (is_numeric($txnState)) {
            if ($txnState == 0) {
                $query->where('a.transaction_id = 0'); // Not completed
            } else {
                $query->where('a.transaction_id > 0'); // Completed transaction
            }
        }

        // Filter by search in title
        $search = $this->getState('filter.search');
        if (!empty($search)) {

            if (stripos($search, 'id:') === 0) {
                $query->where('a.id = ' . (int)substr($search, 3));
            } elseif (stripos($search, 'uid:') === 0) {
                $query->where('a.user_id = ' . (int)substr($search, 4));
            } elseif (stripos($search, 'pid:') === 0) {
                $query->where('a.project_id = ' . (int)substr($search, 4));
            } elseif (stripos($search, 'tid:') === 0) {
                $query->where('c.txn_id = ' . $db->quote(substr($search, 4)));
            } else {
                $escaped = $db->escape($search, true);
                $quoted  = $db->quote("%" . $escaped . "%", false);
                $query->where('c.title LIKE ' . $quoted);
            }
        }

        // Add the list ordering clause.
        $orderString = $this->getOrderString();
        $query->order($db->escape($orderString));

        return $query;
    }

    protected function getOrderString()
    {
        $orderCol  = $this->getState('list.ordering');
        $orderDirn = $this->getState('list.direction');

        return $orderCol . ' ' . $orderDirn;
    }
}
