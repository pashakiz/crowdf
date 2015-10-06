<?php
/**
 * @package      Crowdfunding\Projects
 * @subpackage   Validators
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

namespace Crowdfunding\Validator\Project;

use Prism\Validator\ValidatorInterface;

defined('JPATH_BASE') or die;

/**
 * This class provides functionality for validation project owner.
 *
 * @package      Crowdfunding\Projects
 * @subpackage   Validators
 */
class Owner implements ValidatorInterface
{
    protected $projectId;
    protected $userId;
    
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
     * $projectId = 1;
     * $userId = 2;
     *
     * $owner = new Crowdfunding\Validator\Project\Owner(\JFactory::getDbo(), $projectId, $userId);
     * </code>
     *
     * @param \JDatabaseDriver $db        Database object.
     * @param int             $projectId Project ID.
     * @param int             $userId    User ID.
     */
    public function __construct(\JDatabaseDriver $db, $projectId, $userId)
    {
        $this->db        = $db;
        $this->projectId = $projectId;
        $this->userId    = $userId;
    }

    /**
     * Validate project owner.
     *
     * <code>
     * $projectId = 1;
     * $userId = 2;
     *
     * $owner = new Crowdfunding\Validator\Project\Owner(\JFactory::getDbo(), $projectId, $userId);
     * if(!$owner->isValid()) {
     * ......
     * }
     * </code>
     *
     * @return bool
     */
    public function isValid()
    {
        $query = $this->db->getQuery(true);

        $query
            ->select("COUNT(*)")
            ->from($this->db->quoteName("#__crowdf_projects", "a"))
            ->where("a.id = " . (int)$this->projectId)
            ->where("a.user_id = " . (int)$this->userId);

        $this->db->setQuery($query, 0, 1);
        $result = $this->db->loadResult();

        return (bool)$result;
    }
}
