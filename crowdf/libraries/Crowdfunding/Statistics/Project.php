<?php
/**
 * @package      Crowdfunding
 * @subpackage   Statistics
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

namespace Crowdfunding\Statistics;

use Joomla\Registry\Registry;
use Prism;
use Crowdfunding\Amount;
use Crowdfunding\Currency;

defined('JPATH_PLATFORM') or die;

/**
 * This is a base class for project statistics.
 *
 * @package      Crowdfunding
 * @subpackage   Statistics
 */
class Project
{
    protected $id;

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
     * $projectId    = 1;
     *
     * $statistics   = new CrowdfundingStatisticsProject(\JFactory::getDbo(), $projectId);
     * </code>
     *
     * @param \JDatabaseDriver $db Database Driver
     * @param int             $id Project ID
     */
    public function __construct(\JDatabaseDriver $db, $id)
    {
        $this->db = $db;
        $this->id = (int)$id;
    }

    /**
     * Return the number of transactions.
     *
     * <code>
     * $projectId    = 1;
     *
     * $statistics   = new CrowdfundingStatisticsProject(\JFactory::getDbo(), $projectId);
     * $numberOfTransactions = $statistics->getTransactionsNumber();
     * </code>
     *
     * @return int
     */
    public function getTransactionsNumber()
    {
        // Create a new query object.
        $query = $this->db->getQuery(true);

        $query
            ->select("COUNT(*)")
            ->from($this->db->quoteName("#__crowdf_transactions", "a"))
            ->where("a.project_id = " . (int)$this->id);

        $this->db->setQuery($query);

        $result = $this->db->loadResult();

        if (!$result) {
            $result = 0;
        }

        return $result;
    }

    /**
     * Calculate a project amount for full period of the campaign.
     *
     * <code>
     * $projectId    = 1;
     *
     * $statistics   = new CrowdfundingStatisticsProject(\JFactory::getDbo(), $projectId);
     * $amount = $statistics->getFullPeriodAmounts();
     * </code>
     *
     * @return int
     */
    public function getFullPeriodAmounts()
    {
        $query = $this->db->getQuery(true);
        $query
            ->select("a.funding_start, a.funding_end")
            ->from($this->db->quoteName("#__crowdf_projects", "a"))
            ->where("a.id = " . (int)$this->id);

        $this->db->setQuery($query);
        $result = $this->db->loadObject();

        // Validate dates
        $fundingStartDate = new Prism\Validator\Date($result->funding_start);
        $fundingEndDate   = new Prism\Validator\Date($result->funding_end);
        if (!$fundingStartDate->isValid() or !$fundingEndDate->isValid()) {
            return array();
        }

        $dataset = array();

        $date  = new Prism\Date();

        $timezone = $date->getTimezone();

        $date1 = new \JDate($result->funding_start);
        $date2 = new \JDate($result->funding_end);

        $period = $date->getDaysPeriod($date1, $date2);

        $query = $this->db->getQuery(true);
        $query
            ->select("a.txn_date as date, SUM(a.txn_amount) as amount")
            ->from($this->db->quoteName("#__crowdf_transactions", "a"))
            ->where("a.project_id = " . (int)$this->id)
            ->group("DATE(a.txn_date)");

        $this->db->setQuery($query);
        $results = (array)$this->db->loadAssocList();

        // Prepare data
        $data = array();
        foreach ($results as $result) {
            $date         = new \JDate($result["date"]);
            $index        = $date->format("d.m");
            $data[$index] = $result;
        }

        /** @var $day \JDate */
        foreach ($period as $day) {
            $day->setTimezone($timezone);

            $dayMonth = $day->format("d.m");
            if (isset($data[$dayMonth])) {
                $amount = $data[$dayMonth]["amount"];
            } else {
                $amount = 0;
            }

            $dataset[] = array("date" => $dayMonth, "amount" => $amount);
        }

        return $dataset;
    }

