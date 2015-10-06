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
 * Get a list of items
 */
class CrowdfundingModelCategories extends JModelList
{
    /**
     * Constructor.
     *
     * @param   array  $config An optional associative array of configuration settings.
     *
     * @see     JController
     * @since   1.6
     */
    public function __construct($config = array())
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'id', 'a.id',
                'title', 'a.title'
            );
        }

        parent::__construct($config);
    }

    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @since   1.6
     */
    protected function populateState($ordering = null, $direction = null)
    {
        // List state information.
        parent::populateState('a.title', 'asc');

        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        $value = $app->input->getString("filter_search");
        $this->setState('filter.search', $value);

        $value = $app->input->getString("id");
        $this->setState('filter.parent_id', $value);

        // Load the component parameters.
        $params = $app->getParams($this->option);
        $this->setState('params', $params);

        // Set limit
        $value = $app->input->getInt("limit");
        if (!$value) {
            $value = $params->get("categories_categories_limit", $app->get('list_limit', 20));
        }
        $this->setState('list.limit', $value);

        $value = $app->input->getInt('limitstart', 0);
        $this->setState('list.start', $value);

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
        $id .= ':' . $this->getState('filter.parent_id');
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
        // Create a new query object.
        $db = $this->getDbo();
        /** @var $db JDatabaseDriver */
        $query = $db->getQuery(true);

        // Select the required fields from the table.
        $query->select(
            $this->getState(
                'list.select',
                'a.id, a.title, a.description, a.params, ' .
                $query->concatenate(array("a.id", "a.alias"), ":") . " AS slug"
            )
        )
            ->from($db->quoteName('#__categories', 'a'))
            ->where('a.extension = ' . $db->quote("com_crowdfunding"))
            ->where('a.published = 1')
            ->where('a.level = 1');

        // Filter by search phrase or ID.
        $search = $this->getState('filter.search');
        if (!empty($search)) {
            if (stripos($search, 'id:') === 0) {
                $query->where('a.id = ' . (int)substr($search, 3));
            } else {
                $escaped = $db->escape($search, true);
                $quoted  = $db->quote("%" . $escaped . "%", false);
                $query->where('a.title LIKE ' . $quoted);
            }
        }

        // Filter by parent ID.
        $parentId = $this->getState('filter.parent_id');
        if (!empty($parentId)) {
            $query->where('a.parent_id = ' . (int)$parentId);
        }

        // Add the list ordering clause.
        $orderString = $this->getOrderString();
        $query->order($db->escape($orderString));

        return $query;
    }

    protected function getOrderString()
    {
        $params    = $this->getState("params");

        $order     = $params->get("categories_order", "title");
        $orderDirn = $params->get("categories_dirn", "desc");

        $allowedDirns = array("asc", "desc");
        if (!in_array($orderDirn, $allowedDirns)) {
            $orderDirn = "ASC";
        } else {
            $orderDirn = Joomla\String\String::strtoupper($orderDirn);
        }

        switch ($order) {

            case "ordering":
                $orderCol = "a.rgt";
                break;

            case "created_time":
                $orderCol = "a.created_time";
                break;

            default: // Title
                $orderCol = "a.title";
                break;

        }

        $orderString = $orderCol . ' ' . $orderDirn;

        return $orderString;
    }
}
