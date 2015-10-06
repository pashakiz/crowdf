<?php
/**
 * @package      Crowdfunding
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// no direct access
defined('_JEXEC') or die;

/**
 * It is Crowdfunding helper class
 */
abstract class CrowdfundingHelper
{
    protected static $extension = "com_crowdfunding";

    protected static $statistics = array();

    /**
     * Configure the Linkbar.
     *
     * @param    string $vName The name of the active view.
     *
     * @since    1.6
     */
    public static function addSubmenu($vName = 'dashboard')
    {

        JHtmlSidebar::addEntry(
            JText::_('COM_CROWDFUNDING_DASHBOARD'),
            'index.php?option=' . self::$extension . '&view=dashboard',
            $vName == 'dashboard'
        );

        JHtmlSidebar::addEntry(
            JText::_('COM_CROWDFUNDING_PROJECTS'),
            'index.php?option=' . self::$extension . '&view=projects',
            $vName == 'projects'
        );

        JHtmlSidebar::addEntry(
            JText::_('COM_CROWDFUNDING_TRANSACTIONS'),
            'index.php?option=' . self::$extension . '&view=transactions',
            $vName == 'transactions'
        );

        JHtmlSidebar::addEntry(
            JText::_('COM_CROWDFUNDING_CATEGORIES'),
            'index.php?option=com_categories&extension=' . self::$extension . '',
            $vName == 'categories'
        );

        JHtmlSidebar::addEntry(
            JText::_('COM_CROWDFUNDING_COMMENTS'),
            'index.php?option=' . self::$extension . '&view=comments',
            $vName == 'comments'
        );

        JHtmlSidebar::addEntry(
            JText::_('COM_CROWDFUNDING_COUNTRIES'),
            'index.php?option=' . self::$extension . '&view=countries',
            $vName == 'countries'
        );

        JHtmlSidebar::addEntry(
            JText::_('COM_CROWDFUNDING_CURRENCIES'),
            'index.php?option=' . self::$extension . '&view=currencies',
            $vName == 'currencies'
        );

        JHtmlSidebar::addEntry(
            JText::_('COM_CROWDFUNDING_EMAILS'),
            'index.php?option=' . self::$extension . '&view=emails',
            $vName == 'emails'
        );

        JHtmlSidebar::addEntry(
            JText::_('COM_CROWDFUNDING_LOCATIONS'),
            'index.php?option=' . self::$extension . '&view=locations',
            $vName == 'locations'
        );

        JHtmlSidebar::addEntry(
            JText::_('COM_CROWDFUNDING_LOGS'),
            'index.php?option=' . self::$extension . '&view=logs',
            $vName == 'logs'
        );

        JHtmlSidebar::addEntry(
            JText::_('COM_CROWDFUNDING_REPORTS'),
            'index.php?option=' . self::$extension . '&view=reports',
            $vName == 'reports'
        );

        JHtmlSidebar::addEntry(
            JText::_('COM_CROWDFUNDING_TYPES'),
            'index.php?option=' . self::$extension . '&view=types',
            $vName == 'types'
        );

        JHtmlSidebar::addEntry(
            JText::_('COM_CROWDFUNDING_USERS'),
            'index.php?option=' . self::$extension . '&view=users',
            $vName == 'users'
        );

        JHtmlSidebar::addEntry(
            JText::_('COM_CROWDFUNDING_UPDATES'),
            'index.php?option=' . self::$extension . '&view=updates',
            $vName == 'updates'
        );

        JHtmlSidebar::addEntry(
            JText::_('COM_CROWDFUNDING_PLUGINS'),
            'index.php?option=com_plugins&view=plugins&filter_search=' . rawurlencode("crowdfunding"),
            $vName == 'plugins'
        );

    }

    public static function getProjectTitle($projectId)
    {
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query
            ->select("title")
            ->from("#__crowdf_projects")
            ->where("id = " . (int)$projectId);

        $db->setQuery($query);

        return $db->loadResult();
    }

    public static function getProject($projectId, $fields = array("id"))
    {
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        $selectFields = array();
        foreach ($fields as $field) {
            $selectFields[] = $db->quoteName($field);
        }

        $query
            ->select($selectFields)
            ->from("#__crowdf_projects")
            ->where($db->quoteName("id") . " = " . (int)$projectId);

        $db->setQuery($query);

        return $db->loadObject();

    }

    public static function getUserIdByRewardId($rewardId)
    {
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query
            ->select("b.user_id")
            ->from($db->quoteName("#__crowdf_rewards", "a"))
            ->innerJoin($db->quoteName("#__crowdf_projects", "b") . " ON a.project_id = b.id")
            ->where("a.id = " . (int)$rewardId);

        $db->setQuery($query);
        $result = $db->loadResult();

        return (int)$result;
    }

