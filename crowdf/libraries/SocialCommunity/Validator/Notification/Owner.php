<?php
/**
 * @package      SocialCommunity\Notification
 * @subpackage   Validators
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

namespace SocialCommunity\Validator\Notification;

use Prism\Validator\ValidatorInterface;

defined('JPATH_BASE') or die;

/**
 * This class provides functionality for validation notification owner.
 *
 * @package      SocialCommunity\Notification
 * @subpackage   Validators
 */
class Owner implements ValidatorInterface
{
    protected $db;

    protected $id;
    protected $userId;

    /**
     * Initialize the object.
     *
     * <code>
     * $notificationId = 1;
     * $userId = 2;
     *
     * $owner = new SocialCommunity\Validator\Notification\Owner(JFactory::getDbo(), $notificationId, $userId);
     * </code>
     *
     * @param \JDatabaseDriver $db Database object.
     * @param int             $id Notification ID.
     * @param int             $userId    User ID.
     */
    public function __construct(\JDatabaseDriver $db, $id, $userId)
    {
        $this->db        = $db;
        $this->id        = $id;
        $this->userId    = $userId;
    }

    /**
     * Validate notification owner.
     *
     * <code>
     * $notificationId = 1;
     * $userId = 2;
     *
     * $owner = new SocialCommunity\Validator\Notification\Owner(JFactory::getDbo(), $notificationId, $userId);
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
            ->from($this->db->quoteName("#__itpsc_notifications", "a"))
            ->where("a.id = " . (int)$this->id)
            ->where("a.user_id = " . (int)$this->userId);

        $this->db->setQuery($query, 0, 1);
        return (bool)$this->db->loadResult();
    }
}
