<?php
/**
 * @package      Crowdfunding
 * @subpackage   Rewards
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

namespace Crowdfunding;

use Prism;
use Joomla\Utilities\ArrayHelper;

defined('JPATH_PLATFORM') or die;

/**
 * This class provides functionality that manage rewards.
 *
 * @package      Crowdfunding
 * @subpackage   Rewards
 */
class Rewards extends Prism\Database\ArrayObject
{
    protected static $instances = array();

    /**
     * Create and initialize an object.
     *
     * <code>
     * $options = array(
     *     "project_id" => 1,
     *     "state" => Prism\Constants::PUBLISHED
     * );
     *
     * $rewards   = Crowdfunding\Rewards::getInstance(\JFactory::getDbo(), $options);
     * </code>
     *
     * @param \JDatabaseDriver $db
     * @param array            $options
     *
     * @return null|self
     */
    public static function getInstance(\JDatabaseDriver $db, $options = array())
    {
        $projectId = (!isset($options["project_id"])) ? 0 : $options["project_id"];

        if (!isset(self::$instances[$projectId])) {
            $item = new Rewards($db);
            $item->load($options);

            self::$instances[$projectId] = $item;
        }

        return self::$instances[$projectId];
    }

    /**
     * Load rewards data from database, by project ID.
     *
     * <code>
     * $options = array(
     *     "project_id" => 1,
     *     "state" => Prism\Constants::PUBLISHED
     * );
     *
     * $rewards   = new Crowdfunding\Rewards(\JFactory::getDbo());
     * $rewards->load($options);
     *
     * foreach($rewards as $reward) {
     *   echo $reward->title;
     *   echo $reward->amount;
     * }
     * </code>
     *
     * @param array $options
     */
    public function load($options = array())
    {
        $projectId = (!isset($options["project_id"])) ? 0 : $options["project_id"];

        $query = $this->db->getQuery(true);

        $query
            ->select(
                "a.id, a.title, a.description, a.amount, a.number, a.distributed, " .
                "a.delivery, a.image, a.image_thumb, a.image_square"
            )
            ->from($this->db->quoteName("#__crowdf_rewards", "a"))
            ->where("a.project_id = " . (int)$projectId);

        // Get state
        $state = ArrayHelper::getValue($options, "state", 0, "int");
        if (!empty($state)) {
            $query->where("a.published = " . (int)$state);
        }

        $this->db->setQuery($query);
        $results = (array)$this->db->loadAssocList();

        $this->items = $results;
    }

    /**
     * Return an array that contains rewards IDs.
     *
     * <code>
     * $options = array(
     *     "project_id" => 1
     * );
     *
     * $rewards   = new Crowdfunding\Rewards(\JFactory::getDbo());
     * $rewards->load($options);
     *
     * $rewardsKeys = $rewards->getKeys();
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
     * Get number of people who have to receive rewards current rewards.
     *
     * <code>
     * $options = array(
     *     "project_id" => 1
     * );
     *
     * $rewards   = new Crowdfunding\Rewards(\JFactory::getDbo());
     * $rewards->load($options);
     *
     * $receiversNumber = $rewards->countReceivers();
     * </code>
     *
     * @return array
     */
    public function countReceivers()
    {
        $keys = $this->getKeys();
        ArrayHelper::toInteger($keys);

        if (!$keys) {
            return array();
        }

        $query = $this->db->getQuery(true);

        $query
            ->select("a.reward_id, COUNT(a.id) AS funders")
            ->from($this->db->quoteName("#__crowdf_transactions", "a"))
            ->group("a.reward_id")
            ->where("a.reward_id IN ( " . implode(",", $keys) . " )");

        $this->db->setQuery($query);
        $result = (array)$this->db->loadAssocList("reward_id");

        foreach ($this->items as &$item) {
            $item["funders"] = (!isset($result[$item["id"]])) ? 0 : $result[$item["id"]]["funders"];
        }

        unset($item);

        return $result;
    }
}