    /**
     * This module collects statistical data about project - number of updates, comments, funders,...
     *
     * @param integer $projectId
     *
     * @return array
     */
    public static function getProjectData($projectId)
    {
        $db = JFactory::getDbo();

        /// Updates
        if (!isset(self::$statistics[$projectId])) {
            self::$statistics[$projectId] = array(
                "updates"  => null,
                "comments" => null,
                "funders"  => null
            );

        }

        // Count updates
        if (is_null(self::$statistics[$projectId]["updates"])) {

            $query = $db->getQuery(true);
            $query
                ->select("COUNT(*) AS updates")
                ->from($db->quoteName("#__crowdf_updates"))
                ->where("project_id = " . (int)$projectId);

            $db->setQuery($query);

            self::$statistics[$projectId]["updates"] = $db->loadResult();
        }

        // Count comments
        if (is_null(self::$statistics[$projectId]["comments"])) {

            $query = $db->getQuery(true);
            $query
                ->select("COUNT(*) AS comments")
                ->from($db->quoteName("#__crowdf_comments"))
                ->where("project_id = " . (int)$projectId)
                ->where("published = 1");

            $db->setQuery($query);

            self::$statistics[$projectId]["comments"] = $db->loadResult();
        }

        // Count funders
        if (is_null(self::$statistics[$projectId]["funders"])) {

            $query = $db->getQuery(true);
            $query
                ->select("COUNT(*) AS funders")
                ->from($db->quoteName("#__crowdf_transactions", "a"))
                ->where("a.project_id  = " . (int)$projectId)
                ->where("(a.txn_status = " . $db->quote("completed") . " OR a.txn_status = ". $db->quote("pending") . ")");

            $db->setQuery($query);

            self::$statistics[$projectId]["funders"] = $db->loadResult();
        }

        return self::$statistics[$projectId];
    }

    /**
     * Generate a path to the folder, where the images are stored.
     *
     * @param int    $userId User Id.
     * @param string $path   A base path to the folder. It can be JPATH_BASE, JPATH_ROOT, JPATH_SITE,... Default is JPATH_ROOT.
     *
     * @return string
     */
    public static function getImagesFolder($userId = 0, $path = JPATH_ROOT)
    {
        jimport('joomla.filesystem.path');
        jimport('joomla.filesystem.folder');

        $params = JComponentHelper::getParams(self::$extension);
        /** @var $params Joomla\Registry\Registry */

        $folder = $path . DIRECTORY_SEPARATOR . $params->get("images_directory", "images/crowdfunding");

        if (!empty($userId)) {
            $folder .= DIRECTORY_SEPARATOR . "user" . (int)$userId;
        }

        return JPath::clean($folder);
    }

    /**
     * Generate a path to the temporary images folder.
     *
     * @param string $path   A base path to the folder. It can be JPATH_BASE, JPATH_ROOT, JPATH_SITE,... Default is JPATH_ROOT.
     *
     * @return string
     */
    public static function getTemporaryImagesFolder($path = JPATH_ROOT)
    {
        jimport('joomla.filesystem.path');
        jimport('joomla.filesystem.folder');

        $params = JComponentHelper::getParams(self::$extension);
        /** @var $params Joomla\Registry\Registry */

        $folder = $path .DIRECTORY_SEPARATOR. $params->get("images_directory", "images/crowdfunding") .DIRECTORY_SEPARATOR. "temporary";

        return JPath::clean($folder);
    }

    /**
     * Generate a URI path to the folder, where the images are stored.
     *
     * @param int $userId User Id.
     *
     * @return string
     */
    public static function getImagesFolderUri($userId = 0)
    {
        $params = JComponentHelper::getParams(self::$extension);
        /** @var $params Joomla\Registry\Registry */

        $uriImages = $params->get("images_directory", "images/crowdfunding");

        if (!empty($userId)) {
            $uriImages .= "/user" . (int)$userId;
        }

        return $uriImages;
    }

    /**
     * Generate a URI path to the temporary images folder.
     *
     * @return string
     */
    public static function getTemporaryImagesFolderUri()
    {
        $params = JComponentHelper::getParams(self::$extension);
        /** @var $params Joomla\Registry\Registry */

        $uriImages = $params->get("images_directory", "images/crowdfunding") . "/temporary";

        return $uriImages;
    }

    /**
     * Create a folder and index.html file.
     *
     * @param string $folder
     *
     * @return string
     */
    public static function createFolder($folder)
    {
        JFolder::create($folder);

        $folderIndex = JPath::clean($folder . DIRECTORY_SEPARATOR . "index.html");
        $buffer      = "<!DOCTYPE html><title></title>";

        jimport('joomla.filesystem.file');
        JFile::write($folderIndex, $buffer);
    }

