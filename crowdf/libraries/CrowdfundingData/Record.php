<?php
/**
 * @package      CrowdfundingData
 * @subpackage   Records
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2014 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

namespace CrowdfundingData;

use Prism;

defined('JPATH_PLATFORM') or die;

/**
 * This class provides functionality that manage records.
 *
 * @package      CrowdfundingData
 * @subpackage   Records
 */
class Record extends Prism\Database\Table
{
    protected $id;
    protected $name;
    protected $address;
    protected $record_date;
    protected $country_id;
    protected $project_id;
    protected $transaction_id;
    protected $user_id;
    protected $auser_id;
    protected $session_id;

    protected $country;

    /**
     * Load data of a record from database.
     *
     * <code>
     * $keys = array(
     *    "user_id" => 1
     * );
     *
     * $record    = new CrowdfundingData\Record(\JFactory::getDbo());
     * $record->load($keys);
     * </code>
     *
     * @param int|array $keys
     * @param int|array $options
     */
    public function load($keys, $options = array())
    {
        $query = $this->db->getQuery(true);

        $query
            ->select(
                "a.id, a.name, a.address, a.country_id, a.project_id, a.transaction_id, a.user_id, a.session_id, " .
                "b.name AS country"
            )
            ->from($this->db->quoteName("#__cfdata_records", "a"))
            ->leftJoin($this->db->quoteName("#__crowdf_countries", "b") . " ON a.country_id = b.id");

        if (!is_array($keys)) {
            $query->where("a.id = " . (int)$keys);
        } else {
            foreach ($keys as $key => $value) {
                $query->where($this->db->quoteName("a.".$key) . "=" . $this->db->quote($value));
            }
        }

        $this->db->setQuery($query);
        $result = $this->db->loadAssoc();

        if (!$result) {
            $result = array();
        }

        $this->bind($result);
    }

    /**
     * Store data to database.
     *
     * <code>
     * $data = array(
     *  "name"    => "John Dow",
     *  "user_id" => 1
     * );
     *
     * $record    = new CrowdfundingData\Record(\JFactory::getDbo());
     * $record->bind($data);
     * $record->store();
     * </code>
     */
    public function store()
    {
        if (!$this->id) { // Insert
            $this->insertObject();
        } else { // Update
            $this->updateObject();
        }
    }

    protected function updateObject()
    {
        $query = $this->db->getQuery(true);

        $query
            ->update($this->db->quoteName("#__cfdata_records"))
            ->set($this->db->quoteName("name") . "=" . $this->db->quote($this->name))
            ->set($this->db->quoteName("address") . "=" . $this->db->quote($this->address))
            ->set($this->db->quoteName("country_id") . "=" . $this->db->quote($this->country_id))
            ->set($this->db->quoteName("project_id") . "=" . $this->db->quote($this->project_id))
            ->set($this->db->quoteName("transaction_id") . "=" . $this->db->quote($this->transaction_id))
            ->set($this->db->quoteName("user_id") . "=" . $this->db->quote($this->user_id))
            ->set($this->db->quoteName("session_id") . "=" . $this->db->quote($this->session_id))
            ->where($this->db->quoteName("id") ."=". (int)$this->id);

        $this->db->setQuery($query);
        $this->db->execute();
    }

    protected function insertObject()
    {
        $recordDate   = (!$this->record_date) ? "NULL" : $this->db->quote($this->record_date);

        $query = $this->db->getQuery(true);

        $query
            ->insert($this->db->quoteName("#__cfdata_records"))
            ->set($this->db->quoteName("name") . "=" . $this->db->quote($this->name))
            ->set($this->db->quoteName("address") . "=" . $this->db->quote($this->address))
            ->set($this->db->quoteName("record_date") . "=" . $recordDate)
            ->set($this->db->quoteName("country_id") . "=" . $this->db->quote($this->country_id))
            ->set($this->db->quoteName("project_id") . "=" . $this->db->quote($this->project_id))
            ->set($this->db->quoteName("transaction_id") . "=" . $this->db->quote($this->transaction_id))
            ->set($this->db->quoteName("user_id") . "=" . $this->db->quote($this->user_id))
            ->set($this->db->quoteName("session_id") . "=" . $this->db->quote($this->session_id));

        $this->db->setQuery($query);
        $this->db->execute();

        $this->id = $this->db->insertid();
    }

    /**
     * Return record ID.
     *
     * <code>
     * $recordId  = 1;
     *
     * $record    = new CrowdfundingData\Record(\JFactory::getDbo());
     * $record->load($recordId);
     *
     * if (!$record->getId()) {
     * ...
     * }
     * </code>
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Return country ID.
     *
     * <code>
     * $recordId  = 1;
     *
     * $record    = new CrowdfundingData\Record(\JFactory::getDbo());
     * $record->load($recordId);
     *
     * $countryId = $record->getCountryId();
     * </code>
     */
    public function getCountryId()
    {
        return $this->country_id;
    }

