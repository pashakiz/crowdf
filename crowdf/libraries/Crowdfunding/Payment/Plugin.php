<?php
/**
 * @package      Crowdfunding
 * @subpackage   Plugins
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

namespace Crowdfunding\Payment;

use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;
use Joomla\String\String;
use Prism;
use Crowdfunding;
use EmailTemplates;

// no direct access
defined('_JEXEC') or die;

/**
 * Crowdfunding payment plugin class.
 *
 * @package      Crowdfunding
 * @subpackage   Plugin
 */
class Plugin extends \JPlugin
{
    /**
     * Affects constructor behavior. If true, language files will be loaded automatically.
     *
     * @var    boolean
     * @since  3.1
     */
    protected $autoloadLanguage = true;

    protected $paymentService;

    protected $log;
    protected $textPrefix;
    protected $debugType;

    /**
     * This property contains keys of response data
     * that will be used to be generated an array with extra data.
     *
     * @var array
     */
    protected $extraDataKeys = array();

    /**
     * @var Registry
     */
    public $params;

    public function __construct(&$subject, $config = array())
    {
        parent::__construct($subject, $config);

        // Prepare log object
        $registry = Registry::getInstance("com_crowdfunding");
        /** @var  $registry Registry */

        $fileName  = $registry->get("logger.file");
        $tableName = $registry->get("logger.table");

        // Create log object
        $this->log = new Prism\Log\Log();

        // Set database writer.
        $this->log->addWriter(new Prism\Log\Writer\Database(\JFactory::getDbo(), $tableName));

        // Set file writer.
        if (!empty($fileName)) {
            $app = \JFactory::getApplication();
            /** @var $app \JApplicationSite */

            $file = \JPath::clean($app->get("log_path") . DIRECTORY_SEPARATOR . $fileName);
            $this->log->addWriter(new Prism\Log\Writer\File($file));
        }

    }


    /**
     * Update rewards properties - availability, distributed,...
     *
     * @param $data
     *
     * @return \Crowdfunding\Reward|null
     */
    protected function updateReward($data)
    {
        // Get reward.
        $keys = array(
            "id"         => ArrayHelper::getValue($data, "reward_id"),
            "project_id" => ArrayHelper::getValue($data, "project_id")
        );
        
        $reward = new Crowdfunding\Reward(\JFactory::getDbo());
        $reward->load($keys);

        // DEBUG DATA
        JDEBUG ? $this->log->add(\JText::_($this->textPrefix . "_DEBUG_REWARD_OBJECT"), $this->debugType, $reward->getProperties()) : null;

        // Check for valid reward.
        if (!$reward->getId()) {

            // Log data in the database
            $this->log->add(
                \JText::_($this->textPrefix . "_ERROR_INVALID_REWARD"),
                $this->debugType,
                array("data" => $data, "reward object" => $reward->getProperties())
            );

            return null;
        }

        // Check for valida amount between reward value and payed by user
        $txnAmount = ArrayHelper::getValue($data, "txn_amount");
        if ($txnAmount < $reward->getAmount()) {

            // Log data in the database
            $this->log->add(
                \JText::_($this->textPrefix . "_ERROR_INVALID_REWARD_AMOUNT"),
                $this->debugType,
                array("data" => $data, "reward object" => $reward->getProperties())
            );

            return null;
        }

        // Verify the availability of rewards
        if ($reward->isLimited() and !$reward->getAvailable()) {

            // Log data in the database
            $this->log->add(
                \JText::_($this->textPrefix . "_ERROR_REWARD_NOT_AVAILABLE"),
                $this->debugType,
                array("data" => $data, "reward object" => $reward->getProperties())
            );

            return null;
        }

        // Increase the number of distributed rewards
        // if there is a limit.
        if ($reward->isLimited()) {
            $reward->increaseDistributed();
            $reward->updateDistributed();
        }

        return $reward;
    }

