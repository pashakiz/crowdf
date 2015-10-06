<?php
/**
 * @package      Crowdfunding
 * @subpackage   Categories
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

namespace Crowdfunding;

use Joomla\Utilities\ArrayHelper;
use Joomla\String\String;

defined('JPATH_PLATFORM') or die;

/**
 * This class provides functionality that manage categories.
 *
 * @package      Crowdfunding
 * @subpackage   Categories
 */
class Categories extends \JCategories
{
    /**
     * The property that contains categories.
     *
     * @var array
     */
    protected $data = array();

    /**
     * Database driver.
     *
     * @var \JDatabaseDriver
     */
    protected $db;

    public function __construct($options = array())
    {
        $options['table']     = '#__crowdf_projects';
        $options['extension'] = 'com_crowdfunding';
        parent::__construct($options);
    }

    /**
     * Set database object.
     *
     * <code>
     * $categories   = new Crowdfunding\Categories();
     * $categories->setDb(\JFactory::getDbo());
     * </code>
     *
     * @param \JDatabaseDriver $db
     *
     * @return self
     */
    public function setDb(\JDatabaseDriver $db)
    {
        $this->db = $db;
        return $this;
    }

    /**
     * Count and return the number of subcategories.
     *
     * <code>
     * $ids = array(1, 2, 3, 4);
     *
     * $categories   = new Crowdfunding\Categories();
     * $categories->setDb(\JFactory::getDbo());
     *
     * $number = $categories->getChildNumber($ids);
     * </code>
     *
     * @param array $ids
     * @param array $options
     *
     * @return array
     */
    public function getChildNumber($ids, $options = array())
    {
        ArrayHelper::toInteger($ids);

        if (!$ids) {
            return array();
        }

        $query = $this->db->getQuery(true);

        $query
            ->select("a.parent_id, COUNT(*) as number")
            ->from($this->db->quoteName("#__categories", "a"))
            ->group("a.parent_id")
            ->where("a.parent_id IN (". implode(",", $ids) .")");

        // Filter by state.
        $state = ArrayHelper::getValue($options, "state");
        if (!is_null($state)) {
            $query->where("a.published = ". (int)$state);
        } else {
            $query->where("a.published IN (0,1)");
        }

        $this->db->setQuery($query);

        $results = $this->db->loadAssocList("parent_id");

        if (!$results) {
            $results = array();
        }

        return $results;
    }

    /**
     * Count and return the number of projects in categories.
     *
     * <code>
     * $ids = array(1, 2, 3, 4);
     *
     * $categories   = new Crowdfunding\Categories();
     * $categories->setDb(\JFactory::getDbo());
     *
     * $number = $categories->getProjectsNumber($ids);
     * </code>
     *
     * @param array $ids
     * @param array $options
     *
     * @return array
     */
    public function getProjectsNumber($ids = array(), $options = array())
    {
        ArrayHelper::toInteger($ids);

        // Get the ids from the current items.
        if (!$ids and !empty($this->data)) {
            foreach ($this->data as $category) {
                $ids[] = $category["id"];
            }
        }

        if (!$ids) {
            return array();
        }

        $query = $this->db->getQuery(true);

        $query
            ->select("a.catid, COUNT(*) as number")
            ->from($this->db->quoteName("#__crowdf_projects", "a"))
            ->group("a.catid")
            ->where("a.catid IN (". implode(",", $ids) .")");

        // Filter by state.
        $state = ArrayHelper::getValue($options, "state");
        if (!is_null($state)) {
            $query->where("a.published = ". (int)$state);
        } else {
            $query->where("a.published IN (0,1)");
        }

        // Filter by approve state.
        $approved = ArrayHelper::getValue($options, "approved");
        if (!is_null($approved)) {
            $query->where("a.approved = ". (int)$approved);
        } else {
            $query->where("a.approved IN (0,1)");
        }

        $this->db->setQuery($query);

        $results = (array)$this->db->loadAssocList("catid");

        return $results;
    }

    /**
     * Load categories.
     *
     * <code>
     * $parentId = 2;
     *
     * $options = array(
     *    "offset" => 0,
     *    "limit" => 10,
     *    "order_by" => "a.name",
     *    "order_dir" => "DESC",
     * );
     *
     * $categories   = new Crowdfunding\Categories();
     * $categories->setDb(\JFactory::getDbo());
     *
     * $categories->load($parentId);
     * </code>
     * 
     * @param null|int $parentId Parent ID or "root".
     * @param array $options
     */
    public function load($parentId = null, $options = array())
    {
        $offset    = (isset($options["offset"])) ? $options["offset"] : 0;
        $limit     = (isset($options["limit"])) ? $options["limit"] : 0;
        $orderBy   = (isset($options["order_by"])) ? $options["order_by"] : "a.title";
        $orderDir  = (isset($options["order_dir"])) ? $options["order_dir"] : "ASC";

        $orderDir = String::strtoupper($orderDir);

        if (!in_array($orderDir, array("ASC", "DESC"))) {
            $orderDir = "ASC";
        }

        $query = $this->db->getQuery(true);
        $query
            ->select(
                "a.id, a.title, a.alias, a.description, a.params, " .
                $query->concatenate(array("a.id", "a.alias"), ":") . " AS slug"
            )
            ->from($this->db->quoteName("#__categories", "a"))
            ->where("a.extension = ". $this->db->quote($this->_extension));

        if (!is_null($parentId)) {
            $query->where("a.parent_id = ". (int)$parentId);
        }
        
        $query->order($this->db->quoteName($orderBy) . " " . $orderDir);

        $this->db->setQuery($query, (int)$offset, (int)$limit);

        $this->data = (array)$this->db->loadAssocList("id");
    }

    /**
     * Return the elements as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return (array)$this->data;
    }
}
