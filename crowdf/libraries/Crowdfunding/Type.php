<?php
/**
 * @package      Crowdfunding
 * @subpackage   Types
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

namespace Crowdfunding;

use Joomla\Utilities\ArrayHelper;
use Prism;

defined('JPATH_PLATFORM') or die;

/**
 * This class provides functionality for managing projects types.
 *
 * @package      Crowdfunding
 * @subpackage   Types
 */
class Type extends Prism\Database\TableImmutable
{
    protected $id = 0;
    protected $title;
    protected $description;
    protected $params = array();

    /**
     * Load a data about a type from database.
     *
     * <code>
     * $typeId  = 1;
     *
     * $type    = new Crowdfunding\Type(\JFactory::getDbo());
     * $type->load($typeId);
     * </code>
     *
     * @param int $keys
     * @param array $options
     */
    public function load($keys, $options = array())
    {
        $query = $this->db->getQuery(true);

        $query
            ->select("a.id, a.title, a.description, a.params")
            ->from($this->db->quoteName("#__crowdf_types", "a"))
            ->where("a.id = " . (int)$keys);

        $this->db->setQuery($query);
        $result = $this->db->loadAssoc();

        if (!$result) {
            $result = array();
        }

        $this->bind($result);
    }

    /**
     * Set data to object properties.
     *
     * <code>
     * $data = array(
     *  "title" => "A ticked for...",
     *  "amount" => "10.00"
     * );
     *
     * $type    = new Crowdfunding\Type();
     * $type->bind($data);
     * </code>
     *
     * @param array $data
     * @param array $ignored
     */
    public function bind($data, $ignored = array())
    {
        if (isset($data["params"])) {
            $this->setParams($data["params"]);
            unset($data["params"]);
        }

        foreach ($data as $key => $value) {
            if (!in_array($key, $ignored)) {
                $this->$key = $value;
            }
        }

    }

    /**
     * Return type ID.
     *
     * <code>
     * $typeId  = 1;
     *
     * $type    = new Crowdfunding\Type(\JFactory::getDbo());
     * $type->load($typeId);
     *
     * if (!$type->getId()) {
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
     * Set type title.
     *
     * <code>
     * $typeId  = 1;
     * $title   = "Standard projects";
     *
     * $type    = new Crowdfunding\Type(\JFactory::getDbo());
     * $type->load($typeId);
     *
     * $type->setTitle($title);
     * $type->store();
     * </code>
     *
     * @param string $title
     *
     * @return self
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Return type title.
     *
     * <code>
     * $typeId  = 1;
     *
     * $type    = new Crowdfunding\Type(\JFactory::getDbo());
     * $type->load($typeId);
     *
     * echo $type->getTitle();
     * </code>
     *
     * @return int
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set type title.
     *
     * <code>
     * $typeId  = 1;
     * $description   = "My description...";
     *
     * $type    = new Crowdfunding\Type(\JFactory::getDbo());
     * $type->load($typeId);
     *
     * $type->setDescription($title);
     * $type->store();
     * </code>
     *
     * @param string $description
     *
     * @return self
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Set type title.
     *
     * <code>
     * $params = array(
     *     "rewards_enabled" = Prism\Constants::ENABLED
     * );
     *
     * $type    = new Crowdfunding\Type(\JFactory::getDbo());
     * $type->load($typeId);
     *
     * $type->setParams($params);
     * $type->store();
     * </code>
     *
     * @param mixed $params
     *
     * @return self
     */
    public function setParams($params)
    {
        if (is_string($params)) {
            $this->params = (array)json_decode($params, true);
        } elseif (is_object($params)) {
            $this->params = ArrayHelper::fromObject($params);
        } elseif (is_array($params)) {
            $this->params = $params;
        } else {
            $this->params = array();
        }

        return $this;
    }

    /**
     * Return type parameters.
     *
     * <code>
     * $typeId  = 1;
     *
     * $type    = new Crowdfunding\Type(\JFactory::getDbo());
     * $type->load($typeId);
     *
     * $params = $type->getParams();
     * </code>
     *
     * @return int
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Return description of the type.
     *
     * <code>
     * $typeId  = 1;
     *
     * $type    = new Crowdfunding\Type();
     * $type->setDb(\JFactory::getDbo());
     * $type->load($typeId);
     *
     * $description = $type->getDescription();
     * </code>
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Check the parameters of the type, if rewards are enabled for this type.
     *
     * <code>
     * $typeId  = 1;
     *
     * $type    = new Crowdfunding\Type(\JFactory::getDbo());
     * $type->load($typeId);
     *
     * if (!$type->isRewardsEnabled()) {
     * ...
     * }
     * </code>
     *
     * @return int
     */
    public function isRewardsEnabled()
    {
        $rewards = false;
        if (isset($this->params["rewards_enabled"])) {
            $rewards = (!$this->params["rewards_enabled"]) ? false : true;
        }

        return $rewards;
    }
}
