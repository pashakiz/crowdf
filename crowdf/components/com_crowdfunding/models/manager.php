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

JLoader::register("CrowdfundingModelProjectItem", CROWDFUNDING_PATH_COMPONENT_SITE . "/models/projectitem.php");

class CrowdfundingModelManager extends CrowdfundingModelProjectItem
{
    /**
     * Return information about project rewards and their receivers.
     *
     * @param int $projectId
     *
     * @return array
     */
    public function getRewardsData($projectId)
    {
        $db    = $this->getDbo();
        $query = $db->getQuery(true);

        $query
            ->select(
                "a.id AS transaction_id, a.receiver_id, a.reward_state, a.txn_id, a.reward_id, " .
                "b.title AS reward, ".
                "c.name, c.email"
            )
            ->from($db->quoteName("#__crowdf_transactions", "a"))
            ->leftJoin($db->quoteName("#__crowdf_rewards", "b") . " ON a.reward_id = b.id")
            ->leftJoin($db->quoteName("#__users", "c") . " ON a.receiver_id = c.id")
            ->where("a.project_id = ". (int)$projectId)
            ->where("a.reward_id > 0 ");

        $db->setQuery($query);

        $results = $db->loadObjectList();

        if (!$results) {
            $results = array();
        }

        return $results;
    }
}
