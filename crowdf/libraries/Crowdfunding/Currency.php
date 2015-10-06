<?php
/**
 * @package      Crowdfunding
 * @subpackage   Currencies
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

namespace Crowdfunding;

use Prism;

defined('JPATH_PLATFORM') or die;

/**
 * This class contains methods that are used for managing currency.
 *
 * @package      Crowdfunding
 * @subpackage   Currencies
 */
class Currency extends Prism\Database\TableImmutable
{
    protected $id;
    protected $title;
    protected $code;
    protected $symbol;
    protected $position;

    protected static $instances = array();

    /**
     * Create an object or return existing one.
     *
     * <code>
     * $currencyId = 1;
     *
     * $currency   = Crowdfunding\Currency::getInstance(\JFactory::getDbo(), $currencyId);
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
            $item = new Currency($db);
            $item->load($id);

            self::$instances[$id] = $item;
        }

        return self::$instances[$id];
    }

    /**
     * Load currency data from database by ID.
     *
     * <code>
     * $keys = array(
     *     "id" => 1,
     *     "code" => "EUR"
     * );
     *
     * $currency   = new Crowdfunding\Currency(\JFactory::getDbo());
     * $currency->load($currencyId);
     * </code>
     *
     * @param int|array $keys
     * @param array $options
     */
    public function load($keys, $options = array())
    {
        $query = $this->db->getQuery(true);
        $query
            ->select("a.id, a.title, a.code, a.symbol, a.position")
            ->from($this->db->quoteName("#__crowdf_currencies", "a"));

        if (is_array($keys)) {
            foreach ($keys as $key => $value) {
                $query->where($this->db->quoteName($key) ." = " . $this->db->quote($value));
            }
        } else {
            $query->where("a.id = " . (int)$keys);
        }

        $this->db->setQuery($query);
        $result = (array)$this->db->loadAssoc();

        $this->bind($result);
    }

    /**
     * Return currency ID.
     *
     * <code>
     * $currencyId  = 1;
     *
     * $currency    = new Crowdfunding\Currency(\JFactory::getDbo());
     * $currency->load($currencyId);
     *
     * if (!$currency->getId()) {
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
     * Return currency code (abbreviation).
     *
     * <code>
     * $currencyId  = 1;
     *
     * $currency    = new Crowdfunding\Currency(\JFactory::getDbo());
     * $currency->load($currencyId);
     *
     * // Return GBP
     * $code = $currency->getCode();
     * </code>
     *
     * @return int
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Return currency symbol.
     *
     * <code>
     * $currencyId  = 1;
     *
     * $currency    = new Crowdfunding\Currency(\JFactory::getDbo());
     * $currency->load($currencyId);
     *
     * // Return £
     * $symbol = $currency->getSymbol();
     * </code>
     *
     * @return int
     */
    public function getSymbol()
    {
        return $this->symbol;
    }

    /**
     * Return the position of currency symbol.
     *
     * <code>
     * $currencyId  = 1;
     *
     * $currency    = new Crowdfunding\Currency(\JFactory::getDbo());
     * $currency->load($currencyId);
     *
     * // Return 0 = beginning; 1 = end;
     * if (0 == $currency->getPosition()) {
     * ...
     * }
     * </code>
     *
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }
}
