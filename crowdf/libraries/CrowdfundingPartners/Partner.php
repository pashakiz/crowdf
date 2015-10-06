<?php
/**
 * @package      CrowdfundingPartners
 * @subpackage   Partners
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

namespace CrowdfundingPartners;

use Prism\Database\TableImmutable;

defined('JPATH_PLATFORM') or die;

/**
 * This class provides functionality that manage records.
 *
 * @package      CrowdfundingPartners
 * @subpackage   Partners
 */
class Partner extends TableImmutable
{
    protected $id;
    protected $name;
    protected $project_id;
    protected $partner_id;

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
     * $partner    = new CrowdfundingPartners\Partner(\JFactory::getDbo());
     * </code>
     *
     * @param \JDatabaseDriver  $db
     */
    public function __construct(\JDatabaseDriver $db = null)
    {
        $this->db = $db;
    }

    /**
     * Load data of a record from database.
     *
     * <code>
     * $id = 1;
     *
     * $partner    = new CrowdfundingPartners\Partner();
     * $partner->setDb(\JFactory::getDbo());
     * $partner->load($id);
     * </code>
     *
     * @param int $keys
     * @param array $options
     */
    public function load($keys, $options = array())
    {
        $query = $this->db->getQuery(true);

        $query
            ->select(
                "a.id, a.name, a.project_id, a.partner_id"
            )
            ->from($this->db->quoteName("#__cfpartners_partners", "a"))
            ->where("a.id = " .(int)$keys);

        $this->db->setQuery($query);
        $result = $this->db->loadAssoc();

        if (!$result) {
            $result = array();
        }

        $this->bind($result);
    }

    /**
     * Return record ID.
     *
     * <code>
     * $id  = 1;
     *
     * $partner    = new CrowdfundingPartners\Partner(\JFactory::getDbo());
     * $partner->load($id);
     *
     * if (!$partner->getId()) {
     * ...
     * }
     * </code>
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Return an ID of a user, that has been assigned as a partner to a project.
     *
     * <code>
     * $id  = 1;
     *
     * $partner    = new CrowdfundingPartners\Partner(\JFactory::getDbo());
     * $partner->load($id);
     *
     * $partnerId = $partner->getPartnerId();
     * </code>
     *
     * @return int
     */
    public function getPartnerId()
    {
        return $this->partner_id;
    }

    /**
     * Return a name of an user, that has been assigned as a partner to a project.
     *
     * <code>
     * $id  = 1;
     *
     * $partner    = new CrowdfundingPartners\Partner(\JFactory::getDbo());
     * $partner->load($id);
     *
     * $name = $partner->getName();
     * </code>
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Return a project ID.
     *
     * <code>
     * $id  = 1;
     *
     * $partner    = new CrowdfundingPartners\Partner(\JFactory::getDbo());
     * $partner->load($id);
     *
     * $projectId = $partner->getProjectId();
     * </code>
     *
     * @return string
     */
    public function getProjectId()
    {
        return $this->project_id;
    }
}
