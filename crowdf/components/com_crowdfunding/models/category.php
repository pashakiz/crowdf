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

class CrowdfundingModelCategory extends JModelList
{
    protected $items   = null;
    protected $numbers = null;
    protected $params  = null;

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
                0,1,2,3,4,5,6,7,8,9,10
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

        // Filter by country
        $value = $app->input->get("filter_country", "", "cmd");
        $this->setState($this->context . '.filter_country', $value);

        // Filter by location
        $value = $app->input->get("filter_location", 0, "int");
        $this->setState($this->context . '.filter_location', $value);

        // Filter by phrase
        $value = $app->input->get("filter_phrase");
        $this->setState($this->context . '.filter_phrase', $value);

        // Filter by filter type
        $value = $app->input->get("filter_fundingtype", "", "cmd");
        $this->setState($this->context . '.filter_fundingtype', $value);

        // Filter by filter type
        $value = $app->input->get("filter_projecttype", 0, "uint");
        $this->setState($this->context . '.filter_projecttype', $value);

        // Filter by filter date.
        $value = $app->input->get("filter_date", 0, "uint");
        $this->setState($this->context . '.filter_date', $value);

        // Filter by funding state.
        $value = $app->input->get("filter_funding_state", 0, "uint");
        $this->setState($this->context . '.filter_funding_state', $value);

        // Filter by featured state.
        $value = $app->input->get("filter_featured");
        $this->setState($this->context . '.filter_featured', $value);

        // Set category id
        $catId = $app->input->get("id", 0, "uint");
        $this->setState($this->context . '.category_id', $catId);

        // It is a discovery page and I can filter it by category.
        // If it is a subcategory page, there is a category ID
        if (!$catId) {
            // Filter by category
            $value = $app->input->get("filter_category");
            $this->setState($this->context . '.category_id', $value);
        } else {
            $app->input->set("filter_category", (int)$catId);
        }

