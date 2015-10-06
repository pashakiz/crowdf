<?php
/**
 * @package      Crowdfunding
 * @subpackage   Updates
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

namespace Crowdfunding;

use Prism;
use Joomla\Utilities\ArrayHelper;

defined('JPATH_PLATFORM') or die;

/**
 * This class provides functionality that manage updates.
 *
 * @package      Crowdfunding
 * @subpackage   Updates
 */
class Updates extends Prism\Database\ArrayObject
{
    protected $allowedStates = array(Prism\Constants::SENT, Prism\Constants::NOT_SENT);

    /**
     * Load data about updates from database by project ID.
     *
     * <code>
     * $options = array(
     *     "project_id" => 1, // It can also be an array with IDs.
     *     "period" => 7, // Period in days
     *     "limit" => 10 // Limit the results
     * );
     *
     * $updates   = new Crowdfunding\Updates(\JFactory::getDbo());
     * $updates->load($options);
     *
     * foreach($updates as $item) {
     *      echo $item->title;
     *      echo $item->record_date;
     * }
     * </code>
     *
     * @param array $options
     */
    public function load($options = array())
    {
        $query = $this->db->getQuery(true);

        $query
            ->select("a.id, a.title, a.description, a.record_date, a.project_id")
            ->from($this->db->quoteName("#__crowdf_updates", "a"));

        // Filter by project ID.
        $projectId = ArrayHelper::getValue($options, "project_id", 0, "int");
        $query->where("a.project_id = " . (int)$projectId);

        // Filter by period.
        $period = ArrayHelper::getValue($options, "period", 0, "int");
        if (!empty($period) and ($period > 0)) {
            $query->where("a.record_date >= DATE_SUB(NOW(), INTERVAL ".$period." DAY)");
        }

        // Set limit.
        $limit = ArrayHelper::getValue($options, "limit", 0, "int");
        if (!empty($limit)) {
            $this->db->setQuery($query, 0, $limit);
        } else {
            $this->db->setQuery($query);
        }

        $this->items = (array)$this->db->loadAssocList();
    }

    /**
     * Return an array that contains updates IDs.
     *
     * <code>
     * $options = array(
     *     "project_id" => 1
     * );
     *
     * $updates   = new Crowdfunding\Updates(\JFactory::getDbo());
     * $updates->load($options);
     *
     * $keys = $updates->getKeys();
     * </code>
     *
     * @return array
     */
    public function getKeys()
    {
        $keys = array();
        foreach ($this->items as $item) {
            $keys[] = (int)$item["id"];
        }

        return $keys;
    }

    /**
     * Change the state of update records.
     *
     * <code>
     * $ids = array(1, 2, 3, 4, 5);
     *
     * $updates = new Crowdfunding\Updates(\JFactory::getDbo());
     * $updates->changeState($ids);
     * </code>
     *
     * @param int $state 1 = Sent; 0 = Not sent;
     * @param null|array $ids
     */
    public function changeState($state, $ids = null)
    {
        if (is_null($ids) and !empty($this->items)) {
            $ids = $this->getKeys();
        }

        if (!in_array($state, $this->allowedStates)) {
            $state = 0;
        }

        if (!empty($ids)) {
            $query = $this->db->getQuery(true);
            $query
                ->update($this->db->quoteName("#__crowdf_updates"))
                ->set($this->db->quoteName("state") ."=". (int)$state)
                ->where($this->db->quoteName("id") ." IN (".implode(",", $ids).")");

            $this->db->setQuery($query);
            $this->db->execute();
        }

    }
}
