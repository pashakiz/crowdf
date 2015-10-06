<?php
/**
 * @package      Crowdfunding\Projects
 * @subpackage   Validators
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

namespace Crowdfunding\Validator\Project;

use Prism\Date;
use Prism\Validator\ValidatorInterface;

defined('JPATH_BASE') or die;

/**
 * This class provides functionality for validation project owner.
 *
 * @package      Crowdfunding\Projects
 * @subpackage   Validators
 */
class Period implements ValidatorInterface
{
    protected $startDate;
    protected $endDate;
    protected $minDays;
    protected $maxDays;

    /**
     * Initialize the object.
     *
     * <code>
     * $startDate = "2015-01-01";
     * $endDate = "2015-01-30";
     * $minDays = "10";
     * $maxDays = "30";
     *
     * $period = new Crowdfunding\Validator\Project\Period($startDate, $endDate, $minDays, $maxDays);
     * </code>
     *
     * @param string $startDate
     * @param string $endDate
     * @param int $minDays
     * @param int $maxDays
     */
    public function __construct($startDate, $endDate, $minDays, $maxDays)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->minDays = $minDays;
        $this->maxDays = $maxDays;
    }

    /**
     * Validate project owner.
     *
     * <code>
     * $startDate = "2015-01-01";
     * $endDate = "2015-01-30";
     * $minDays = "10";
     * $maxDays = "30";
     *
     * $period = new Crowdfunding\Validator\Project\Period($startDate, $endDate, $minDays, $maxDays);
     * if(!$period->isValid()) {
     * ......
     * }
     * </code>
     *
     * @return bool
     */
    public function isValid()
    {
        // Get interval between starting and ending date.
        $fundingStartDate = new \JDate($this->startDate);
        $fundingEndDate   = new \JDate($this->endDate);
        $interval     = $fundingStartDate->diff($fundingEndDate);

        $days = $interval->format("%r%a");

        // Validate minimum dates
        if ($days < $this->minDays) {
            return false;
        }

        if (!empty($this->maxDays) and $days > $this->maxDays) {
            return false;
        }

        return true;
    }
}
