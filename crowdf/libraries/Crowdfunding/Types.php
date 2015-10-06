<?php
/**
 * @package      Crowdfunding
 * @subpackage   Types
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

namespace Crowdfunding;

use Prism;

defined('JPATH_PLATFORM') or die;

/**
 * This class provides functionality for managing types.
 *
 * @package      Crowdfunding
 * @subpackage   Types
 */
class Types extends Prism\Database\ArrayObject
{
    protected static $instance;

    /**
     * Initialize and create an object.
     *
     * <code>
     * $options = array(
     *  "order_column" => "title", // id or title
     *  "order_direction" => "DESC",
     * );
     *
     * $types    = Crowdfunding\Types::getInstance(\JFactory::getDbo(), $options);
     * </code>
     *
     * @param \JDatabaseDriver $db
     * @param array $options
     *
     * @return self
     */
    public static function getInstance(\JDatabaseDriver $db, $options = array())
    {
        if (is_null(self::$instance)) {
            self::$instance = new Types($db);
            self::$instance->load($options);
        }

        return self::$instance;
    }

    /**
     * Set a database object.
     *
     * <code>
     * $types    = new Crowdfunding\Types();
     * $types->setDb(\JFactory::getDbo());
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
     * Load types data from database.
     *
     * <code>
     * $options = array(
     *  "order_column" => "title", // id or title
     *  "order_direction" => "DESC",
     * );
     *
     * $types    = new Crowdfunding\Types();
     * $types->setDb(\JFactory::getDbo());
     * $types->load($options);
     *
     * foreach ($types as $type) {
     *      echo $type["title"];
     *      echo $type["description"];
     * }
     * </code>
     *
     * @param array $options
     */
    public function load($options = array())
    {
        $query = $this->db->getQuery(true);

        $query
            ->select("a.id, a.title, a.description, a.params")
            ->from($this->db->quoteName("#__crowdf_types", "a"));

        // Order by column
        if (isset($options["order_column"])) {

            $orderString = $this->db->quoteName($options["order_column"]);

            // Order direction
            if (isset($options["order_direction"])) {
                $orderString .= (strcmp("DESC", $options["order_direction"])) ? " DESC" : " ASC";
            }

            $query->order($orderString);
        }

        $this->db->setQuery($query);
        $results = $this->db->loadAssocList();

        if (!empty($results)) {

            foreach ($results as $result) {
                $type = new Type(\JFactory::getDbo());
                $type->bind($result);
                $this->items[] = $type;
            }

        } else {
            $this->items = array();
        }
    }
}
