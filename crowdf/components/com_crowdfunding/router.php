<?php
/**
 * @package      Crowdfunding
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined('_JEXEC') or die;

jimport("Crowdfunding.init");

/**
 * Method to build Route
 *
 * @param array $query
 *
 * @return array
 */
function CrowdfundingBuildRoute(&$query)
{
    $segments = array();

    // get a menu item based on Itemid or currently active
    $app  = JFactory::getApplication();
    $menu = $app->getMenu();

    // we need a menu item.  Either the one specified in the query, or the current active one if none specified
    if (empty($query['Itemid'])) {
        $menuItem      = $menu->getActive();
        $menuItemGiven = false;
    } else {
        $menuItem      = $menu->getItem($query['Itemid']);
        $menuItemGiven = (isset($menuItem->query)) ? true : false ;
    }

    // Check again
    if ($menuItemGiven and isset($menuItem) and strcmp("com_crowdfunding", $menuItem->component) != 0) {
        $menuItemGiven = false;
        unset($query['Itemid']);
    }

    $mView   = (empty($menuItem->query['view'])) ? null : $menuItem->query['view'];
    $mId     = (empty($menuItem->query['id'])) ? null : $menuItem->query['id'];
//    $mOption = (empty($menuItem->query['option'])) ? null : $menuItem->query['option'];
//    $mCatid  = (empty($menuItem->query['catid'])) ? null : $menuItem->query['catid'];

    // If is set view and Itemid missing, we have to put the view to the segments
    if (isset($query['view'])) {
        $view = $query['view'];
    } else {
        return $segments;
    }

    // Are we dealing with a category that is attached to a menu item?
    if (($menuItem instanceof stdClass) and isset($view) and ($mView == $view) and (isset($query['id'])) and ($mId == (int)$query['id'])) {

        unset($query['view']);

        if (isset($query['catid'])) {
            unset($query['catid']);
        }

        if (isset($query['layout'])) {
            unset($query['layout']);
        }

        unset($query['id']);

        return $segments;
    }
    
    // Views
    if (isset($view)) {

        switch ($view) {

            case "category":

                if (!$menuItemGiven) {
                    $segments[] = $view;
                }
                unset($query['view']);

                if (isset($query['id'])) {
                    $categoryId = $query['id'];
                } else {
                    // We should have id set for this view.  If we don't, it is an error.
                    return $segments;
                }

                $segments = CrowdfundingHelperRoute::prepareCategoriesSegments($categoryId, $segments, $menuItem, $menuItemGiven);

                unset($query['id']);

                break;

            case "backing":
            case "embed":
            case "details":

                if (!$menuItemGiven) {
                    $segments[] = $view;
                }
                unset($query['view']);

                // If a project is assigned to a menu item.
                if ($menuItemGiven and (strcmp("details", $menuItem->query["view"]) == 0) and ($menuItem->query["id"] == (int)$query['id'])) {

                } else {

                    // If a project is NOT assigned to a menu item.
                    if (isset($query['id']) and isset($query['catid']) and !empty($query['catid'])) {
                        $categoryId = (int)$query['catid'];

                        if (false === strpos($query['id'], ":")) {
                            $alias       = CrowdfundingHelperRoute::getProjectAlias($query['id']);
                            $query['id'] = $query['id'] . ":" . $alias;
                        }
                    } else {
                        // We should have these two set for this view.  If we don't, it is an error.
                        return $segments;
                    }

                    $segments = CrowdfundingHelperRoute::prepareCategoriesSegments($categoryId, $segments, $menuItem, $menuItemGiven);

                    $segments[] = $query['id'];
                }

                unset($query['id']);
                unset($query['catid']);

                if (strcmp("backing", $view) == 0) {
                    $segments[] = "backing";
                }

                if (strcmp("embed", $view) == 0) {
                    $segments[] = "embed";
                }

                break;

            case "report":
                if ($menuItemGiven and (strcmp("report", $menuItem->query["view"]) == 0) and isset($query['view'])) {
                    unset($query['view']);
                }
                break;

            case "categories":
            case "discover":
            case "transactions":
            case "projects":
            case "project": // Form for adding projects

                if (isset($query['view'])) {
                    unset($query['view']);
                }
                break;

        }

    }

    // Layout
    if (isset($query['layout'])) {
        if ($menuItemGiven and isset($menuItem->query['layout'])) {
            if ($query['layout'] == $menuItem->query['layout']) {
                unset($query['layout']);
            }
        } else {
            if ($query['layout'] == 'default') {
                unset($query['layout']);
            }
        }
    }

    // Screen
    if (isset($query["screen"])) {
        $segments[] = $query["screen"];
        unset($query['screen']);
    }

    $total = count($segments);

    for ($i = 0; $i < $total; $i++) {
        $segments[$i] = str_replace(':', '-', $segments[$i]);
    }

    return $segments;
}

