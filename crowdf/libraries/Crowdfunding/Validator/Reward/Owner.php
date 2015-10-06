<?php
/**
 * @package      Crowdfunding\Rewards
 * @subpackage   Validators
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

namespace Crowdfunding\Validator\Reward;

use Prism\Validator\ValidatorInterface;

defined('JPATH_BASE') or die;

/**
 * This class provides functionality for validation reward owner.
 *
 * @package      Crowdfunding\Rewards
 * @subpackage   Validators
 */
class Owner implements ValidatorInterface
{
    protected $db;
    protected $rewardId;
    protected $userId;

    /**
     * Initialize the object.
     *
     * <code>
     * $rewardId = 1;
     * $userId = 2;
     *
     * $owner = new Crowdfunding\Validator\Reward\Owner(\JFactory::getDbo(), $rewardId, $userId);
     * </code>
     *
     * @param \JDatabaseDriver $db        Database object.
     * @param int             $rewardId  Reward ID.
     * @param int             $userId    User ID.
     */
    public function __construct(\JDatabaseDriver $db, $rewardId, $userId)
    {
        $this->db        = $db;
        $this->rewardId  = $rewardId;
        $this->userId    = $userId;
    }

    /**
     * Validate reward owner.
     *
     * <code>
     * $rewardId = 1;
     * $userId = 2;
     *
     * $owner = new Crowdfunding\Validator\Reward\Owner(\JFactory::getDbo(), $rewardId, $userId);
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
            ->select("b.user_id")
            ->from($this->db->quoteName("#__crowdf_rewards", "a"))
            ->innerJoin($this->db->quoteName("#__crowdf_projects", "b") . " ON a.project_id = b.id")
            ->where("a.id = " . (int)$this->rewardId);

        $this->db->setQuery($query);
        $userId = $this->db->loadResult();

        return (bool)($this->userId == $userId);
    }
}
