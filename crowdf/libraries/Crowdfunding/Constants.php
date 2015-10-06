<?php
/**
 * @package      Crowdfunding
 * @subpackage   Constants
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

namespace Crowdfunding;

defined('JPATH_PLATFORM') or die;

/**
 * Crowdfunding constants
 *
 * @package      Crowdfunding
 * @subpackage   Constants
 */
class Constants
{
    // Session contexts
    const PAYMENT_SESSION_CONTEXT = "payment_session_project";
    const CROPPED_IMAGES_CONTEXT  = "cropped_images_project";
    const TEMPORARY_IMAGE_CONTEXT = "temporary_image_project";

    // Filtering
    const FILTER_STARTED_SOON = 1;
    const FILTER_ENDING_SOON = 2;
    const FILTER_SUCCESSFULLY_COMPLETED = 1;

    // Ordering
    const ORDER_BY_ORDERING = 0;
    const ORDER_BY_NAME = 1;
    const ORDER_BY_CREATED_DATE = 2;
    const ORDER_BY_START_DATE = 3;
    const ORDER_BY_END_DATE = 4;
    const ORDER_BY_POPULARITY = 5;
    const ORDER_BY_FUNDING = 6;
    const ORDER_BY_FANS = 7;
    const ORDER_BY_LOCATION_NAME = 10;
    const ORDER_BY_NUMBER_OF_PROJECTS = 20;
}