        // Set limit
        $value = $app->input->getInt("limit");
        if (!$value) {
            $value = $params->get("items_limit", $app->get('list_limit', 20));
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
        $id .= ':' . $this->getState($this->context . '.category_id');
        $id .= ':' . $this->getState($this->context . '.filter_country');
        $id .= ':' . $this->getState($this->context . '.filter_location');
        $id .= ':' . $this->getState($this->context . '.filter_fundingtype');
        $id .= ':' . $this->getState($this->context . '.filter_projecttype');
        $id .= ':' . $this->getState($this->context . '.filter_phrase');
        $id .= ':' . $this->getState($this->context . '.filter_date');
        $id .= ':' . $this->getState($this->context . '.filter_funding_state');
        $id .= ':' . $this->getState($this->context . '.filter_featured');

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
                'a.id, a.title, a.short_desc, a.image, a.user_id, a.catid, a.featured, ' .
                'a.goal, a.funded, a.funding_start, a.funding_end, a.funding_days, a.funding_type, ' .
                $query->concatenate(array("a.id", "a.alias"), ":") . " AS slug, " .
                'b.name AS user_name, ' .
                $query->concatenate(array("c.id", "c.alias"), ":") . " AS catslug"
            )
        );
        $query->from($db->quoteName('#__crowdf_projects', 'a'));
        $query->innerJoin($db->quoteName('#__users', 'b') . ' ON a.user_id = b.id');
        $query->innerJoin($db->quoteName('#__categories', 'c') . ' ON a.catid = c.id');

        $this->prepareFilters($query);
        $this->prepareFilterDate($query);
        $this->prepareFilterFundingState($query);

        // Filter by state
        $query->where('a.published = 1');
        $query->where('a.approved = 1');

        // Add the list ordering clause.
        $orderString = $this->getOrderString();
        $query->order($db->escape($orderString));

        return $query;
    }

    protected function getOrderString()
    {
        $params    = $this->getState("params");

        $order     = $this->getState("list.ordering");
        $orderDirn = $this->getState("list.direction");

        if (!is_numeric($order)) {
            $order     = $params->get("items_order", Crowdfunding\Constants::ORDER_BY_START_DATE);
            $orderDirn = $params->get("items_order_direction", "desc");
        }

        // Convert direction to uppercase.
        $orderDirn = Joomla\String\String::strtoupper($orderDirn);

        // Validate directions.
        $allowedDirns = array("ASC", "DESC");
        if (!in_array($orderDirn, $allowedDirns)) {
            $orderDirn = "ASC";
        }

        $fundingEndSort = ", a.funding_end ASC";

        switch ($order) {

            case Crowdfunding\Constants::ORDER_BY_NAME:
                $orderCol = "a.title";
                break;

            case Crowdfunding\Constants::ORDER_BY_CREATED_DATE:
                $orderCol = "a.created";
                break;

            case Crowdfunding\Constants::ORDER_BY_START_DATE:
                $orderCol = "a.funding_start";
                break;

            case Crowdfunding\Constants::ORDER_BY_END_DATE:
                $orderCol = "a.funding_end";
                $fundingEndSort = "";
                break;

            case Crowdfunding\Constants::ORDER_BY_POPULARITY:
                $orderCol = "a.hits";
                break;

            case Crowdfunding\Constants::ORDER_BY_FUNDING:
                $orderCol = "a.funded";
                break;

            default: // Ordering
                $orderCol = "a.ordering";
                break;
        }

        $orderString = 'a.featured DESC, ' . $orderCol . ' ' . $orderDirn . $fundingEndSort;

        return $orderString;
    }

    /**
     * Prepare some main filters.
     *
     * @param JDatabaseQuery $query
     */
    protected function prepareFilters(&$query)
    {
        $db     = JFactory::getDbo();

        // Filter by featured state.
        $featured = $this->getState($this->context . ".filter_featured");
        if (!is_null($featured)) {
            if (!$featured) {
                $query->where('a.featured = 0');
            } else {
                $query->where('a.featured = 1');
            }
        }

        // Filter by category ID
        $categoryId = $this->getState($this->context . ".category_id", 0);
        if (!empty($categoryId)) {
            $query->where('a.catid = ' . (int)$categoryId);
        }

        // Filter by project type
        $projectTypeId = $this->getState($this->context . ".filter_projecttype", 0);
        if (!empty($projectTypeId)) {
            $query->where('a.type_id = ' . (int)$projectTypeId);
        }

        // Filter by country
        $countryCode = $this->getState($this->context . ".filter_country");
        if (!empty($countryCode)) {
            $query->innerJoin($db->quoteName("#__crowdf_locations", "l") . " ON a.location_id = l.id");
            $query->where('l.country_code = ' . $db->quote($countryCode));
        }

        // Filter by location
        $locationId = $this->getState($this->context . ".filter_location");
        if (!empty($locationId)) {
            $query->where('a.location_id = ' . (int)$locationId);
        }

        // Filter by funding type
        $filterFundingType = Joomla\String\String::strtoupper(Joomla\String\String::trim($this->getState($this->context . ".filter_fundingtype")));
        if (!empty($filterFundingType)) {
            $allowedFundingTypes = array("FIXED", "FLEXIBLE");
            if (in_array($filterFundingType, $allowedFundingTypes)) {
                $query->where('a.funding_type = ' . $db->quote($filterFundingType));
            }
        }

        // Filter by phrase
        $phrase = $this->getState($this->context . ".filter_phrase");
        if (!empty($phrase)) {
            $escaped = $db->escape($phrase, true);
            $quoted  = $db->quote("%" . $escaped . "%", false);
            $query->where('a.title LIKE ' . $quoted);
        }
    }

    /**
     * Prepare filter by date.
     *
     * @param JDatabaseQuery $query
     */
    protected function prepareFilterDate(&$query)
    {
        $db     = JFactory::getDbo();

        // Filter by date.
        $filter = (int)$this->getState($this->context . ".filter_date");

        switch($filter) {
            case 1: // Starting soon
                jimport("joomla.date.date");
                $date  = new JDate();
                $today = $date->toSql();

                $date->sub(new DateInterval("P7D"));
                $query->where("a.funding_start >= " . $db->quote($date->toSql()) . " AND a.funding_start <= ". $db->quote($today));
                break;

            case 2: // Ending soon
                jimport("joomla.date.date");
                $date  = new JDate();
                $today = $date->toSql();

                $date->add(new DateInterval("P7D"));
                $query->where("a.funding_end >= " . $db->quote($today) . " AND a.funding_start <= ". $db->quote($date->toSql()));
                break;
        }
    }

    /**
     * Prepare filter by funding state.
     *
     * @param JDatabaseQuery $query
     */
    protected function prepareFilterFundingState(&$query)
    {
        $db     = JFactory::getDbo();

        // Filter by funding state.
        $filter = (int)$this->getState($this->context . ".filter_funding_state");

        switch($filter) {
            case 1: // Successfully funded.
                jimport("joomla.date.date");
                $date  = new JDate();
                $today = $date->toSql();

                $query->where("a.funding_end < " . $db->quote($today) . " AND a.funded >= a.goal");
                break;
        }
    }
}
