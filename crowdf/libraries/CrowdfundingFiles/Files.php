<?php
/**
 * @package      CrowdfundingFiles
 * @subpackage   Files
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

namespace CrowdfundingFiles;

use Prism\Database\ArrayObject;
use Joomla\Utilities\ArrayHelper;

defined('JPATH_PLATFORM') or die;

/**
 * This class provides functionality that manage files.
 *
 * @package      CrowdfundingFiles
 * @subpackage   Files
 */
class Files extends ArrayObject
{
    /**
     * Load files data by ID from database.
     *
     * <code>
     * $options = array(
     *    "ids" => array(1,2,3,4,5),
     *    "project_id" => 1,
     *    "user_id" => 2
     * );
     *
     * $files   = new CrowdfundingFilesFiles(JFactory::getDbo());
     * $files->load($options);
     *
     * foreach($files as $file) {
     *   echo $file["title"];
     *   echo $file["filename"];
     * }
     * </code>
     *
     * @param array $options
     */
    public function load($options = array())
    {
        // Load project data
        $query = $this->db->getQuery(true);

        $query
            ->select("a.id, a.title, a.filename, a.project_id, a.user_id")
            ->from($this->db->quoteName("#__cffiles_files", "a"));

        $ids = (isset($options["ids"])) ? $options["ids"] : null;
        if (!empty($ids)) {
            ArrayHelper::toInteger($ids);
            $query->where("a.id IN ( " . implode(",", $ids) . " )");
        }

        $projectId = (isset($options["project_id"])) ? $options["project_id"] : null;
        if (!empty($projectId)) {
            $query->where("a.project_id = " . (int)$projectId);
        }

        $userId = (isset($options["user_id"])) ? $options["user_id"] : null;
        if (!empty($userId)) {
            $query->where("a.user_id = " . (int)$userId);
        }

        $this->db->setQuery($query);
        $results = (array)$this->db->loadAssocList();

        $this->items = $results;
    }
}