    /**
     * Generate a URI string by a given list of parameters.
     *
     * @param array $params
     *
     * @return string
     */
    public static function generateUrlParams($params)
    {
        $result = "";
        foreach ($params as $key => $param) {
            $result .= "&" . rawurlencode($key) . "=" . rawurlencode($param);
        }

        return $result;
    }

    /**
     * Prepare date format.
     *
     * @param bool $calendar
     *
     * @return string
     */
    public static function getDateFormat($calendar = false)
    {
        $params = JComponentHelper::getParams("com_crowdfunding");
        /** @var  $params Joomla\Registry\Registry */

        $dateFormat = $params->get("project_date_format", "Y-m-d");

        if ($calendar) {
            switch($dateFormat) {
                case "Y-m-d":
                    $dateFormat = "YYYY-MM-DD";
                    break;
                case "d-m-Y":
                    $dateFormat = "DD-MM-YYYY";
                    break;
                case "m-d-Y":
                    $dateFormat = "MM-DD-YYYY";
                    break;
            }
        }

        return $dateFormat;
    }

    /**
     * Prepare an amount, parsing it from formatted string to decimal value.
     * This is most used when a user post a data via form.
     *
     * @param float $value
     *
     * @return string|float
     */
    public static function parseAmount($value)
    {
        $params = JComponentHelper::getParams("com_crowdfunding");
        /** @var  $params Joomla\Registry\Registry */

        // Get currency
        $currency       = Crowdfunding\Currency::getInstance(JFactory::getDbo(), $params->get("project_currency"));

        // Parse the goal amount.
        $amount = new Crowdfunding\Amount($params, $value);
        $amount->setCurrency($currency);

        return $amount->parse();
    }

    public static function prepareCategories($items)
    {
        $result = array();

        if (!empty($items)) {

            foreach ($items as $key => $item) {

                // Decode parameters
                if (!empty($item->params)) {
                    $item->params = json_decode($item->params, true);

                    // Generate a link to the picture.
                    if (is_array($item->params)) {
                        $image = Joomla\Utilities\ArrayHelper::getValue($item->params, "image");
                        if (!empty($image)) {
                            $item->image_link = JUri::base().$image;
                        }
                    }
                }

                // Generate lines by number of items in a row.
                $result[$key] = $item;
            }
        }

        return $result;
    }

    public static function prepareItems($items)
    {
        $result = array();

        if (!empty($items)) {
            foreach ($items as $key => $item) {

                // Calculate funding end date
                if (!empty($item->funding_days)) {

                    $fundingStartDate = new Crowdfunding\Date($item->funding_start);
                    $endDate = $fundingStartDate->calculateEndDate($item->funding_days);
                    $item->funding_end = $endDate->format("Y-m-d");

                }

                // Calculate funded percentage.
                $percent = new Prism\Math();
                $percent->calculatePercentage($item->funded, $item->goal, 0);
                $item->funded_percents = (string)$percent;

                // Calculate days left
                $today = new Crowdfunding\Date();
                $item->days_left = $today->calculateDaysLeft($item->funding_days, $item->funding_start, $item->funding_end);

                $result[$key] = $item;
            }
        }

        return $result;
    }

    public static function fetchUserIds($items)
    {
        $result = array();

        if (!empty($items)) {
            foreach ($items as $key => $item) {
                if (is_object($item) and isset($item->user_id)) {
                    $result[] = $item->user_id;
                } elseif (is_array($item) and isset($item["user_id"])) {
                    $result[] = $item["user_id"];
                } else {
                    continue;
                }
            }
        }

        return $result;
    }

    public static function prepareIntegrations($socialPlatform, array $usersIds)
    {
        // Prepare social integration.
        $socialProfilesBuilder = new Prism\Integration\Profiles\Builder(
            array(
                "social_platform" => $socialPlatform,
                "users_ids" => $usersIds
            )
        );

        $socialProfilesBuilder->build();

        return $socialProfilesBuilder->getProfiles();
    }

    public static function isRewardsEnabled($projectId)
    {
        // Check for enabled rewards by component options.
        $componentParams = JComponentHelper::getParams('com_crowdfunding');
        if (!$componentParams->get("rewards_enabled", 1)) {
            return false;
        }

        // Check for enabled rewards by project type.
        $project = Crowdfunding\Project::getInstance(JFactory::getDbo(), $projectId);
        $type    = $project->getType();

        if (!is_null($type) and !$type->isRewardsEnabled()) {
            return false;
        }

        return true;
    }
}