    /**
     * This method is invoked when the administrator changes transaction status from the backend.
     *
     * @param string $context   This string gives information about that where it has been executed the trigger.
     * @param object $item      A transaction data.
     * @param string $oldStatus Old status
     * @param string $newStatus New status
     *
     * @return void
     */
    public function onTransactionChangeState($context, &$item, $oldStatus, $newStatus)
    {
        $allowedContexts = array("com_crowdfunding.transaction", "com_crowdfundingfinance.transaction");
        if (!in_array($context, $allowedContexts)) {
            return;
        }

        $app = \JFactory::getApplication();
        /** @var $app \JApplicationSite */

        if ($app->isSite()) {
            return;
        }

        $doc = \JFactory::getDocument();
        /**  @var $doc \JDocumentHtml */

        // Check document type
        $docType = $doc->getType();
        if (strcmp("html", $docType) != 0) {
            return;
        }

        // Verify the service provider.
        $paymentService = str_replace(" ", "", String::strtolower(String::trim($item->service_provider)));
        if (strcmp($this->paymentService, $paymentService) != 0) {
            return;
        }

        if (strcmp($oldStatus, "completed") == 0) { // Remove funds if someone change the status from completed to other one.

            $project = new Crowdfunding\Project(\JFactory::getDbo());
            $project->load($item->project_id);

            // DEBUG DATA
            JDEBUG ? $this->log->add(\JText::_($this->textPrefix . "_DEBUG_BCSNC"), $this->debugType, $project->getProperties()) : null;

            $project->removeFunds($item->txn_amount);
            $project->storeFunds();

            // DEBUG DATA
            JDEBUG ? $this->log->add(\JText::_($this->textPrefix . "_DEBUG_ACSNC"), $this->debugType, $project->getProperties()) : null;

        } elseif (strcmp($newStatus, "completed") == 0) { // Add funds if someone change the status to completed.

            $project = new Crowdfunding\Project(\JFactory::getDbo());
            $project->load($item->project_id);

            // DEBUG DATA
            JDEBUG ? $this->log->add(\JText::_($this->textPrefix . "_DEBUG_BCSTC"), $this->debugType, $project->getProperties()) : null;

            $project->addFunds($item->txn_amount);
            $project->storeFunds();

            // DEBUG DATA
            JDEBUG ? $this->log->add(\JText::_($this->textPrefix . "_DEBUG_ACSTC"), $this->debugType, $project->getProperties()) : null;
        }

    }

