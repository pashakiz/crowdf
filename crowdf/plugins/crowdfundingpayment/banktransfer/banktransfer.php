<?php
/**
 * @package      Crowdfunding
 * @subpackage   Plugins
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// no direct access
defined('_JEXEC') or die;

jimport("Prism.init");
jimport("Crowdfunding.init");
jimport("EmailTemplates.init");

/**
 * Crowdfunding Bank Transfer Payment Plugin
 *
 * @package      Crowdfunding
 * @subpackage   Plugins
 */
class plgCrowdfundingPaymentBankTransfer extends Crowdfunding\Payment\Plugin
{
    protected $paymentService = "banktransfer";
    protected $version        = "2.0";

    protected $textPrefix = "PLG_CROWDFUNDINGPAYMENT_BANKTRANSFER";
    protected $debugType = "BANKTRANSFER_PAYMENT_PLUGIN_DEBUG";

    /**
     * @var JApplicationSite
     */
    protected $app;

    /**
     * This method prepares a payment gateway - buttons, forms,...
     * That gateway will be displayed on the summary page as a payment option.
     *
     * @param string    $context This string gives information about that where it has been executed the trigger.
     * @param object    $item    A project data.
     * @param Joomla\Registry\Registry $params  The parameters of the component
     *
     * @return null|string
     */
    public function onProjectPayment($context, &$item, &$params)
    {
        if (strcmp("com_crowdfunding.payment", $context) != 0) {
            return null;
        }

        if ($this->app->isAdmin()) {
            return null;
        }

        $doc = JFactory::getDocument();
        /**  @var $doc JDocumentHtml */

        // Check document type
        $docType = $doc->getType();
        if (strcmp("html", $docType) != 0) {
            return null;
        }

        JHtml::_('jquery.framework');
        JText::script('PLG_CROWDFUNDINGPAYMENT_BANKTRANSFER_REGISTER_TRANSACTION_QUESTION');

        // Get the path for the layout file
        $path = JPath::clean(JPluginHelper::getLayoutPath('crowdfundingpayment', 'banktransfer'));

        ob_start();
        include $path;
        $html = ob_get_clean();

        return $html;

    }

