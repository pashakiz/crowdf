<?php
/**
 * @package      Crowdfunding\Images
 * @subpackage   Validators
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

namespace Crowdfunding\Image\Validator;

use Prism;

defined('JPATH_BASE') or die;

\JLoader::register("Prism\\File\\Validator", JPATH_LIBRARIES . "/prism/file/validator.php");

/**
 * This class provides functionality validation image owner.
 *
 * @package      Crowdfunding\Images
 * @subpackage   Validators
 */
class Owner extends Prism\File\Validator
{
    protected $db;
    protected $imageId;
    protected $userId;

    /**
     * Initialize the object.
     *
     * <code>
     * $imageId = 1;
     * $userId = 2;
     *
     * $image   = new Crowdfunding\Image\Validator\Owner(\JFactory::getDbo(), $imageId, $userId);
     * </code>
     *
     * @param \JDatabaseDriver $db Database object.
     * @param int $imageId Image ID.
     * @param int $userId User ID.
     */
    public function __construct(\JDatabaseDriver $db, $imageId, $userId)
    {
        $this->db      = $db;
        $this->imageId = $imageId;
        $this->userId  = $userId;
    }

    /**
     * Validate image owner.
     *
     * <code>
     * $imageId = 1;
     * $userId = 2;
     *
     * $image   = new Crowdfunding\Image\Validator\Owner(\JFactory::getDbo(), $imageId, $userId);
     * if (!$image->isValid()) {
     * ...
     * }
     * </code>
     */
    public function isValid()
    {
        $subQuery = $this->db->getQuery(true);
        $subQuery
            ->select("b.project_id")
            ->from($this->db->quoteName("#__crowdf_images", "b"))
            ->where("b.id = " . (int)$this->imageId);

        $query = $this->db->getQuery(true);
        $query
            ->select("COUNT(*)")
            ->from($this->db->quoteName("#__crowdf_projects", "a"))
            ->where("a.id = (" . $subQuery . ")")
            ->where("a.user_id = " . (int)$this->userId);

        $this->db->setQuery($query, 0, 1);
        $result = $this->db->loadResult();

        return (bool)$result;
    }
}