    /**
     * Calculate three types of project amount - goal, funded amount and remaining amount.
     *
     * <code>
     * $projectId    = 1;
     *
     * $statistics   = new CrowdfundingStatisticsProject(\JFactory::getDbo(), $projectId);
     * $data = $statistics->getFundedAmount();
     * </code>
     *
     * @return array
     *
     * # Example result:
     * array(
     *    "goal" = array("label" => "Goal", "amount" => 1000),
     *    "funded" = array("label" => "Funded", "amount" => 100),
     *    "remaining" = array("label" => "Remaining", "amount" => 900)
     * )
     */
    public function getFundedAmount()
    {
        $data = array();

        $query = $this->db->getQuery(true);
        $query
            ->select("a.funded, a.goal")
            ->from($this->db->quoteName("#__crowdf_projects", "a"))
            ->where("a.id = " . (int)$this->id);

        $this->db->setQuery($query);
        $result = $this->db->loadObject();
        /** @var $result object */

        if (empty($result->funded) or empty($result->goal)) {
            return $data;
        }

        // Get currency
        $params = \JComponentHelper::getParams("com_crowdfunding");
        /** @var  $params Registry */

        $currencyId = $params->get("project_currency");
        $currency   = Currency::getInstance(\JFactory::getDbo(), $currencyId, $params);

        $amount = new Amount();
        $amount->setCurrency($currency);

        $data["goal"] = array(
            "label"  => \JText::sprintf("COM_CROWDFUNDINGFINANCE_GOAL_S", $amount->setValue($result->goal)->formatCurrency()),
            "amount" => (float)$result->goal
        );

        $data["funded"] = array(
            "label"  => \JText::sprintf("COM_CROWDFUNDINGFINANCE_FUNDED_S", $amount->setValue($result->funded)->formatCurrency()),
            "amount" => (float)$result->funded
        );

        $remaining = (float)($result->goal - $result->funded);
        if ($remaining < 0) {
            $remaining = 0;
        }

        $data["remaining"] = array(
            "label"  => \JText::sprintf("COM_CROWDFUNDINGFINANCE_REMAINING_S", $amount->setValue($remaining)->formatCurrency()),
            "amount" => $remaining
        );

        return $data;
    }

    /**
     * Return the number of comments.
     *
     * <code>
     * $projectId    = 1;
     *
     * $statistics   = new CrowdfundingStatisticsProject(\JFactory::getDbo(), $projectId);
     * $numberOfComments = $statistics->getCommentsNumber();
     * </code>
     *
     * @return int
     */
    public function getCommentsNumber()
    {
        // Create a new query object.
        $query = $this->db->getQuery(true);

        $query
            ->select("COUNT(*)")
            ->from($this->db->quoteName("#__crowdf_comments", "a"))
            ->where("a.project_id = " . (int)$this->id);

        $this->db->setQuery($query);

        $result = $this->db->loadResult();

        if (!$result) {
            $result = 0;
        }

        return $result;
    }

    /**
     * Return the number of updates.
     *
     * <code>
     * $projectId    = 1;
     *
     * $statistics   = new CrowdfundingStatisticsProject(\JFactory::getDbo(), $projectId);
     * $numberOfUpdates = $statistics->getUpdatesNumber();
     * </code>
     *
     * @return int
     */
    public function getUpdatesNumber()
    {
        // Create a new query object.
        $query = $this->db->getQuery(true);

        $query
            ->select("COUNT(*)")
            ->from($this->db->quoteName("#__crowdf_updates", "a"))
            ->where("a.project_id = " . (int)$this->id);

        $this->db->setQuery($query);

        $result = $this->db->loadResult();

        if (!$result) {
            $result = 0;
        }

        return $result;
    }

    /**
     * Return information about amounts by transaction statuses.
     *
     * <code>
     * $projectId    = 1;
     *
     * $statistics   = new CrowdfundingStatisticsProject(\JFactory::getDbo(), $projectId);
     * $payoutInformation = $statistics->getPayoutInformation();
     * </code>
     *
     * @return array
     */
    public function getTransactionsStatusStatistics()
    {
        // Create a new query object.
        $query = $this->db->getQuery(true);

        $query
            ->select("a.project_id, a.txn_status, COUNT(id) AS transactions, SUM(txn_amount) AS amount, SUM(fee) AS fee_amount")
            ->from($this->db->quoteName("#__crowdf_transactions", "a"))
            ->group("a.txn_status")
            ->where("a.project_id = " . (int)$this->id);

        $this->db->setQuery($query);

        $result = $this->db->loadAssocList("txn_status");

        if (!$result) {
            $result = array();
        }

        return $result;
    }

    /**
     * Return information about amounts by transaction statuses.
     *
     * <code>
     * $projectId    = 1;
     *
     * $statistics   = new CrowdfundingStatisticsProject(\JFactory::getDbo(), $projectId);
     * $payoutInformation = $statistics->getPayoutInformation();
     * </code>
     *
     * @return array
     */
    public function getPayoutStatistics()
    {
        // Create a new query object.
        $query = $this->db->getQuery(true);

        $query
            ->select("a.txn_status, SUM(txn_amount) AS amount")
            ->from($this->db->quoteName("#__crowdf_transactions", "a"))
            ->group("a.txn_status")
            ->where("a.project_id = " . (int)$this->id);

        $this->db->setQuery($query);

        $result = $this->db->loadAssocList("txn_status");

        if (!$result) {
            $result = array();
        }

        return $result;
    }
}
