<?php
/**
 * @package      Crowdfunding
 * @subpackage   Users
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

namespace Crowdfunding\User;

defined('JPATH_PLATFORM') or die;

/**
 * This class provides functionality that manage user profile.
 *
 * @package      Crowdfunding
 * @subpackage   Users
 */
class User
{
    protected $id;
    protected $name;
    protected $email;

    /**
     * User rewards.
     *
     * @var array
     */
    protected $rewards;

    /**
     * List with followed campaigns.
     *
     * @var null|array
     */
    protected $followed;

    /**
     * Database driver.
     *
     * @var \JDatabaseDriver
     */
    protected $db;

    protected static $instances = array();

    /**
     * Initialize the object.
     *
     * <code>
     * $user    = new Crowdfunding\User(JFactory::getDbo());
     * </code>
     * 
     * @param \JDatabaseDriver  $db
     */
    public function __construct(\JDatabaseDriver $db)
    {
        $this->db = $db;
    }

    /**
     * Create an object or return existing one.
     *
     * <code>
     * $userId = 1;
     *
     * $currency   = Crowdfunding\User::getInstance(\JFactory::getDbo(), $userId);
     * </code>
     *
     * @param \JDatabaseDriver $db
     * @param int             $id
     *
     * @return null|self
     */
    public static function getInstance(\JDatabaseDriver $db, $id)
    {
        if (!isset(self::$instances[$id])) {
            $item = new User($db);
            $item->load($id);

            self::$instances[$id] = $item;
        }

        return self::$instances[$id];
    }

    /**
     * Load user data from database by ID.
     *
     * <code>
     * $userId = 1;
     *
     * $user   = new Crowdfunding\User(\JFactory::getDbo());
     * $user->load($userId);
     * </code>
     *
     * @param int $id
     */
    public function load($id)
    {
        $query = $this->db->getQuery(true);
        $query
            ->select("a.id, a.name, a.email")
            ->from($this->db->quoteName("#__users", "a"))
            ->where("a.id = " . (int)$id);

        $this->db->setQuery($query);
        $result = (array)$this->db->loadAssoc();

        $this->bind($result);
    }

    /**
     * Set data about user to object parameters.
     *
     * <code>
     * $data = array(
     *    "name"  => "John Dow"
     * );
     *
     * $user   = new Crowdfunding\User(\JFactory::getDbo());
     * $user->bind($data);
     * </code>
     *
     * @param array $data
     * @param array $ignored
     *
     */
    public function bind($data, $ignored = array())
    {
        foreach ($data as $key => $value) {
            if (!in_array($key, $ignored)) {
                $this->$key = $value;
            }
        }
    }

    /**
     * Return user ID.
     *
     * <code>
     * $userId  = 1;
     *
     * $user    = new Crowdfunding\User(\JFactory::getDbo());
     * $user->load($userId);
     *
     * if (!$user->getId()) {
     * ....
     * }
     * </code>
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set user ID.
     *
     * <code>
     * $userId  = 1;
     *
     * $user    = new Crowdfunding\User(\JFactory::getDbo());
     * $user->setId($userId)
     * </code>
     *
     * @param int $id
     *
     * @return self
     */
    public function setId($id)
    {
        $this->id = (int)$id;

        return $this;
    }

    /**
     * Return user ID.
     *
     * <code>
     * $userId  = 1;
     *
     * $user    = new Crowdfunding\User(\JFactory::getDbo());
     * $user->load($userId);
     *
     * $name = $user->getName();
     * </code>
     *
     * @return int
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Return user ID.
     *
     * <code>
     * $userId  = 1;
     *
     * $user    = new Crowdfunding\User(\JFactory::getDbo());
     * $user->load($userId);
     *
     * $email = $user->getEmail();
     * </code>
     *
     * @return int
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Return list with followed campaigns.
     *
     * <code>
     * $userId  = 1;
     *
     * $user    = new Crowdfunding\User(\JFactory::getDbo());
     * $user->load($userId)
     *
     * $followedCampaigns = $user->getFollowed()
     * </code>
     *
     * @return array
     */
    public function getFollowed()
    {
        if (is_null($this->followed)) {
            $query = $this->db->getQuery(true);
            $query
                ->select("a.project_id")
                ->from($this->db->quoteName("#__crowdf_followers", "a"))
                ->where("a.user_id = " . (int)$this->id);

            $this->db->setQuery($query);
            $this->followed = (array)$this->db->loadColumn();
        }

        return $this->followed;
    }

    /**
     * Start following campaign.
     *
     * <code>
     * $userId  = 1;
     * $projectId  = 2;
     *
     * $user    = new Crowdfunding\User(\JFactory::getDbo());
     * $user->load($userId)
     *
     * $user->follow($projectId)
     * </code>
     *
     * @param int $projectId
     *
     * @return array
     */
    public function follow($projectId)
    {
        $projectId = (int)$projectId;

        if (!$this->id) {
            throw new \InvalidArgumentException(\JText::_("LIB_CROWDFUNDING_INVALID_USER"));
        }

        if (!$projectId) {
            throw new \InvalidArgumentException(\JText::_("LIB_CROWDFUNDING_INVALID_PROJECT"));
        }

        if (is_null($this->followed)) {
            $this->getFollowed();
        }

        if (!in_array($projectId, $this->followed)) {
            $query = $this->db->getQuery(true);
            $query
                ->insert($this->db->quoteName("#__crowdf_followers"))
                ->set($this->db->quoteName("user_id")    ."=". (int)$this->id)
                ->set($this->db->quoteName("project_id") ."=". (int)$projectId);

            $this->db->setQuery($query);
            $this->db->execute();
        }
    }

    /**
     * Stop follow a campaign.
     *
     * <code>
     * $userId  = 1;
     * $projectId  = 2;
     *
     * $user    = new Crowdfunding\User(\JFactory::getDbo());
     * $user->load($userId)
     *
     * $user->unfollow($projectId)
     * </code>
     *
     * @param int $projectId
     *
     * @return array
     */
    public function unfollow($projectId)
    {
        $projectId = (int)$projectId;

        if (!$this->id) {
            throw new \InvalidArgumentException(\JText::_("LIB_CROWDFUNDING_INVALID_USER"));
        }

        if (!$projectId) {
            throw new \InvalidArgumentException(\JText::_("LIB_CROWDFUNDING_INVALID_PROJECT"));
        }

        if (is_null($this->followed)) {
            $this->getFollowed();
        }

        if (in_array($projectId, $this->followed)) {
            $query = $this->db->getQuery(true);
            $query
                ->delete($this->db->quoteName("#__crowdf_followers"))
                ->where($this->db->quoteName("user_id")    ."=". (int)$this->id)
                ->where($this->db->quoteName("project_id") ."=". (int)$projectId);

            $this->db->setQuery($query);
            $this->db->execute();
        }
    }
}
