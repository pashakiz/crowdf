<?php
/**
 * @package      Crowdfunding
 * @subpackage   Projects
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

namespace Crowdfunding;

use Prism;
use Joomla\Utilities\ArrayHelper;

defined('JPATH_PLATFORM') or die;

/**
 * This class provides functionality that manage projects.
 *
 * @package      Crowdfunding
 * @subpackage   Projects
 */
class Projects extends Prism\Database\ArrayObject
{
    /**
     * Load projects from database by IDs.
     *
     * <code>
     * $options = array(
     *    "ids"       => array(1,2,3),
     *    "published" => Prism\Constants::PUBLISHED,
     *    "approved" => Prism\Constants::APPROVED
     * );
     *
     * $projects    = new Crowdfunding\Projects(\JFactory::getDbo());
     * $projects->load($options);
     *
     * foreach ($projects as $project) {
     *      echo $project["title"];
     *      echo $project["funding_start"];
     * }
     * </code>
     *
     * @param array $options
     *
     * @return array
     */
    public function load($options = array())
    {
        $ids = (!isset($options["ids"])) ? array() : $options["ids"];

        ArrayHelper::toInteger($ids);
        if (!empty($ids)) {

            // Prepare and return main query.
            $query = $this->getQuery();

            // Prepare the query to load project by IDs.
            $query->where("a.id IN ( " . implode(",", $ids) . " )");

            // Prepare project states in the query.
            $this->prepareQueryStates($query, $options);

            $this->db->setQuery($query);

            $this->items = (array)$this->db->loadAssocList();
        }
    }

    /**
     * Load projects from database.
     *
     * <code>
     * $phrase  = "Gamification";
     *
     * $options = array(
     *  "published" => Prism\Constants::PUBLISHED,
     *  "approved" => Prism\Constants::APPROVED
     * );
     *
     * $projects    = new Crowdfunding\Projects(\JFactory::getDbo());
     * $projects->loadByString($phrase, $options);
     *
     * foreach ($projects as $project) {
     *      echo $project->title;
     *      echo $project->funding_start;
     * }
     * </code>
     *
     * @param string $phrase
     * @param array $options
     *
     * @return array
     */
    public function loadByString($phrase, $options = array())
    {
        $results = array();

        if (!empty($phrase)) {

            // Prepare and return main query.
            $query = $this->getQuery();

            // Prepare LIKE filter.
            $escaped = $this->db->escape($phrase, true);
            $quoted  = $this->db->quote("%" . $escaped . "%", false);
            $query->where("a.title LIKE " . $quoted);

            // Prepare project states in the query.
            $this->prepareQueryStates($query, $options);

            $query->order("a.title ASC");

            $this->db->setQuery($query);
            $results = (array)$this->db->loadAssocList();

        }

        $this->items = $results;
    }

    /**
     * Prepare the main query.
     *
     * @return \JDatabaseQuery
     */
    protected function getQuery()
    {
        // Load project data
        $query = $this->db->getQuery(true);

        $query
            ->select(
                "a.id, a.title, a.alias, " .
                $query->concatenate(array("a.id", "a.alias"), ":") . ' AS slug, ' .
                $query->concatenate(array("b.id", "b.alias"), ":") . ' AS catslug'
            )
            ->from($this->db->quoteName("#__crowdf_projects", "a"))
            ->innerJoin($this->db->quoteName("#__categories", "b") . " ON a.catid = b.id");

        return $query;
    }

    /**
     * Prepare the state of the project in where clause of the query.
     *
     * @param \JDatabaseQuery $query
     * @param array $options
     */
    protected function prepareQueryStates(&$query, $options = array())
    {
        // Filter by state published.
        $published = ArrayHelper::getValue($options, "published", 0, "int");
        if (!empty($published)) {
            $query->where("a.published = " . (int)$published);
        }

        // Filter by state approved.
        $approved = ArrayHelper::getValue($options, "approved", 0, "int");
        if (!empty($approved)) {
            $query->where("a.approved = " . (int)$approved);
        }
    }

