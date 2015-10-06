<?php
/**
 * @package      Crowdfunding\Statistics
 * @subpackage   Projects
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

namespace Crowdfunding\Statistics\Projects;

defined('JPATH_PLATFORM') or die;

\JLoader::register("Crowdfunding\\Statistics\\Projects\\Base", JPATH_LIBRARIES . "/crowdfunding/statistics/projects/base.php");

/**
 * This class loads statistics about projects.
 *
 * @package      Crowdfunding\Statistics
 * @subpackage   Projects
 */
class Latest extends Base
{
    /**
     * Load latest projects ordering by starting date of campaigns.
     *
     * <code>
     * $limit = 10;
     *
     * $latest = new Crowdfunding\Statistics\Projects\Latest(\JFactory::getDbo());
     * $latest->load($limit);
     *
     * foreach ($latest as $project) {
     *      echo $project["title"];
     *      echo $project["funding_start"];
     * }
     * </code>
     *
     * @param int $limit The number of results.
     */
    public function load($limit = 5)
    {
        $query = $this->getQuery();

        $query
            ->where("a.published = 1")
            ->where("a.approved = 1")
            ->order("a.funding_start DESC");

        $this->db->setQuery($query, 0, (int)$limit);

        $this->items = (array)$this->db->loadAssocList();
    }

    /**
     * Load latest projects ordering by created date.
     *
     * <code>
     * $limit = 10;
     *
     * $latest = new Crowdfunding\Statistics\Projects\Latest(\JFactory::getDbo());
     * $latest->loadByCreated($limit);
     *
     * foreach ($latest as $project) {
     *      echo $project["title"];
     *      echo $project["funding_start"];
     * }
     * </code>
     *
     * @param int $limit The number of results.
     */
    public function loadByCreated($limit = 5)
    {
        $query = $this->getQuery();

        $query
            ->where("a.published = 1")
            ->where("a.approved = 1")
            ->order("a.created DESC");

        $this->db->setQuery($query, 0, (int)$limit);

        $this->items = (array)$this->db->loadAssocList();
    }
}
