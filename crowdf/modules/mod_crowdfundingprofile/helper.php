<?php
/**
 * @package      Crowdfunding
 * @subpackage   Modules
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined('_JEXEC') or die;

class CrowdfundingProfileModuleHelper {
    
    public static function getData($projectId)
    {
        // Get current date
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);
        
        $query
            ->select("a.user_id, b.name")
            ->from($db->quoteName("#__crowdf_projects", "a"))
            ->innerJoin($db->quoteName("#__users", "b") . " ON a.user_id = b.id")
            ->where("a.id =".(int)$projectId);
        
        $db->setQuery($query, 0, 1);

        $result = $db->loadAssoc();

        if (!$result) {
            $result = array();
        }

        return $result;
    }
}
