<?php
/**
 * @package      CrowdfundingPartners
 * @subpackage   Files
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

namespace CrowdfundingPartners;

use Prism\Database\ArrayObject;
use Joomla\Utilities\ArrayHelper;

defined('JPATH_PLATFORM') or die;

/**
 * This class provides functionality that manage partners.
 *
 * @package      CrowdfundingPartners
 * @subpackage   Parnters
 */
class Partners extends ArrayObject
{
    /**
     * Load partners data by ID from database.
     *
     * <code>
     * $ids = array(1,2,3,4,5);
     *
     * $partners   = new CrowdfundingPartners\Partners(JFactory::getDbo());
     * $partners->load($ids);
     *
     * foreach($partners as $partner) {
     *   echo $partners["name"];
     *   echo $partners["partner_id"];
     * }
     * </code>
     *
     * @param int $projectId
     * @param array $ids
     */
    public function load($projectId = 0, $ids = array())
    {
        // Load project data
        $query = $this->db->getQuery(true);

        $query
            ->select("a.id, a.name, a.project_id, a.partner_id")
            ->from($this->db->quoteName("#__cfpartners_partners", "a"));

        if (!empty($ids)) {
            ArrayHelper::toInteger($ids);
            $query->where("a.id IN ( " . implode(",", $ids) . " )");
        }

        if (!empty($projectId)) {
            $query->where("a.project_id = " . (int)$projectId);
        }

        $this->db->setQuery($query);
        $results = $this->db->loadAssocList();

        if (!$results) {
            $results = array();
        }

        $this->items = $results;
    }

    /**
     * Add a new value to the array.
     *
     * <code>
     * $partner = array(
     *     "name" => "John Dow",
     *     "project_id" => 1,
     *     "partner_id" => 2
     * );
     *
     * $partners   = new CrowdfundingPartners\Partners();
     * $partners->add($partner);
     * </code>
     *
     * @param array $value
     * @param null|int $index
     *
     * @return $this
     */
    public function add($value, $index = null)
    {
        if (!is_null($index)) {
            $this->items[$index] = $value;
        } else {
            $this->items[] = $value;
        }

        return $this;
    }
}
