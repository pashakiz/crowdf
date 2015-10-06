<?php
/**
 * @package      CrowdfundingPartners
 * @subpackage   Component
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2014 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// no direct access
defined('_JEXEC') or die;

/**
 * Get a list of items
 */
class CrowdfundingPartnersModelPartners extends JModelLegacy
{
    /**
     * Returns a reference to the a Table object, always creating it.
     *
     * @param   string $type    The table type to instantiate
     * @param   string $prefix A prefix for the table class name. Optional.
     * @param   array  $config Configuration array for model. Optional.
     *
     * @return  JTable  A database object
     * @since   1.6
     */
    public function getTable($type = 'Partner', $prefix = 'CrowdfundingPartnersTable', $config = array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }

    /**
     * Return user ID searching by username or email.
     *
     * @param string $username
     *
     * @return int
     */
    public function getUserId($username)
    {
        $db = $this->getDbo();

        $query = $db->getQuery(true);
        $query
            ->select("a.id")
            ->from($db->quoteName("#__users", "a"))
            ->where("a.username = " . $db->quote($username), "OR")
            ->where("a.email = " .$db->quote($username));

        $db->setQuery($query, 0, 1);
        $result = (int)$db->loadResult();

        return $result;
    }

    /**
     * Check if the user has been assigned to a project.
     *
     * @param int $partnerId
     * @param int $projectId
     *
     * @return bool
     */
    public function hasAssigned($partnerId, $projectId)
    {
        $db = $this->getDbo();

        $query = $db->getQuery(true);
        $query
            ->select("COUNT(*)")
            ->from($db->quoteName("#__cfpartners_partners", "a"))
            ->where("a.partner_id = " . (int)$partnerId)
            ->where("a.project_id = " .(int)$projectId);

        $db->setQuery($query, 0, 1);
        $result = (int)$db->loadResult();

        return (bool)$result;
    }

    /**
     * Store the partner in database.
     *
     * @param object $partner
     * @param int $projectId
     *
     * @return array
     */
    public function addPartner($partner, $projectId)
    {
        $db = $this->getDbo();
        /** @var $db JDatabaseMySQLi */

        $query = $db->getQuery(true);
        $query
            ->insert($db->quoteName("#__cfpartners_partners"))
            ->set($db->quoteName("name") . "=" . $db->quote($partner->name))
            ->set($db->quoteName("project_id") . "=" . (int)$projectId)
            ->set($db->quoteName("partner_id") . "=" . (int)$partner->id);

        $db->setQuery($query);
        $db->execute();

        $itemId = $db->insertid();

        return $itemId;
    }

    /**
     * Delete a partner record.
     *
     * @param integer $itemId
     */
    public function remove($itemId)
    {
        $db    = $this->getDbo();
        $query = $db->getQuery(true);

        $query
            ->delete($db->quoteName("#__cfpartners_partners"))
            ->where($db->quoteName("id") ."=".(int)$itemId);

        $db->setQuery($query);
        $db->execute();
    }
}
