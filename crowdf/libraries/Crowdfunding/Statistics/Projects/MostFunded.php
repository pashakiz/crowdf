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
class MostFunded extends Base
{
    /**
     * Load data about the most funded projects.
     *
     * <code>
     * $mostFunded = new Crowdfunding\Statistics\Projects\MostFunded(\JFactory::getDbo());
     * $mostFunded->load();
     *
     * foreach ($mostFunded as $project) {
     *      echo $project["title"];
     * }
     * </code>
     *
     * @param int $limit Number of result that will be loaded.
     */
    public function load($limit = 5)
    {
        // Get current date
        jimport("joomla.date.date");
        $date  = new \JDate();
        $today = $date->toSql();

        $query = $this->getQuery();

        $query
            ->where("( a.published = 1 AND a.approved = 1 )")
            ->where("( a.funding_start <= " . $this->db->quote($today) . " AND a.funding_end >= " . $this->db->quote($today) . " )")
            ->order("a.funded DESC");

        $this->db->setQuery($query, 0, (int)$limit);

        $this->items = (array)$this->db->loadAssocList();
    }
}