    /**
     * This method performs the transaction.
     *
     * @param string $context
     * @param Joomla\Registry\Registry $params
     *
     * @return null|array
     */
    public function onPaymentNotify($context, &$params)
    {
        if (strcmp("com_crowdfunding.notify.banktransfer", $context) != 0) {
            return null;
        }

        if ($this->app->isAdmin()) {
            return null;
        }

        $doc = JFactory::getDocument();
        /**  @var $doc JDocumentHtml */

        // Check document type
        $docType = $doc->getType();
        if (strcmp("raw", $docType) != 0) {
            return null;
        }

        $projectId = $this->app->input->getInt("pid");
        $amount    = $this->app->input->getFloat("amount");

        // Prepare the array that will be returned by this method
        $result = array(
            "project"         => null,
            "reward"          => null,
            "transaction"     => null,
            "payment_session" => null,
            "redirect_url"    => null,
            "message"         => null
        );

        // Get project
        $project = new Crowdfunding\Project(JFactory::getDbo());
        $project->load($projectId);

        // DEBUG DATA
        JDEBUG ? $this->log->add(JText::_($this->textPrefix . "_DEBUG_PROJECT_OBJECT"), $this->debugType, $project->getProperties()) : null;

        // Check for valid project
        if (!$project->getId()) {

            // Log data in the database
            $this->log->add(
                JText::_($this->textPrefix . "_ERROR_INVALID_PROJECT"),
                $this->debugType,
                array(
                    "PROJECT OBJECT" => $project->getProperties(),
                    "REQUEST METHOD" => $this->app->input->getMethod(),
                    "_REQUEST"       => $_REQUEST
                )
            );

            return null;
        }

        $currencyId = $params->get("project_currency");
        $currency   = Crowdfunding\Currency::getInstance(JFactory::getDbo(), $currencyId, $params);

        // Prepare return URL
        $result["redirect_url"] = Joomla\String\String::trim($this->params->get('return_url'));
        if (!$result["redirect_url"]) {

            $filter = JFilterInput::getInstance();

            $uri = JUri::getInstance();
            $domain = $filter->clean($uri->toString(array("scheme", "host")));

            $result["redirect_url"] = $domain . JRoute::_(CrowdfundingHelperRoute::getBackingRoute($project->getSlug(), $project->getCatslug(), "share"), false);
        }

        // DEBUG DATA
        JDEBUG ? $this->log->add(JText::_($this->textPrefix . "_DEBUG_RETURN_URL"), $this->debugType, $result["redirect_url"]) : null;

        // Payment Session

        $userId  = JFactory::getUser()->get("id");
        $aUserId = $this->app->getUserState("auser_id");

        // Reset anonymous user hash ID,
        // because the payment session based in it will be removed when transaction completes.
        if (!empty($aUserId)) {
            $this->app->setUserState("auser_id", "");
        }

        $paymentSessionContext    = Crowdfunding\Constants::PAYMENT_SESSION_CONTEXT . $project->getId();
        $paymentSessionLocal      = $this->app->getUserState($paymentSessionContext);

        $paymentSession = $this->getPaymentSession(array(
            "session_id"    => $paymentSessionLocal->session_id
        ));

        // DEBUG DATA
        JDEBUG ? $this->log->add(JText::_($this->textPrefix . "_DEBUG_PAYMENT_SESSION_OBJECT"), $this->debugType, $paymentSession->getProperties()) : null;

        // Validate payment session record.
        if (!$paymentSession->getId()) {

            // Log data in the database
            $this->log->add(
                JText::_($this->textPrefix . "_ERROR_INVALID_PAYMENT_SESSION"),
                $this->debugType,
                $paymentSession->getProperties()
            );

            // Send response to the browser
            $response = array(
                "success" => false,
                "title"   => JText::_($this->textPrefix . "_FAIL"),
                "text"    => JText::_($this->textPrefix . "_ERROR_INVALID_PROJECT")
            );

            return $response;
        }

        // Validate a reward and update the number of distributed ones.
        // If the user is anonymous, the system will store 0 for reward ID.
        // The anonymous users can't select rewards.
        $rewardId = ($paymentSession->isAnonymous()) ? 0 : (int)$paymentSession->getRewardId();
        if (!empty($rewardId)) {

            $validData = array(
                "reward_id"  => $rewardId,
                "project_id" => $projectId,
                "txn_amount" => $amount
            );

            $reward  = $this->updateReward($validData);

            // Validate the reward.
            if (!$reward) {
                $rewardId = 0;
            }
        }

        // Prepare transaction data
        $transactionId = new Prism\String();
        $transactionId->generateRandomString(12, "BT");

        $transactionId   = Joomla\String\String::strtoupper($transactionId);
        $transactionData = array(
            "txn_amount"       => $amount,
            "txn_currency"     => $currency->getCode(),
            "txn_status"       => "pending",
            "txn_id"           => $transactionId,
            "project_id"       => $projectId,
            "reward_id"        => $rewardId,
            "investor_id"      => (int)$userId,
            "receiver_id"      => (int)$project->getUserId(),
            "service_provider" => "Bank Transfer"
        );

        // DEBUG DATA
        JDEBUG ? $this->log->add(JText::_($this->textPrefix . "_DEBUG_TRANSACTION_DATA"), $this->debugType, $transactionData) : null;

        // Auto complete transaction
        if ($this->params->get("auto_complete", 0)) {
            $transactionData["txn_status"] = "completed";
            $project->addFunds($amount);
            $project->storeFunds();
        }

        // Store transaction data
        $transaction = new Crowdfunding\Transaction(JFactory::getDbo());
        $transaction->bind($transactionData);

        $transaction->store();

        // Generate object of data, based on the transaction properties.
        $properties = $transaction->getProperties();
        $result["transaction"] = Joomla\Utilities\ArrayHelper::toObject($properties);

        // Generate object of data, based on the project properties.
        $properties        = $project->getProperties();
        $result["project"] = Joomla\Utilities\ArrayHelper::toObject($properties);

        // Generate object of data, based on the reward properties.
        if (!empty($reward)) {
            $properties       = $reward->getProperties();
            $result["reward"] = Joomla\Utilities\ArrayHelper::toObject($properties);
        }

        // Generate data object, based on the payment session properties.
        $properties       = $paymentSession->getProperties();
        $result["payment_session"] = Joomla\Utilities\ArrayHelper::toObject($properties);

        // Set message to the user.
        $result["message"] = JText::sprintf($this->textPrefix . "_TRANSACTION_REGISTERED", $transaction->getTransactionId(), $transaction->getTransactionId());

        // DEBUG DATA
        JDEBUG ? $this->log->add(JText::_($this->textPrefix . "_DEBUG_RESULT_DATA"), $this->debugType, $result) : null;

        // Close payment session and remove payment session record.
        $txnStatus = (isset($result["transaction"]->txn_status)) ? $result["transaction"]->txn_status : null;
        $this->closePaymentSession($paymentSession, $txnStatus);

        return $result;
    }

    /**
     * This method is invoked after complete payment.
     * It is used to be sent mails to user and administrator
     *
     * @param object $context
     * @param object $transaction Transaction data
     * @param Joomla\Registry\Registry $params Component parameters
     * @param object $project Project data
     * @param object $reward Reward data
     * @param object $paymentSession Payment session data.
     */
    public function onAfterPayment($context, &$transaction, &$params, &$project, &$reward, &$paymentSession)
    {
        if (strcmp("com_crowdfunding.notify.banktransfer", $context) != 0) {
            return;
        }

        if ($this->app->isAdmin()) {
            return;
        }

        $doc = JFactory::getDocument();
        /**  @var $doc JDocumentHtml */

        // Check document type
        $docType = $doc->getType();
        if (strcmp("raw", $docType) != 0) {
            return;
        }

        // Send mails
        $this->sendMails($project, $transaction, $params, $reward);
    }
}