    /**
     * Send emails to the administrator, project owner and the user who have made a donation.
     *
     * @param object   $project
     * @param object   $transaction
     * @param Registry $params
     * @param object   $reward
     */
    protected function sendMails($project, $transaction, $params, $reward = null)
    {
        $app = \JFactory::getApplication();
        /** @var $app \JApplicationSite */

        // Get website
        $uri     = \JUri::getInstance();
        $website = $uri->toString(array("scheme", "host"));

        $emailMode = $this->params->get("email_mode", "plain");

        $componentParams = \JComponentHelper::getParams("com_crowdfunding");

        $currency   = Crowdfunding\Currency::getInstance(\JFactory::getDbo(), $componentParams->get("project_currency"));
        $amount     = new Crowdfunding\Amount($componentParams);
        $amount->setCurrency($currency);

        // Prepare data for parsing.
        $data = array(
            "site_name"      => $app->get("sitename"),
            "site_url"       => \JUri::root(),
            "item_title"     => $project->title,
            "item_url"       => $website . \JRoute::_(\CrowdfundingHelperRoute::getDetailsRoute($project->slug, $project->catslug)),
            "amount"         => $amount->setValue($transaction->txn_amount)->formatCurrency(),
            "transaction_id" => $transaction->txn_id,
            "reward_title"   => "",
            "delivery_date"  => "",
            "payer_name"     => "",
            "payer_email"    => ""
        );

        // Set reward data.
        if (!empty($reward)) {
            $data["reward_title"] = $reward->title;
            if ($reward->delivery != "0000-00-00") {
                $date = new \JDate($reward->delivery);
                $data["delivery_date"] = $date->format("d F Y");
            }
        }

        // Prepare data about payer if he is NOT anonymous ( is registered user with profile ).
        if (!empty($transaction->investor_id)) {
            $investor            = \JFactory::getUser($transaction->investor_id);
            $data["payer_email"] = $investor->get("email");
            $data["payer_name"]  = $investor->get("name");
        }

        // Send mail to the administrator
        $emailId = $this->params->get("admin_mail_id", 0);
        if (!empty($emailId)) {

            $email = new EmailTemplates\Email();
            $email->setDb(\JFactory::getDbo());
            $email->load($emailId);

            if (!$email->getSenderName()) {
                $email->setSenderName($app->get("fromname"));
            }
            if (!$email->getSenderEmail()) {
                $email->setSenderEmail($app->get("mailfrom"));
            }

            // Prepare recipient data.
            $componentParams = \JComponentHelper::getParams("com_crowdfunding");
            /** @var  $componentParams Registry */

            $recipientId = $componentParams->get("administrator_id");
            if (!empty($recipientId)) {
                $recipient     = \JFactory::getUser($recipientId);
                $recipientName = $recipient->get("name");
                $recipientMail = $recipient->get("email");
            } else {
                $recipientName = $app->get("fromname");
                $recipientMail = $app->get("mailfrom");
            }

            // Prepare data for parsing
            $data["sender_name"]     = $email->getSenderName();
            $data["sender_email"]    = $email->getSenderEmail();
            $data["recipient_name"]  = $recipientName;
            $data["recipient_email"] = $recipientMail;

            $email->parse($data);
            $subject = $email->getSubject();
            $body    = $email->getBody($emailMode);

            $mailer = \JFactory::getMailer();
            if (strcmp("html", $emailMode) == 0) { // Send as HTML message
                $return = $mailer->sendMail($email->getSenderEmail(), $email->getSenderName(), $recipientMail, $subject, $body, Prism\Constants::MAIL_MODE_HTML);
            } else { // Send as plain text.
                $return = $mailer->sendMail($email->getSenderEmail(), $email->getSenderName(), $recipientMail, $subject, $body, Prism\Constants::MAIL_MODE_PLAIN);
            }

            // Check for an error.
            if ($return !== true) {
                $this->log->add(\JText::_($this->textPrefix . "_ERROR_MAIL_SENDING_ADMIN"), $this->debugType, $mailer->ErrorInfo);
            }

        }

        // Send mail to project owner.
        $emailId = $this->params->get("creator_mail_id", 0);
        if (!empty($emailId)) {

            $email = new EmailTemplates\Email();
            $email->setDb(\JFactory::getDbo());
            $email->load($emailId);

            if (!$email->getSenderName()) {
                $email->setSenderName($app->get("fromname"));
            }
            if (!$email->getSenderEmail()) {
                $email->setSenderEmail($app->get("mailfrom"));
            }

            $user          = \JFactory::getUser($transaction->receiver_id);
            $recipientName = $user->get("name");
            $recipientMail = $user->get("email");

            // Prepare data for parsing
            $data["sender_name"]     = $email->getSenderName();
            $data["sender_email"]    = $email->getSenderEmail();
            $data["recipient_name"]  = $recipientName;
            $data["recipient_email"] = $recipientMail;

            $email->parse($data);
            $subject = $email->getSubject();
            $body    = $email->getBody($emailMode);

            $mailer = \JFactory::getMailer();
            if (strcmp("html", $emailMode) == 0) { // Send as HTML message
                $return = $mailer->sendMail($email->getSenderEmail(), $email->getSenderName(), $recipientMail, $subject, $body, Prism\Constants::MAIL_MODE_HTML);

            } else { // Send as plain text.
                $return = $mailer->sendMail($email->getSenderEmail(), $email->getSenderName(), $recipientMail, $subject, $body, Prism\Constants::MAIL_MODE_PLAIN);

            }

            // Check for an error.
            if ($return !== true) {
                $this->log->add(\JText::_($this->textPrefix . "_ERROR_MAIL_SENDING_PROJECT_OWNER"), $this->debugType, $mailer->ErrorInfo);
            }
        }

        // Send mail to backer.
        $emailId = $this->params->get("user_mail_id", 0);
        if (!empty($emailId) and !empty($transaction->investor_id)) {

            $email = new EmailTemplates\Email();
            $email->setDb(\JFactory::getDbo());
            $email->load($emailId);

            if (!$email->getSenderName()) {
                $email->setSenderName($app->get("fromname"));
            }
            if (!$email->getSenderEmail()) {
                $email->setSenderEmail($app->get("mailfrom"));
            }

            $user          = \JFactory::getUser($transaction->investor_id);
            $recipientName = $user->get("name");
            $recipientMail = $user->get("email");

            // Prepare data for parsing
            $data["sender_name"]     = $email->getSenderName();
            $data["sender_email"]    = $email->getSenderEmail();
            $data["recipient_name"]  = $recipientName;
            $data["recipient_email"] = $recipientMail;

            $email->parse($data);
            $subject = $email->getSubject();
            $body    = $email->getBody($emailMode);

            $mailer = \JFactory::getMailer();
            if (strcmp("html", $emailMode) == 0) { // Send as HTML message
                $return = $mailer->sendMail($email->getSenderEmail(), $email->getSenderName(), $recipientMail, $subject, $body, Prism\Constants::MAIL_MODE_HTML);

            } else { // Send as plain text.
                $return = $mailer->sendMail($email->getSenderEmail(), $email->getSenderName(), $recipientMail, $subject, $body, Prism\Constants::MAIL_MODE_PLAIN);

            }

            // Check for an error.
            if ($return !== true) {
                $this->log->add(\JText::_($this->textPrefix . "_ERROR_MAIL_SENDING_PROJECT_OWNER"), $this->debugType, $mailer->ErrorInfo);
            }

        }

    }

