<?php
/**
 * @package      Crowdfunding
 * @subpackage   Dates
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

namespace Crowdfunding;

use Prism;

defined('JPATH_PLATFORM') or die;

/**
 * This is a class that provides functionality for managing dates.
 *
 * @package      Crowdfunding
 * @subpackage   Dates
 */
class Date extends Prism\Date
{
    /**
     * Calculate days left.
     *
     * <code>
     * $fundingDays  = 30;
     * $fundingStart = "01-06-2014";
     * $fundingEnd   = "30-06-2014";
     *
     * $today    = new Crowdfunding\Date();
     * $daysLeft = $today->calculateDaysLeft($fundingDays, $fundingStart, $fundingEnd);
     * </code>
     *
     * @param int    $fundingDays
     * @param string $fundingStart
     * @param string $fundingEnd
     *
     * @return int
     */
    public function calculateDaysLeft($fundingDays, $fundingStart, $fundingEnd)
    {
        // Calculate days left
        $today = clone $this;

        if (!empty($fundingDays)) {

            $validatorDate = new Prism\Validator\Date($fundingStart);

            // Validate starting date.
            // If there is not starting date, set number of day.
            if (!$validatorDate->isValid($fundingStart)) {
                return (int)$fundingDays;
            }

            $endingDate = new \DateTime($fundingStart);
            $endingDate->modify("+" . (int)$fundingDays . " days");

        } else {
            $endingDate = new \DateTime($fundingEnd);
        }

        $interval = $today->diff($endingDate);
        $daysLeft = $interval->format("%r%a");

        if ($daysLeft < 0) {
            $daysLeft = 0;
        }

        return abs($daysLeft);
    }
}