    /**
     * Count and return rewards number of the projects.
     *
     * <code>
     * $projectsIds = array(1,2,3);
     *
     * $projects    = new Crowdfunding\Projects(\JFactory::getDbo());
     * $projects->load($projectsIds);
     *
     * $rewardsNumber = $projects->getRewardsNumber();
     * </code>
     *
     * @param array $ids Projects IDs
     *
     * @return array
     */
    public function getRewardsNumber($ids = array())
    {
        // If it is missing IDs as parameter, get the IDs of the current items.
        if (!$ids and !empty($this->items)) {
            $ids = $this->getKeys();
        }

        $results = array();

        // If there are no IDs, return empty array.
        if (!empty($ids)) {

            // Create a new query object.
            $query = $this->db->getQuery(true);

            $query
                ->select("a.project_id, COUNT(*) as number")
                ->from($this->db->quoteName("#__crowdf_rewards", "a"))
                ->where("a.project_id IN (" . implode(",", $ids) . ")")
                ->group("a.project_id");

            $this->db->setQuery($query);

            $results = (array)$this->db->loadAssocList("project_id");
        }

        return $results;
    }

    /**
     * Count and return transactions number.
     *
     * <code>
     * $projectsIds = array(1,2,3);
     *
     * $projects           = new Crowdfunding\Projects(\JFactory::getDbo());
     * $transactionsNumber = $projects->getTransactionsNumber($projectsIds);
     * </code>
     *
     * @param array $ids Projects IDs
     *
     * @return array
     */
    public function getTransactionsNumber($ids = array())
    {
        // If it is missing IDs as parameter, get the IDs of the current items.
        if (!$ids and !empty($this->items)) {
            $ids = $this->getKeys();
        }

        $results = array();

        // If there are no IDs, return empty array.
        if (!empty($ids)) {

            // Create a new query object.
            $query = $this->db->getQuery(true);

            $query
                ->select("a.project_id, COUNT(*) as number")
                ->from($this->db->quoteName("#__crowdf_transactions", "a"))
                ->where("a.project_id IN (" . implode(",", $ids) . ")")
                ->group("a.project_id");

            $this->db->setQuery($query);

            $results = (array)$this->db->loadAssocList("project_id");
        }

        return $results;
    }

    /**
     * Prepare an array that will be used as options in drop down form element.
     *
     * <code>
     * $phrase  = "Gamification";
     *
     * $options = array(
     *    "published" => Prism\Constants::PUBLISHED,
     *    "approved" => Prism\Constants::APPROVED
     * );
     *
     * $projects    = new Crowdfunding\Projects(\JFactory::getDbo());
     * $projects->loadByString($phrase, $options);
     *
     * $formOptions = $projects->toOptions();
     * </code>
     *
     * @return array
     */
    public function toOptions()
    {
        $options = array();

        foreach ($this->items as $item) {
            $options[] = array(
                "id"   => $item["id"],
                "name" => $item["title"]
            );
        }

        return $options;
    }

    /**
     * Return an array that contains IDs of the items.
     *
     * <code>
     * $phrase = "Gamification";
     *
     * $projects   = new Crowdfunding\Projects(\JFactory::getDbo());
     * $projects->loadByString($phrase);
     *
     * $keys = $projects->getKeys();
     * </code>
     *
     * @return array
     */
    public function getKeys()
    {
        $keys = array();

        foreach ($this->items as $item) {
            $keys[] = $item["id"];
        }

        return $keys;
    }

    /**
     * Return project object by ID.
     *
     * <code>
     * $phrase = "Gamification";
     * $projectId = 1;
     *
     * $projects   = new Crowdfunding\Projects(\JFactory::getDbo());
     * $projects->loadByString($phrase);
     *
     * $project = $projects->getProject($projectId);
     * </code>
     *
     * @param int $projectId
     *
     * @return null|Project
     */
    public function getProject($projectId)
    {
        $item = null;

        foreach ($this->items as $project) {
            if ($projectId == $project["id"]) {
                $item = new Project(\JFactory::getDbo());
                $item->bind($project);
                break;
            }
        }

        return $item;
    }
}