/**
 * Method to parse Route
 *
 * @param array $segments
 *
 * @return array
 */
function CrowdfundingParseRoute($segments)
{
    $total = count($segments);
    $vars = array();

    for ($i = 0; $i < $total; $i++) {
        $segments[$i] = preg_replace('/-/', ':', $segments[$i], 1);
    }

    //Get the active menu item.
    $app  = JFactory::getApplication();
    $menu = $app->getMenu();
    $item = $menu->getActive();

    // Count route segments
    $count = count($segments);

    // Standard routing for articles.  If we don't pick up an Itemid then we get the view from the segments
    // the first segment is the view and the last segment is the id of the details, category or payment.
    if (!isset($item)) {
        $vars['view']  = $segments[0];
        $vars['id']    = $segments[$count - 1];

        return $vars;
    }

    // COUNT == 1

    // Category
    if ($count == 1) {

        // We check to see if an alias is given.  If not, we assume it is a project,
        // because categories have always alias.
        // If it is a menu item "Details" that could be one of its specific views - backing, embed,...
        if (false == strpos($segments[0], ':')) {

            switch ($segments[0]) {

                case "backing":
                case "embed":

                    $id = $item->query["id"];
                    $project = CrowdfundingHelperRoute::getProject($id);

                    $vars['view']   = $segments[0];
                    $vars['catid']  = (int)$project["catid"];
                    $vars['id']     = (int)$project["id"];

                    break;

                default:
                    $vars['view'] = 'details';
                    $vars['id']   = (int)$segments[0];
                    break;
            }

            return $vars;
        }

        list($id, $alias) = explode(':', $segments[0], 2);
        $alias = str_replace(":", "-", $alias);

        // first we check if it is a category
        $category = JCategories::getInstance('Crowdfunding')->get($id);

        if ($category and (strcmp($category->alias, $alias) == 0)) {
            $vars['view'] = 'category';
            $vars['id']   = $id;

            return $vars;
        } else {
            $project = CrowdfundingHelperRoute::getProject($id);
            if (!empty($project)) {
                if ($project["alias"] == $alias) {

                    $vars['view']  = 'details';
                    $vars['catid'] = (int)$project["catid"];
                    $vars['id']    = (int)$id;

                    return $vars;
                }
            }
        }

    }

    // COUNT >= 2

    if ($count >= 2) {

        $view = $segments[$count - 1];

        switch ($view) {

            case "backing":

                $itemId = (int)$segments[$count - 2];

                // Get catid from menu item
                if (!empty($item->query["id"])) {
                    $catId = (int)$item->query["id"];
                } else {
                    $catId = (int)$segments[$count - 3];
                }

                $vars['view']  = 'backing';
                $vars['id']    = (int)$itemId;
                $vars['catid'] = (int)$catId;

                break;

            case "embed": // Backing without reward

                $itemId = (int)$segments[$count - 2];

                // Get catid from menu item
                if (!empty($item->query["id"])) {
                    $catId = (int)$item->query["id"];
                } else {
                    $catId = (int)$segments[$count - 3];
                }

                $vars['view']  = 'embed';
                $vars['id']    = (int)$itemId;
                $vars['catid'] = (int)$catId;

                break;

            case "updates": // Screens of details - "updates", "comments", "funders"
            case "comments":
            case "funders":

                $itemId = (int)$segments[$count - 2];

                // Get catid from menu item
                if (!empty($item->query["id"])) {
                    $catId = (int)$item->query["id"];
                } else {
                    $catId = (int)$segments[$count - 3];
                }

                $vars['view']  = 'details';
                $vars['id']    = (int)$itemId;
                $vars['catid'] = (int)$catId;

                // Get screen
                $screen         = $segments[$count - 1];
                $allowedScreens = array("updates", "comments", "funders");
                if (in_array($screen, $allowedScreens)) {
                    $vars['screen'] = $screen;
                }

                break;

            default:

                // if there was more than one segment, then we can determine where the URL points to
                // because the first segment will have the target category id prepended to it.  If the
                // last segment has a number prepended, it is details, otherwise, it is a category.
                $catId = (int)$segments[$count - 2];
                $id    = (int)$segments[$count - 1];

                if ($id > 0 and $catId > 0) {
                    $vars['view']  = 'details';
                    $vars['catid'] = $catId;
                    $vars['id']    = $id;
                } else {
                    $vars['view'] = 'category';
                    $vars['id']   = $id;
                }

                break;

        }

    }

    return $vars;
}
