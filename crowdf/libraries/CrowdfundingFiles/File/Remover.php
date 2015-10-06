<?php
/**
 * @package      CrowdfundingFiles\File
 * @subpackage   Removers
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

namespace CrowdfundingFiles\File;

defined('JPATH_BASE') or die;

/**
 * This class provides functionality for removing a file from database.
 *
 * @package      CrowdfundingFiles\File
 * @subpackage   Removers
 */
class Remover
{
    /**
     * Database driver.
     *
     * @var \JDatabaseDriver
     */
    protected $db;

    protected $fileId;
    protected $mediaFolder;

    /**
     * Initialize the object.
     *
     * <code>
     * $fileId = 1;
     * $filesFolder = "/.../folder";
     *
     * $file   = new CrowdfundingFiles\File\Remover(JFactory::getDbo(), $fileId, $mediaFolder);
     * $file->remove();
     * </code>
     *
     * @param \JDatabaseDriver $db Database object.
     * @param int $fileId File ID.
     * @param string $mediaFolder A path to the file.
     */
    public function __construct($db, $fileId, $mediaFolder)
    {
        $this->db           = $db;
        $this->fileId       = $fileId;
        $this->mediaFolder  = $mediaFolder;
    }

    /**
     * Remove an extra image from database and file system.
     *
     * <code>
     * $fileId = 1;
     * $filesFolder = "/.../folder";
     *
     * $file   = new CrowdfundingFiles\File\Remover(JFactory::getDbo(), $fileId, $mediaFolder);
     * $file->remove();
     * </code>
     */
    public function remove()
    {
        // Get the image
        $query = $this->db->getQuery(true);
        $query
            ->select("a.filename")
            ->from($this->db->quoteName("#__cffiles_files", "a"))
            ->where("a.id = " . (int)$this->fileId);

        $this->db->setQuery($query, 0, 1);
        $fileName = $this->db->loadResult();

        if (!empty($fileName)) {

            // Remove the file from the filesystem
            $file = \JPath::clean($this->mediaFolder . DIRECTORY_SEPARATOR . $fileName);
            if (\JFile::exists($file)) {
                \JFile::delete($file);
            }

            // Delete the record
            $query = $this->db->getQuery(true);
            $query
                ->delete($this->db->quoteName("#__cffiles_files"))
                ->where($this->db->quoteName("id") . " = " . (int)$this->fileId);

            $this->db->setQuery($query);
            $this->db->execute();
        }
    }
}