    /**
     * This method returns payment session.
     *
     * @param array $options The keys used to load payment session data from database.
     *
     * @throws \UnexpectedValueException
     *
     * @return Crowdfunding\Payment\Session
     */
    public function getPaymentSession(array $options)
    {
        $id        = ArrayHelper::getValue($options, "id");
        $sessionId = ArrayHelper::getValue($options, "session_id");
        $uniqueKey = ArrayHelper::getValue($options, "unique_key");

        // Prepare keys for anonymous user.
        if (!empty($id)) {
            $keys = $id;
        } elseif (!empty($sessionId)) {
            $keys = array(
                "session_id"   => $sessionId
            );
        } elseif (!empty($uniqueKey)) { // Prepare keys to get record by unique key.
            $keys = array(
                "unique_key" => $uniqueKey,
            );
        } else {
            throw new \UnexpectedValueException(\JText::_("LIB_CROWDFUNDING_INVALID_PAYMENT_SESSION_KEYS"));
        }

        $paymentSession = new Crowdfunding\Payment\Session(\JFactory::getDbo());
        $paymentSession->load($keys);

        return $paymentSession;
    }

    /**
     * Generate a system message.
     *
     * @param string $message
     *
     * @return string
     */
    protected function generateSystemMessage($message)
    {
        $html = '
        <div id="system-message-container">
			<div id="system-message">
                <div class="alert alert-error">
                    <a data-dismiss="alert" class="close">×</a>
                    <h4 class="alert-heading">Error</h4>
                    <div>
                        <p>' . htmlentities($message, ENT_QUOTES, "UTF-8") . '</p>
                    </div>
                </div>
            </div>
	    </div>';

        return $html;
    }

    /**
     * This method get fees from Crowdfunding Finance.
     *
     * @param $fundingType
     *
     * @return array
     */
    protected function getFees($fundingType)
    {
        $fees = array();

        if (\JComponentHelper::isEnabled("com_crowdfundingfinance")) {

            $params = \JComponentHelper::getParams("com_crowdfundingfinance");
            /** @var $params Registry */

            if (strcmp("FIXED", $fundingType) == 0) {
                if ($params->get("fees_fixed_campaign_percent")) {
                    $fees["fixed_campaign_percent"] = $params->get("fees_fixed_campaign_percent");
                }

                if ($params->get("fees_fixed_campaign_amount")) {
                    $fees["fixed_campaign_amount"] = $params->get("fees_fixed_campaign_amount");
                }
            }

            if (strcmp("FLEXIBLE", $fundingType) == 0) {
                if ($params->get("fees_flexible_campaign_percent")) {
                    $fees["flexible_campaign_percent"] = $params->get("fees_flexible_campaign_percent");
                }

                if ($params->get("fees_flexible_campaign_amount")) {
                    $fees["flexible_campaign_amount"] = $params->get("fees_flexible_campaign_amount");
                }
            }
        }

        return $fees;
    }

