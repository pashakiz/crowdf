<?php
/**
 * @package      CrowdfundingFinance
 * @subpackage   Payouts
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace CrowdfundingFinance;

use Prism\Database\TableImmutable;

defined('JPATH_PLATFORM') or die;

/**
 * This class provides functionality that manage a payout.
 *
 * @package      CrowdfundingFinance
 * @subpackage   Payouts
 */
class Payout extends TableImmutable
{
    /**
     * Project ID.
     *
     * @var int
     */
    protected $id;

    protected $paypal_email;
    protected $paypal_first_name;
    protected $paypal_last_name;
    protected $iban;
    protected $bank_account;

    /**
     * Load a payout data from database.
     *
     * <code>
     * $keys = array(
     *    "project_id" => 1
     * );
     *
     * $payout    = new CrowdfundingFinance\Payout();
     * $payout->setDb(\JFactory::getDbo());
     * $payout->load($keys);
     * </code>
     *
     * @param int $keys Project ID
     * @param array $options
     */
    public function load($keys, $options = array())
    {
        $query = $this->db->getQuery(true);

        $query
            ->select(
                "a.id, a.paypal_email, a.paypal_first_name, a.paypal_last_name, a.iban, a.bank_account "
            )
            ->from($this->db->quoteName("#__cffinance_payouts", "a"))
            ->where("a.id = " . (int)$keys);

        $this->db->setQuery($query);
        $result = $this->db->loadAssoc();

        if (!$result) {
            $result = array();
        }

        $this->bind($result);
    }

    /**
     * Return payout ID.
     *
     * <code>
     * $projectId  = 1;
     *
     * $payout    = new CrowdfundingFinance\Payout(\JFactory::getDbo());
     * $payout->load($projectId);
     *
     * if (!$payout->getId()) {
     * ...
     * }
     * </code>
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Return PayPal e-mail.
     *
     * <code>
     * $projectId  = 1;
     *
     * $payout    = new CrowdfundingFinance\Payout(\JFactory::getDbo());
     * $payout->load($projectId);
     *
     * $paypalEmail = $payout->getPaypalEmail();
     * </code>
     */
    public function getPaypalEmail()
    {
        return $this->paypal_email;
    }

    /**
     * Return PayPal First Name.
     *
     * <code>
     * $projectId  = 1;
     *
     * $payout    = new CrowdfundingFinance\Payout(\JFactory::getDbo());
     * $payout->load($projectId);
     *
     * $paypalFirstName = $payout->getPayPalFirstName();
     * </code>
     */
    public function getPaypalFirstName()
    {
        return (string)$this->paypal_first_name;
    }

    /**
     * Return PayPal last name.
     *
     * <code>
     * $projectId  = 1;
     *
     * $payout    = new CrowdfundingFinance\Payout(\JFactory::getDbo());
     * $payout->load($projectId);
     *
     * $paypalLastName = $payout->getPayPalLastName();
     * </code>
     */
    public function getPaypalLastName()
    {
        return $this->paypal_last_name;
    }

    /**
     * Return the IBAN of the user where the amount should be sent.
     *
     * <code>
     * $projectId  = 1;
     *
     * $payout    = new CrowdfundingFinance\Payout(\JFactory::getDbo());
     * $payout->load($projectId);
     *
     * $iban = $payout->getIban();
     * </code>
     */
    public function getIban()
    {
        return $this->iban;
    }

    /**
     * Return information about user bank account.
     *
     * <code>
     * $projectId  = 1;
     *
     * $payout    = new CrowdfundingFinance\Payout(\JFactory::getDbo());
     * $payout->load($projectId);
     *
     * $bankAccount = $payout->getBankAccount();
     * </code>
     */
    public function getBankAccount()
    {
        return $this->bank_account;
    }
}
