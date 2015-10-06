<?php
/**
 * @package      Crowdfunding
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined('_JEXEC') or die;

class CrowdfundingTableProject extends JTable
{
    public $id;
    public $alias;
    public $image;
    public $image_small;
    public $image_square;
    public $goal;
    public $funded;
    public $funding_start;
    public $funding_end;
    public $funding_days;
    public $catid;

    protected $fundedPercent = 0;
    protected $daysLeft = 0;
    protected $slug = "";
    protected $catslug = "";
    protected $location_preview = "";

    /**
     * @param JDatabaseDriver $db
     */
    public function __construct($db)
    {
        parent::__construct('#__crowdf_projects', 'id', $db);
    }

    /**
     * Method to load a row from the database by primary key and bind the fields
     * to the JTable instance properties.
     *
     * @param   mixed   $keys  An optional primary key value to load the row by, or an array of fields to match.  If not
     *                         set the instance property value is used.
     * @param   boolean $reset True to reset the default values before loading the new row.
     *
     * @return  boolean  True if successful. False if row not found or on error (internal error state set in that case).
     *
     * @link    http://docs.joomla.org/JTable/load
     * @since   11.1
     */
    public function load($keys = null, $reset = true)
    {
        parent::load($keys, $reset);

        $this->slug = $this->id .".". $this->alias;

        // Calculate funded percent
        if (!$this->goal) {
            $this->fundedPercent = 0;
        } else {
            $percentage = new Prism\Math();
            $percentage->calculatePercentage($this->funded, $this->goal, 0);
            $this->fundedPercent = (string)$percentage;
        }

        // Calculate end date
        if (!empty($this->funding_days)) {

            $fundingStartDateValidator = new Prism\Validator\Date($this->funding_start);
            if (!$fundingStartDateValidator->isValid()) {
                $this->funding_end = "0000-00-00";
            } else {
                $fundingStartDate  = new Crowdfunding\Date($this->funding_start);
                $fundingEndDate    = $fundingStartDate->calculateEndDate($this->funding_days);
                $this->funding_end = $fundingEndDate->toSql();
            }

        }

        // Calculate days left
        $today = new Crowdfunding\Date();
        $this->daysLeft = $today->calculateDaysLeft($this->funding_days, $this->funding_start, $this->funding_end);

        return true;
    }

    /**
     * Return percentage of funded amount.
     *
     * @return int
     */
    public function getFundedPercent()
    {
        return $this->fundedPercent;
    }

    public function setFundedPercent($percent)
    {
        $this->fundedPercent = $percent;
    }

    /**
     * Return the days that left to the end of campaign.
     *
     * @return int $daysLeft
     */
    public function getDaysLeft()
    {
        return $this->daysLeft;
    }

    public function getSlug()
    {
        return $this->slug;
    }

    public function getCatSlug()
    {
        if (!$this->catslug) {
            $db    = $this->getDbo();
            $query = $db->getQuery(true);
            $query
                ->select($query->concatenate(array("a.id", "a.alias"), ":") . " AS catslug")
                ->from($db->quoteName("#__categories", "a"))
                ->where("a.id = " .(int)$this->catid);

            $db->setQuery($query, 0, 1);
            $result = $db->loadResult();

            if (!empty($result)) {
                $this->catslug = (string)$result;
            } else {
                $this->catslug = (int)$this->catid;
            }
        }

        return $this->catslug;
    }
}
