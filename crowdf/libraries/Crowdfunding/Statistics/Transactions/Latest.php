<?php
/**
 * @package      Crowdfunding\Statistics
 * @subpackage   Transactions
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

namespace Crowdfunding\Statistics\Transactions;

defined('JPATH_PLATFORM') or die;

\JLoader::register("Crowdfunding\\Statistics\\Transactions\\Base", JPATH_LIBRARIES . "/crowdfunding/statistics/transactions/base.php");

/**
 * This class loads statistics about transactions.
 *
 * @package      Crowdfunding\Statistics
 * @subpackage   Transactions
 */
class Latest extends Base
{
    /**
     * Load latest transaction ordering by record date.
     *
     * <code>
     * $limit = 10;
     *
     * $latest = new CrowdfundingStatisticsTransactionsLatest(JFactory::getDbo());
     * $latest->load($limit);
     *
     * foreach ($latest as $project) {
     *      echo $project["txn_amount"];
     *      echo $project["txn_currency"];
     *      echo $project["txn_date"];
     * }
     * </code>
     *
     * @param int $limit The number of results.
     */
    public function load($limit = 5)
    {
        $query = $this->getQuery();

        $query->order("a.txn_date DESC");

        $this->db->setQuery($query, 0, (int)$limit);

        $this->items = (array)$this->db->loadAssocList();
    }
}
