<?php
/**
 * @package      Prism
 * @subpackage   Integrations\Profile
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Prism\Integration\Profile;

defined('JPATH_PLATFORM') or die;

/**
 * This class provides functionality to
 * integrate extensions with the profile of Gravatar.
 *
 * @package      Prism
 * @subpackage   Integrations\Profile
 */
class Gravatar implements ProfileInterface
{
    protected $user_id;
    protected $hash;
    protected $email;

    /**
     * Predefined image sizes.
     *
     * @var array
     */
    protected $avatarSizes = array(
        "icon" => "40",
        "small" => "80",
        "medium" => "160",
        "large" => "200",
    );

    /**
     * Database driver.
     * 
     * @var \JDatabaseDriver
     */
    protected $db;

    protected static $instances = array();

    /**
     * Initialize the object
     *
     * <code>
     * $userId = 1;
     *
     * $profile = new Prism\Integration\Profile\Gravatar(\JFactory::getDbo());
     * </code>
     * 
     * @param  \JDatabaseDriver $db
     */
    public function __construct(\JDatabaseDriver $db)
    {
        $this->db = $db;
    }

    /**
     * Create an object.
     *
     * <code>
     * $userId = 1;
     *
     * $profile = Prism\Integration\Profile\Gravatar::getInstance(\JFactory::getDbo(), $userId);
     * </code>
     *
     * @param  \JDatabaseDriver $db
     * @param  int $id
     *
     * @return null|Gravatar
     */
    public static function getInstance(\JDatabaseDriver $db, $id)
    {
        if (empty(self::$instances[$id])) {
            $item   = new Gravatar($db);
            $item->load($id);

            self::$instances[$id] = $item;
        }

        return self::$instances[$id];
    }

    /**
     * Load user data
     *
     * <code>
     * $userId = 1;
     *
     * $profile = new Prism\Integration\Profile\Gravatar(\JFactory::getDbo());
     * $profile->load($userId);
     * </code>
     * 
     * @param int $id
     */
    public function load($id)
    {
        $query = $this->db->getQuery(true);
        $query
            ->select("a.id AS user_id, a.email, MD5(a.email) as hash")
            ->from($this->db->quoteName("#__users", "a"))
            ->where("a.id = " . (int)$id);

        $this->db->setQuery($query);
        $result = (array)$this->db->loadAssoc();

        $this->bind($result);
    }

    /**
     * Set values to object properties.
     *
     * <code>
     * $data = array(
     *     "name" => "...",
     *     "country" => "...",
     * ...
     * );
     *
     * $profile = new Prism\Integration\Profile\Gravatar(\JFactory::getDbo());
     * $profile->bind($data);
     * </code>
     *
     * @param array $data
     * @param array $ignored
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
     * Provide a link to social profile.
     * This method integrates users with profiles
     * of some Joomla! social extensions.
     *
     * <code>
     * $userId = 1;
     *
     * $profile = new Prism\Integration\Profile\Gravatar(\JFactory::getDbo());
     * $profile->load($userId);
     * 
     * $link = $profile->getLink();
     * </code>
     *
     * @param bool $route Route or not the link.
     *
     * @return string Return a link to the profile.
     */
    public function getLink($route = true)
    {
        return "javascript:void(0)";
    }

    /**
     * Provide a link to social avatar.
     *
     * <code>
     * $userId = 1;
     *
     * $profile = new Prism\Integration\Profile\Gravatar(\JFactory::getDbo());
     * $profile->load($userId);
     * 
     * $avatar = $profile->getAvatar();
     * </code>
     * 
     * @param string $size  One of the following sizes - icon, small, medium, large.
     *
     * @return string Return a link to the picture.
     */
    public function getAvatar($size = "small")
    {
        $avatarSize = (!isset($this->avatarSizes[$size])) ? null : (int)$this->avatarSizes[$size];

        $link = "http://www.gravatar.com/avatar/" . $this->hash;

        if (!empty($avatarSize)) {
            $link .= "?s=" . $avatarSize;
        }

        return $link;
    }

    /**
     * Return a location name where the user lives.
     *
     * <code>
     * $userId = 1;
     *
     * $profile = new Prism\Integration\Profile\Gravatar(\JFactory::getDbo());
     * $profile->load($userId);
     * 
     * $location = $profile->getLocation();
     * </code>
     *
     * @return string
     */
    public function getLocation()
    {
        return "";
    }

    /**
     * Return a country code of a country where the user lives.
     *
     * <code>
     * $userId = 1;
     *
     * $profile = new Prism\Integration\Profile\Gravatar(\JFactory::getDbo());
     * $profile->load($userId);
     * 
     * $countryCode = $profile->getCountryCode();
     * </code>
     *
     * @return string
     */
    public function getCountryCode()
    {
        return "";
    }
}