    /**
     * This method calculates a fee which is set by Crowdfunding Finance.
     *
     * @param $fundingType
     * @param $fees
     * @param $txnAmount
     *
     * @return float
     */
    protected function calculateFee($fundingType, $fees, $txnAmount)
    {
        $result = 0;

        switch ($fundingType) {

            case "FIXED":
                $feePercent = ArrayHelper::getValue($fees, "fixed_campaign_percent", 0, "float");
                $feeAmount  = ArrayHelper::getValue($fees, "fixed_campaign_amount", 0, "float");

                break;

            case "FLEXIBLE":
                $feePercent = ArrayHelper::getValue($fees, "flexible_campaign_percent", 0, "float");
                $feeAmount  = ArrayHelper::getValue($fees, "flexible_campaign_amount", 0, "float");
                break;
        }

        // Calculate fee based on percent.
        if (!empty($feePercent)) {

            // Calculate amount.
            $math = new Prism\Math();
            $math->calculateValueFromPercent($feePercent, $txnAmount);

            // Add the amount to the result.
            $feePercentAmount = (string)$math;

            if ($txnAmount > $feePercentAmount) {
                $result += (float)$feePercentAmount;
            }
        }

        // Calculate fees based on amount.
        if (!empty($feeAmount) and ($txnAmount > $feeAmount)) {
            $result += $feeAmount;
        }

        // Check for invalid value that is less than zero.
        if ($result < 0) {
            $result = 0;
        }

        return (float)$result;
    }

    protected function getCallbackUrl($htmlEncoded = false)
    {
        $page = String::trim($this->params->get('callback_url'));

        $uri    = \JUri::getInstance();
        $domain = $uri->toString(array("host"));

        // Encode to valid HTML.
        if ($htmlEncoded) {
            $page = str_replace("&", "&amp;", $page);
        }

        // Add the domain to the URL.
        if (false == strpos($page, $domain)) {
            $page = \JUri::root() . $page;
        }

        return $page;
    }

    protected function getReturnUrl($slug, $catslug)
    {
        $page = String::trim($this->params->get('return_url'));
        if (!$page) {
            $uri  = \JUri::getInstance();
            $page = $uri->toString(array("scheme", "host")) . \JRoute::_(\CrowdfundingHelperRoute::getBackingRoute($slug, $catslug, "share"), false);
        }

        return $page;
    }

    protected function getCancelUrl($slug, $catslug)
    {
        $page = String::trim($this->params->get('cancel_url'));
        if (!$page) {
            $uri  = \JUri::getInstance();
            $page = $uri->toString(array("scheme", "host")) . \JRoute::_(\CrowdfundingHelperRoute::getBackingRoute($slug, $catslug, "default"), false);
        }

        return $page;
    }

    /**
     * Prepare extra data.
     *
     * @param array  $data
     * @param string $note
     *
     * @return array
     */
    protected function prepareExtraData($data, $note = "")
    {
        $date        = new \JDate();
        $trackingKey = $date->toUnix();

        $extraData = array(
            $trackingKey => array()
        );

        foreach ($this->extraDataKeys as $key) {
            if (isset($data[$key])) {
                $extraData[$trackingKey][$key] = $data[$key];
            }
        }

        // Set a note.
        if (!empty($note)) {
            $extraData[$trackingKey]["NOTE"] = $note;
        }

        return $extraData;
    }

    /**
     * Check for valid payment gateway.
     *
     * @param string $gateway
     *
     * @return bool
     */
    protected function isValidPaymentGateway($gateway)
    {
        $value1 = String::strtolower($this->paymentService);
        $value2 = String::strtolower($gateway);

        if (String::strcmp($value1, $value2) != 0) {
            return false;
        }

        return true;
    }

    /**
     * Remove an intention and payment session records.
     *
     * @param Crowdfunding\Payment\Session $paymentSession
     * @param string                       $txnStatus
     */
    protected function closePaymentSession($paymentSession, $txnStatus)
    {
        if (strcmp("completed", $txnStatus) == 0) {

            // Remove intention record.
            if ($paymentSession->getIntentionId()) {

                $intention = new Crowdfunding\Intention(\JFactory::getDbo());
                $intention->load($paymentSession->getIntentionId());

                if ($intention->getId()) {
                    $intention->delete();
                }
            }

            // Remove payment session record.
            $paymentSession->delete();
        }
    }
}
