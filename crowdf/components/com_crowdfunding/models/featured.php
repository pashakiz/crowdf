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

class CrowdfundingModelFeatured extends JModelList
{
    protected $items = null;
    protected $numbers = null;
    protected $params = null;

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
                'title', 'a.title',
                'ordering', 'a.ordering'
            );
        }

        parent::__construct($config);
    }

    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @param string $ordering
     * @param string $direction
     *
     * @return  void
     * @since   1.6
     */
    protected function populateState($ordering = 'ordering', $direction = 'ASC')
    {
        parent::populateState("a.ordering", "ASC");

        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        // Load parameters
        $params = $app->getParams();
        $this->setState('params', $params);

        // Set limit
        $value = $params->get("items_limit", $app->get('list_limit', 20));
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
//         $id.= ':' . $this->getState($this->context.'.category_id');

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
        /** @var $db JDatabaseMySQLi * */

        // Create a new query object.
        $query = $db->getQuery(true);

        // Select the required fields from the table.
        $query->select(
            $this->getState(
                'list.select',
                'a.id, a.title, a.short_desc, a.image, a.user_id, a.catid, a.featured, ' .
                'a.goal, a.funded, a.funding_start, a.funding_end, a.funding_days, a.funding_type, ' .
                $query->concatenate(array("a.id", "a.alias"), ":") . ' AS slug, ' .
                'b.name AS user_name, ' .
                $query->concatenate(array("c.id", "c.alias"), ":") . " AS catslug"
            )
        );
        $query->from($db->quoteName('#__crowdf_projects', 'a'));
        $query->innerJoin($db->quoteName('#__users', 'b') . ' ON a.user_id = b.id');
        $query->innerJoin($db->quoteName('#__categories', 'c') . ' ON a.catid = c.id');

        // Filter by category ID
        $categoryId = $this->getState($this->context . ".category_id", 0);
        if (!empty($categoryId)) {
            $query->where('a.catid = ' . (int)$categoryId);
        }

        // Filter by states
        $query->where('a.featured  = 1');
        $query->where('a.published = 1');
        $query->where('a.approved  = 1');

        // Add the list ordering clause.
        $orderString = $this->getOrderString();
        $query->order($db->escape($orderString));

        return $query;
    }

    protected function getOrderString()
    {
        $params    = $this->getState("params");
        $order     = $params->get("items_order", "start_date");
        $orderDirn = $params->get("items_order_direction", "desc");

        $allowedDirns = array("asc", "desc");
        if (!in_array($orderDirn, $allowedDirns)) {
            $orderDirn = "ASC";
        } else {
            $orderDirn = Joomla\String\String::strtoupper($orderDirn);
        }

        switch ($order) {

            case "ordering":
                $orderCol = "a.ordering";
                break;

            case "added":
                $orderCol = "a.id";
                break;

            default: // Start date
                $orderCol = "a.funding_start";
                break;

        }

        $orderString = $orderCol . ' ' . $orderDirn;

        return $orderString;
    }

    public function prepareItems($items)
    {
        $result = array();

        if (!empty($items)) {
            foreach ($items as $key => $item) {

                $result[$key] = $item;

                // Calculate funding end date
                if (!empty($item->funding_days)) {

                    $fundingStartDate = new Crowdfunding\Date($item->funding_start);
                    $fundingEndDate = $fundingStartDate->calculateEndDate($item->funding_days);
                    $result[$key]->funding_end = $fundingEndDate->format("Y-m-d");

                }

                // Calculate funded percentage.
                $percent = new Prism\Math();
                $percent->calculatePercentage($item->funded, $item->goal, 0);
                $result[$key]->funded_percents = (string)$percent;

                // Calculate days left
                $today = new Crowdfunding\Date();
                $result[$key]->days_left       = $today->calculateDaysLeft($item->funding_days, $item->funding_start, $item->funding_end);

            }
        }

        return $result;
    }
}