    /**
     * Return country name.
     *
     * <code>
     * $recordId  = 1;
     *
     * $record    = new CrowdfundingData\Record(\JFactory::getDbo());
     * $record->load($recordId);
     *
     * $country = $record->getCountry();
     * </code>
     */
    public function getCountry()
    {
        return (string)$this->country;
    }

    /**
     * Return user ID.
     *
     * <code>
     * $recordId  = 1;
     *
     * $record    = new CrowdfundingData\Record(\JFactory::getDbo());
     * $record->load($recordId);
     *
     * $userId = $record->getUserId();
     * </code>
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * Return user ID.
     *
     * <code>
     * $recordId  = 1;
     * $userId  = 2;
     *
     * $record    = new CrowdfundingData\Record(\JFactory::getDbo());
     * $record->load($recordId);
     *
     * $record->setUserId($user);
     * </code>
     *
     * @param int $userId
     *
     * @return self
     */
    public function setUserId($userId)
    {
        $this->user_id = $userId;

        return $this;
    }

    /**
     * Return user ID (hash) of anonymous user.
     *
     * <code>
     * $recordId  = 1;
     *
     * $record    = new CrowdfundingData\Record(\JFactory::getDbo());
     * $record->load($recordId);
     *
     * $sessionId = $record->getSessionId();
     * </code>
     *
     * @return string
     */
    public function getSessionId()
    {
        return $this->session_id;
    }

    /**
     * Return session ID.
     *
     * <code>
     * $recordId  = 1;
     * $sessionId  = "session_id_1234"
     *
     * $record    = new CrowdfundingData\Record(\JFactory::getDbo());
     * $record->load($recordId);
     *
     * $record->setSessionId($sessionId);
     * </code>
     *
     * @param int $userId
     *
     * @return self
     */
    public function setAnonymousUserId($userId)
    {
        $this->auser_id = $userId;

        return $this;
    }

    /**
     * Return project ID.
     *
     * <code>
     * $recordId  = 1;
     *
     * $record    = new CrowdfundingData\Record(\JFactory::getDbo());
     * $record->load($recordId);
     *
     * $projectId = $record->getProjectIdUserId();
     * </code>
     */
    public function getProjectId()
    {
        return $this->project_id;
    }

    /**
     * Return reward ID.
     *
     * <code>
     * $recordId  = 1;
     *
     * $record    = new CrowdfundingData\Record();
     * $record->setDb(\JFactory::getDbo());
     * $record->load($recordId);
     *
     * $name = $record->getName();
     * </code>
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Return the date of the record.
     *
     * <code>
     * $recordId  = 1;
     *
     * $record    = new CrowdfundingData\Record();
     * $record->setDb(\JFactory::getDbo());
     * $record->load($recordId);
     *
     * $date = $record->getRecordDate();
     * </code>
     */
    public function getRecordDate()
    {
        return $this->record_date;
    }

    /**
     * Return transaction ID.
     *
     * <code>
     * $recordId  = 1;
     *
     * $record    = new CrowdfundingData\Record();
     * $record->setDb(\JFactory::getDbo());
     * $record->load($recordId);
     *
     * $txnId = $record->getTransactionId();
     * </code>
     */
    public function getTransactionId()
    {
        return $this->transaction_id;
    }

    /**
     * Return transaction ID.
     *
     * <code>
     * $recordId  = 1;
     * $transactionId = 2;
     *
     * $record    = new CrowdfundingData\Record();
     * $record->setDb(\JFactory::getDbo());
     * $record->load($recordId);
     *
     * $record->setTransactionId($transactionId);
     * </code>
     *
     * @param int $transactionId
     *
     * @return self
     */
    public function setTransactionId($transactionId)
    {
        $this->transaction_id = $transactionId;

        return $this;
    }

    /**
     * Return address.
     *
     * <code>
     * $recordId  = 1;
     *
     * $record    = new CrowdfundingData\Record();
     * $record->setDb(\JFactory::getDbo());
     * $record->load($recordId);
     *
     * $address = $record->getAddress();
     * </code>
     *
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Check if the record is of an anonymous user.
     *
     * <code>
     * $recordId  = 1;
     *
     * $record    = new CrowdfundingData\Record();
     * $record->setDb(\JFactory::getDbo());
     * $record->load($recordId);
     *
     * if (!$record->isAnonymous()) {
     * ...
     * }
     * </code>
     */
    public function isAnonymous()
    {
        return (!$this->auser_id) ? false : true;
    }
}
