<?php
/**
 * @package      Crowdfunding
 * @subpackage   Users
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

namespace Crowdfunding\User;

use Prism;
use Joomla\Utilities\ArrayHelper;

defined('JPATH_PLATFORM') or die;

/**
 * This class provides functionality that manage locations.
 *
 * @package      Crowdfunding
 * @subpackage   Users
 */
class Users extends Prism\Database\ArrayObject
{
    /**
     * Load users data from database.
     *
     * <code>
     * $options = array(
     *      "ids" => array(1,2,3,4,5)
     * );
     *
     * $users   = new Crowdfunding\Users(\JFactory::getDbo());
     * $users->load($options);
     *
     * foreach($users as $user) {
     *   echo $user["id"];
     *   echo $user["name"];
     * }
     * </code>
     *
     * @param array $options
     */
    public function load($options = array())
    {
        // Filter by users IDs.
        $ids = ArrayHelper::getValue($options, "ids", array(), "array");
        ArrayHelper::toInteger($ids);

        if (!empty($ids)) {
            $query = $this->db->getQuery(true);

            $query
                ->select("a.id, a.name, a.email")
                ->from($this->db->quoteName("#__users", "a"));

            $query->where("a.id IN (" . implode(",", $ids) . ")");

            $this->db->setQuery($query);

            $this->items = (array)$this->db->loadAssocList();
        }
    }

    /**
     * Return user object.
     *
     * <code>
     * $options = array(
     *      "ids" => array(1,2,3,4,5)
     * );
     *
     * $users   = new Crowdfunding\Users(\JFactory::getDbo());
     * $users->load($options);
     *
     * $userId = 1;
     * $user = $users->getUser($userId);
     * </code>
     *
     * @param int $userId
     *
     * @return null|User
     */
    public function getUser($userId)
    {
        $item = null;

        foreach ($this->items as $user) {
            if ($userId == $user["id"]) {
                $item = new User(\JFactory::getDbo());
                $item->bind($user);
                break;
            }
        }

        return $item;
    }
}
