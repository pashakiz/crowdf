<?php
/**
 * @package      Crowdfunding
 * @subpackage   Statistics
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

namespace Crowdfunding\Statistics\Transactions;

use Prism;

defined('JPATH_PLATFORM') or die;

/**
 * This is a base class for transactions statistics.
 *
 * @package      Crowdfunding\Statistics
 * @subpackage   Transactions
 */
abstract class Base extends Prism\Database\ArrayObject
{
    /**
     * Database driver.
     *
     * @var \JDatabaseDriver
     */
    protected $db;

    /**
     * Initialize the object.
     *
     * <code>
     * $statistics   = new Crowdfunding\Statistics\Transactions\Latest(\JFactory::getDbo());
     * </code>
     *
     * @param \JDatabaseDriver  $db Database Driver
     */
    public function __construct(\JDatabaseDriver $db)
    {
        $this->db = $db;
    }

    protected function getQuery()
    {
        $query = $this->db->getQuery(true);

        $query
            ->select(
                "a.id, a.txn_date, a.txn_amount, a.txn_currency, a.txn_id, a.project_id, a.fee, " .
                "b.title"
            )
            ->from($this->db->quoteName("#__crowdf_transactions", "a"))
            ->leftJoin($this->db->quoteName("#__crowdf_projects", "b") . " ON a.project_id = b.id");

        return $query;
    }
}
