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
jimport("CrowdfundingFinance.init");

/**
 * Crowdfunding PayPal Adaptive payment plugin.
 *
 * @package      Crowdfunding
 * @subpackage   Plugins
 */
class plgCrowdfundingPaymentPayPalAdaptive extends Crowdfunding\Payment\Plugin
{
    protected $paymentService = "paypal";

    protected $textPrefix     = "PLG_CROWDFUNDINGPAYMENT_PAYPALADAPTIVE";
    protected $debugType      = "PAYMENT_PLUGIN_DEBUG_PAYPALADAPTIVE";

    /**
     * @var JApplicationSite
     */
    protected $app;

    protected $envelope = array(
        "errorLanguage" => "en_US",
        "detailLevel" => "returnAll"
    );

    /**
     * This method prepares a payment gateway - buttons, forms,...
     * That gateway will be displayed on the summary page as a payment option.
     *
     * @param string                   $context This string gives information about that where it has been executed the trigger.
     * @param object                   $item    A project data.
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

        // This is a URI path to the plugin folder
        $pluginURI = "plugins/crowdfundingpayment/paypalexpress";

        $html   = array();
        $html[] = '<div class="well">'; // Open "well".

        $html[] = '<h4><img src="' . $pluginURI . '/images/paypal_icon.png" width="36" height="32" alt="PayPal" />' . JText::_($this->textPrefix . "_TITLE") . '</h4>';
        $html[] = '<form action="' . JRoute::_("index.php?option=com_crowdfunding&task=payments.checkout") . '" method="post">';

        $html[] = '<input type="hidden" name="payment_service" value="PayPal" />';
        $html[] = '<input type="hidden" name="pid" value="' . $item->id . '" />';
        $html[] = JHtml::_('form.token');

        $this->prepareLocale($html);

        $html[] = '<img alt="" border="0" width="1" height="1" src="https://www.paypal.com/en_US/i/scr/pixel.gif" />';
        $html[] = '</form>';

        $html[] = '<p class="bg-info p-10-5"><span class="glyphicon glyphicon-info-sign"></span> ' . JText::_($this->textPrefix . "_INFO") . '</p>';

        if ($this->params->get('paypal_sandbox', 1)) {
            $html[] = '<p class="bg-info p-10-5"><span class="glyphicon glyphicon-info-sign"></span> ' . JText::_($this->textPrefix . "_WORKS_SANDBOX") . '</p>';
        }

        $html[] = '</div>'; // Close "well".

        return implode("\n", $html);
    }

    /**
     * Process payment transaction.
     *
     * @param string                   $context
     * @param object                   $item
     * @param Joomla\Registry\Registry $params
     *
     * @return null|array
     */
    public function onPaymentsCheckout($context, &$item, &$params)
    {
        if (strcmp("com_crowdfunding.payments.checkout.paypal", $context) != 0) {
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

        $output = array();

        $notifyUrl = $this->getCallbackUrl();
        $cancelUrl = $this->getCancelUrl($item->slug, $item->catslug);
        $returnUrl = $this->getReturnUrl($item->slug, $item->catslug);

        // DEBUG DATA
        JDEBUG ? $this->log->add(JText::_($this->textPrefix . "_DEBUG_NOTIFY_URL"), $this->debugType, $notifyUrl) : null;
        JDEBUG ? $this->log->add(JText::_($this->textPrefix . "_DEBUG_RETURN_URL"), $this->debugType, $returnUrl) : null;
        JDEBUG ? $this->log->add(JText::_($this->textPrefix . "_DEBUG_CANCEL_URL"), $this->debugType, $cancelUrl) : null;

        // Get country and locale code.
        $countryId    = $this->params->get("paypal_country");

        $country = new Crowdfunding\Country(JFactory::getDbo());
        $country->load($countryId);

        // Create transport object.
        $options = new Joomla\Registry\Registry;
        /** @var  $options Joomla\Registry\Registry */

        $transport = new JHttpTransportCurl($options);
        $http      = new JHttp($options, $transport);

        // Create payment object.
        $options = new Joomla\Registry\Registry;
        /** @var  $options Joomla\Registry\Registry */

        $options->set("urls.cancel", $cancelUrl);
        $options->set("urls.return", $returnUrl);
        $options->set("urls.notify", $notifyUrl);

        $this->prepareCredentials($options);

        // Get server IP address.
        /*$serverIP = $this->app->input->server->get("SERVER_ADDR");
        $options->set("credentials.ip_address", $serverIP);*/

        // Prepare starting and ending date.
        if (!$this->params->get("paypal_starting_date", 0)) { // End date of the campaign.
            $startingDate = new JDate(); // Today
            $startingDate->setTime(0, 0, 0); // At 00:00:00
        } else {
            $startingDate = new JDate($item->ending_date);
            $startingDate->modify("+1 day");
            $startingDate->setTime(0, 0, 0); // At 00:00:00
        }

        $endingDate   = new JDate($item->ending_date);
        $endingDate->modify("+10 days");

        $options->set("payment.starting_date", $startingDate->format(DATE_ATOM));
        $options->set("payment.ending_date", $endingDate->format(DATE_ATOM));

        $options->set("payment.max_amount", $item->amount);
        $options->set("payment.max_total_amount", $item->amount);
        $options->set("payment.number_of_payments", 1);
        $options->set("payment.currency_code", $item->currencyCode);

        $options->set("payment.fees_payer", $this->params->get("paypal_fees_payer"));
        $options->set("payment.ping_type", "NOT_REQUIRED");

        $title = JText::sprintf($this->textPrefix . "_INVESTING_IN_S", htmlentities($item->title, ENT_QUOTES, "UTF-8"));
        $options->set("payment.memo", $title);

        $options->set("request.envelope", $this->envelope);

        // Get payment session.

        $paymentSessionContext    = Crowdfunding\Constants::PAYMENT_SESSION_CONTEXT . $item->id;
        $paymentSessionLocal      = $this->app->getUserState($paymentSessionContext);

        $paymentSession = $this->getPaymentSession(array(
            "session_id"    => $paymentSessionLocal->session_id
        ));

        // Get API url.
        $apiUrl = $this->getApiUrl();

        // DEBUG DATA
        JDEBUG ? $this->log->add(JText::_($this->textPrefix . "_DEBUG_PAYPAL_ADAPTIVE_OPTIONS"), $this->debugType, $options->toArray()) : null;

        $adaptive = new Prism\Payment\PayPal\Adaptive($apiUrl, $options);
        $adaptive->setTransport($http);

        $response = $adaptive->doPreppproval();

        // DEBUG DATA
        JDEBUG ? $this->log->add(JText::_($this->textPrefix . "_DEBUG_PAYPAL_ADAPTIVE_RESPONSE"), $this->debugType, $response) : null;

        $preapprovalKey = $response->getPreApprovalKey();
        if (!$preapprovalKey) {
            return null;
        }

        // Store token to the payment session.
        $paymentSession->setUniqueKey($preapprovalKey);
        $paymentSession->storeUniqueKey();

        // Get paypal checkout URL.
        if (!$this->params->get('paypal_sandbox', 1)) {
            $output["redirect_url"] = $this->params->get("paypal_url") . "?cmd=_ap-preapproval&preapprovalkey=" . rawurlencode($preapprovalKey);
        } else {
            $output["redirect_url"] = $this->params->get("paypal_sandbox_url") . "?cmd=_ap-preapproval&preapprovalkey=" . rawurlencode($preapprovalKey);
        }

        return $output;
    }

    /**
     * Capture payments.
     *
     * @param string                   $context
     * @param object                   $item
     * @param Joomla\Registry\Registry $params
     *
     * @return array|null
     */
    public function onPaymentsCapture($context, &$item, &$params)
    {
        $allowedContext = array("com_crowdfunding.payments.capture.paypal", "com_crowdfundingfinance.payments.capture.paypal");
        if (!in_array($context, $allowedContext)) {
            return null;
        }

        if (!$this->app->isAdmin()) {
            return null;
        }

        $doc = JFactory::getDocument();
        /**  @var $doc JDocumentHtml */

        // Check document type
        $docType = $doc->getType();
        if (strcmp("html", $docType) != 0) {
            return null;
        }

        // Load project object and set "memo".
        $project = new Crowdfunding\Project(JFactory::getDbo());
        $project->load($item->project_id);

        $fundingType = $project->getFundingType();
        $fees = $this->getFees($fundingType);

        // DEBUG DATA
        JDEBUG ? $this->log->add(JText::_($this->textPrefix . "_DEBUG_FEES"), $this->debugType, $fees) : null;

        // Create transport object.
        $options = new Joomla\Registry\Registry;
        /** @var  $options Joomla\Registry\Registry */

        $transport = new JHttpTransportCurl($options);
        $http      = new JHttp($options, $transport);

        // Create payment object.
        $options = new Joomla\Registry\Registry;
        /** @var  $options Joomla\Registry\Registry */

        $notifyUrl = $this->getCallbackUrl();
        $cancelUrl = $this->getCancelUrl($project->getSlug(), $project->getCatSlug());
        $returnUrl = $this->getReturnUrl($project->getSlug(), $project->getCatSlug());

        $options->set("urls.notify", $notifyUrl);
        $options->set("urls.cancel", $cancelUrl);
        $options->set("urls.return", $returnUrl);

        $this->prepareCredentials($options);

        $options->set("payment.action_type", "PAY");
        $options->set("payment.preapproval_key", $item->txn_id);

        $options->set("payment.fees_payer", $this->params->get("paypal_fees_payer"));
        $options->set("payment.currency_code", $item->txn_currency);

        $options->set("request.envelope", $this->envelope);

        $title = JText::sprintf($this->textPrefix . "_INVESTING_IN_S", htmlentities($project->getTitle(), ENT_QUOTES, "UTF-8"));
        $options->set("payment.memo", $title);
        
        // Get API url.
        $apiUrl = $this->getApiUrl();

        try {

            // Calculate the fee.
            $fee = $this->calculateFee($fundingType, $fees, $item->txn_amount);

            // Get receiver list and set it to service options.
            $receiverList = $this->getReceiverList($item, $fee, $fundingType);
            $options->set("payment.receiver_list", $receiverList);

            // DEBUG DATA
            JDEBUG ? $this->log->add(JText::_($this->textPrefix . "_DEBUG_DOCAPTURE_OPTIONS"), $this->debugType, $options) : null;

            $adaptive = new Prism\Payment\PayPal\Adaptive($apiUrl, $options);
            $adaptive->setTransport($http);

            $response = $adaptive->doCapture();

            // DEBUG DATA
            JDEBUG ? $this->log->add(JText::_($this->textPrefix . "_DEBUG_DOCAPTURE_RESPONSE"), $this->debugType, $response) : null;

            // Include extra data to transaction record.
            if ($response->isSuccess()) {
                $note = JText::_("PLG_CROWDFUNDINGPAYMENT_PAYPALADAPTIVE_RESPONSE_NOTE_CAPTURE_PREAPPROVAL");
                $extraData = $this->prepareExtraData($response, $note);

                $transaction = new Crowdfunding\Transaction(JFactory::getDbo());
                $transaction->load($item->id);

                $transaction->setFee($fee);
                $transaction->addExtraData($extraData);
                $transaction->store();

                // DEBUG DATA
                JDEBUG ? $this->log->add(JText::_($this->textPrefix . "_DEBUG_DOCAPTURE_TRANSACTION"), $this->debugType, $transaction->getProperties()) : null;
            }

        } catch (Exception $e) {

            // DEBUG DATA
            JDEBUG ? $this->log->add(JText::_($this->textPrefix . "_DEBUG_ERROR_DOCAPTURE"), $this->debugType, $e->getMessage()) : null;

            $message = array(
                "text" => JText::sprintf($this->textPrefix . "_CAPTURED_UNSUCCESSFULLY", $item->txn_id),
                "type" => "error"
            );

            return $message;
        }

        $message = array(
            "text" => JText::sprintf($this->textPrefix . "_CAPTURED_SUCCESSFULLY", $item->txn_id),
            "type" => "message"
        );

        return $message;
    }

    /**
     * Void payments.
     *
     * @param string                   $context
     * @param object                   $item
     * @param Joomla\Registry\Registry $params
     *
     * @return array|null
     */
    public function onPaymentsVoid($context, &$item, &$params)
    {
        $allowedContext = array("com_crowdfunding.payments.void.paypal", "com_crowdfundingfinance.payments.void.paypal");
        if (!in_array($context, $allowedContext)) {
            return null;
        }

        if (!$this->app->isAdmin()) {
            return null;
        }

        $doc = JFactory::getDocument();
        /**  @var $doc JDocumentHtml */

        // Check document type
        $docType = $doc->getType();
        if (strcmp("html", $docType) != 0) {
            return null;
        }

        // Create transport object.
        $options = new Joomla\Registry\Registry;
        /** @var  $options Joomla\Registry\Registry */

        $transport = new JHttpTransportCurl($options);
        $http      = new JHttp($options, $transport);

        // Create payment object.
        $options = new Joomla\Registry\Registry;
        /** @var  $options Joomla\Registry\Registry */

        $this->prepareCredentials($options);

        $options->set("payment.preapproval_key", $item->txn_id);
        $options->set("request.envelope", $this->envelope);

        // Get API url.
        $apiUrl = $this->getApiUrl();

        try {

            // DEBUG DATA
            JDEBUG ? $this->log->add(JText::_($this->textPrefix . "_DEBUG_DOVOID_OPTIONS"), $this->debugType, $options) : null;

            $adaptive = new Prism\Payment\PayPal\Adaptive($apiUrl, $options);
            $adaptive->setTransport($http);

            $response = $adaptive->doVoid();

            // DEBUG DATA
            JDEBUG ? $this->log->add(JText::_($this->textPrefix . "_DEBUG_DOVOID_RESPONSE"), $this->debugType, $response) : null;

            // Include extra data to transaction record.
            if ($response->isSuccess()) {
                $note = JText::_("PLG_CROWDFUNDINGPAYMENT_PAYPALADAPTIVE_RESPONSE_NOTE_CANCEL_PREAPPROVAL");
                $extraData = $this->prepareExtraData($response, $note);

                $transaction = new Crowdfunding\Transaction(JFactory::getDbo());
                $transaction->load($item->id);

                $transaction->addExtraData($extraData);
                $transaction->updateExtraData();

                // DEBUG DATA
                JDEBUG ? $this->log->add(JText::_($this->textPrefix . "_DEBUG_DOVOID_TRANSACTION"), $this->debugType, $transaction->getProperties()) : null;
            }

        } catch (Exception $e) {

            // DEBUG DATA
            JDEBUG ? $this->log->add(JText::_($this->textPrefix . "_DEBUG_ERROR_DOVOID"), $this->debugType, $e->getMessage()) : null;

            $message = array(
                "text" => JText::sprintf($this->textPrefix . "_VOID_UNSUCCESSFULLY", $item->txn_id),
                "type" => "error"
            );

            return $message;

        }

        $message = array(
            "text" => JText::sprintf($this->textPrefix . "_VOID_SUCCESSFULLY", $item->txn_id),
            "type" => "message"
        );

        return $message;
    }

    /**
     * This method processes transaction data that comes from PayPal instant notifier.
     *
     * @param string                   $context This string gives information about that where it has been executed the trigger.
     * @param Joomla\Registry\Registry $params  The parameters of the component
     *
     * @return null|array
     */
    public function onPaymentNotify($context, &$params)
    {
        if (strcmp("com_crowdfunding.notify.paypal", $context) != 0) {
            return null;
        }

        if ($this->app->isAdmin()) {
            return null;
        }

        $doc = JFactory::getDocument();
        /**  @var $doc JDocumentRaw */

        // Check document type
        $docType = $doc->getType();
        if (strcmp("raw", $docType) != 0) {
            return null;
        }

        // Validate request method
        $requestMethod = $this->app->input->getMethod();
        if (strcmp("POST", $requestMethod) != 0) {
            $this->log->add(
                JText::_($this->textPrefix . "_ERROR_INVALID_REQUEST_METHOD"),
                $this->debugType,
                JText::sprintf($this->textPrefix . "_ERROR_INVALID_TRANSACTION_REQUEST_METHOD", $requestMethod)
            );

            return null;
        }

        // Get PayPal URL
        if ($this->params->get('paypal_sandbox', 1)) {
            $url = $this->params->get("paypal_sandbox_url", "https://www.sandbox.paypal.com/cgi-bin/webscr");
        } else {
            $url = $this->params->get("paypal_url", "https://www.paypal.com/cgi-bin/webscr");
        }

        $loadCertificate = (bool)$this->params->get("paypal_load_certificate", 0);

        // Prepare the array that will be returned by this method
        $result = array(
            "project"         => null,
            "reward"          => null,
            "transaction"     => null,
            "payment_session" => null,
            "payment_service" => $this->paymentService
        );

        switch ($_POST["transaction_type"]) {

            case "Adaptive Payment PREAPPROVAL":
                $result = $this->processPreApproval($result, $url, $loadCertificate, $params);
                break;

            case "Adaptive Payment PAY":
                $result = $this->processPay($result, $url, $loadCertificate, $params);
                break;

            default:
                $result = null;
                break;
        }

        return $result;
    }

    /**
     * Process preapproval notification data from PayPal.
     *
     * @param array $result
     * @param string $url  The parameters of the component
     * @param bool $loadCertificate
     * @param Joomla\Registry\Registry $params  The parameters of the component
     *
     * @return null|array
     */
    protected function processPreApproval(&$result, $url, $loadCertificate, &$params)
    {
        $paypalIpn       = new Prism\Payment\PayPal\Ipn($url, $_POST);
        $paypalIpn->verify($loadCertificate);

        // DEBUG DATA
        JDEBUG ? $this->log->add(JText::_($this->textPrefix . "_DEBUG_IPN_OBJECT"), $this->debugType, $paypalIpn) : null;

        if ($paypalIpn->isVerified()) {

            // Get currency
            $currencyId = $params->get("project_currency");
            $currency   = Crowdfunding\Currency::getInstance(JFactory::getDbo(), $currencyId, $params);

            $preApprovalKey = Joomla\Utilities\ArrayHelper::getValue($_POST, "preapproval_key");

            // Get payment session data
            $keys = array(
                "unique_key" => $preApprovalKey
            );
            $paymentSession = $this->getPaymentSession($keys);

            // DEBUG DATA
            JDEBUG ? $this->log->add(JText::_($this->textPrefix . "_DEBUG_PAYMENT_SESSION"), $this->debugType, $paymentSession->getProperties()) : null;

            // Validate transaction data
            $validData = $this->validateData($_POST, $currency->getCode(), $paymentSession);
            if (is_null($validData)) {
                return $result;
            }

            // DEBUG DATA
            JDEBUG ? $this->log->add(JText::_($this->textPrefix . "_DEBUG_VALID_DATA"), $this->debugType, $validData) : null;

            // Get project.
            $projectId = Joomla\Utilities\ArrayHelper::getValue($validData, "project_id");
            $project   = Crowdfunding\Project::getInstance(JFactory::getDbo(), $projectId);

            // DEBUG DATA
            JDEBUG ? $this->log->add(JText::_($this->textPrefix . "_DEBUG_PROJECT_OBJECT"), $this->debugType, $project->getProperties()) : null;

            // Check for valid project
            if (!$project->getId()) {

                // Log data in the database
                $this->log->add(
                    JText::_($this->textPrefix . "_ERROR_INVALID_PROJECT"),
                    $this->debugType,
                    $validData
                );

                return $result;
            }

            // Set the receiver of funds
            $validData["receiver_id"] = $project->getUserId();

            // Save transaction data.
            // If it is not completed, return empty results.
            // If it is complete, continue with process transaction data
            $transactionData = $this->storeTransaction($validData, $project, $preApprovalKey);
            if (is_null($transactionData)) {
                return $result;
            }

            // Update the number of distributed reward.
            $rewardId = Joomla\Utilities\ArrayHelper::getValue($transactionData, "reward_id");
            $reward   = null;
            if (!empty($rewardId)) {
                $reward = $this->updateReward($transactionData);

                // Validate the reward.
                if (!$reward) {
                    $transactionData["reward_id"] = 0;
                }
            }


            //  Prepare the data that will be returned

            $result["transaction"] = Joomla\Utilities\ArrayHelper::toObject($transactionData);

            // Generate object of data based on the project properties
            $properties        = $project->getProperties();
            $result["project"] = Joomla\Utilities\ArrayHelper::toObject($properties);

            // Generate object of data based on the reward properties
            if (!empty($reward)) {
                $properties       = $reward->getProperties();
                $result["reward"] = Joomla\Utilities\ArrayHelper::toObject($properties);
            }

            // Generate data object, based on the payment session properties.
            $properties       = $paymentSession->getProperties();
            $result["payment_session"] = Joomla\Utilities\ArrayHelper::toObject($properties);

            // DEBUG DATA
            JDEBUG ? $this->log->add(JText::_($this->textPrefix . "_DEBUG_RESULT_DATA"), $this->debugType, $result) : null;

            // Remove payment session.
            $txnStatus = (isset($result["transaction"]->txn_status)) ? $result["transaction"]->txn_status : null;
            $this->closePaymentSession($paymentSession, $txnStatus);

        } else {

            // Log error
            $this->log->add(
                JText::_($this->textPrefix . "_ERROR_INVALID_TRANSACTION_DATA"),
                $this->debugType,
                array("error message" => $paypalIpn->getError(), "paypalIPN" => $paypalIpn, "_POST" => $_POST)
            );

        }

        return $result;
    }

    /**
    * Process PAY notification data from PayPal.
    * This method updates transaction record.
    *
    * @param array $result
    * @param string $url  The parameters of the component
    * @param bool $loadCertificate
     * @param Joomla\Registry\Registry $params  The parameters of the component
    *
    * @return null|array
    */
    protected function processPay(&$result, $url, $loadCertificate, &$params)
    {
        // Get raw post data and parse it.
        $rowPostString = file_get_contents("php://input");

        $string = new Prism\String($rowPostString);
        $rawPost = $string->parseNameValue();

        // DEBUG DATA
        JDEBUG ? $this->log->add(JText::_($this->textPrefix . "_DEBUG_RESPONSE"), $this->debugType, $_POST) : null;
        JDEBUG ? $this->log->add(JText::_($this->textPrefix . "_DEBUG_RESPONSE_INPUT"), $this->debugType, $rawPost) : null;

        $paypalIpn       = new Prism\Payment\PayPal\Ipn($url, $rawPost);
        $paypalIpn->verify($loadCertificate);

        // DEBUG DATA
        JDEBUG ? $this->log->add(JText::_($this->textPrefix . "_DEBUG_IPN_OBJECT"), $this->debugType, $paypalIpn) : null;


        if ($paypalIpn->isVerified()) {

            // Parse raw post transaction data.
            $rawPostTransaction = $paypalIpn->getTransactionData();
            if (!empty($rawPostTransaction)) {
                $_POST["transaction"] = $this->filterRawPostTransaction($rawPostTransaction);
            }

            JDEBUG ? $this->log->add(JText::_("PLG_CROWDFUNDINGPAYMENT_PAYPALADAPTIVE_DEBUG_FILTERED_RAW_POST"), $this->debugType, $_POST) : null;
            unset($rawPostTransaction);
            unset($rawPost);

            $preApprovalKey = Joomla\Utilities\ArrayHelper::getValue($_POST, "preapproval_key");

            // Validate transaction data
            $this->updateTransactionDataOnPay($_POST, $preApprovalKey);

        } else {

            // Log error
            $this->log->add(
                JText::_($this->textPrefix . "_ERROR_INVALID_TRANSACTION_DATA"),
                $this->debugType,
                array("error message" => $paypalIpn->getError(), "paypalIPN" => $paypalIpn, "_POST" => $_POST, "RAW POST" => $rawPost)
            );

        }

        return $result;
    }

    /**
     * This method is executed after complete payment.
     * It is used to be sent mails to user and administrator
     *
     * @param string                   $context
     * @param object                   $transaction Transaction data
     * @param Joomla\Registry\Registry $params      Component parameters
     * @param object                   $project     Project data
     * @param object                   $reward      Reward data
     *
     * @return void
     */
    public function onAfterPayment($context, &$transaction, &$params, &$project, &$reward)
    {
        if (strcmp("com_crowdfunding.notify.paypal", $context) != 0) {
            return;
        }

        if ($this->app->isAdmin()) {
            return;
        }

        $doc = JFactory::getDocument();
        /**  @var $doc JDocumentRaw */

        // Check document type
        $docType = $doc->getType();
        if (strcmp("raw", $docType) != 0) {
            return;
        }

        // Send mails
        $this->sendMails($project, $transaction, $params);
    }

    /**
     * Validate PayPal transaction
     *
     * @param array  $data
     * @param string $currency
     * @param Crowdfunding\Payment\Session $paymentSession
     *
     * @return array|null
     */
    protected function validateData($data, $currency, $paymentSession)
    {
        $date    = new JDate();

        // Get additional information from transaction.
        $extraData = $this->prepareNotificationExtraData($data, JText::_("PLG_CROWDFUNDINGPAYMENT_PAYPALADAPTIVE_RESPONSE_NOTE_NOTIFICATION"));

        // Prepare transaction data
        $transaction = array(
            "investor_id"      => (int)$paymentSession->getUserId(),
            "project_id"       => (int)$paymentSession->getProjectId(),
            "reward_id"        => ($paymentSession->isAnonymous()) ? 0 : (int)$paymentSession->getRewardId(),
            "service_provider" => "PayPal",
            "txn_id"           => Joomla\Utilities\ArrayHelper::getValue($data, "preapproval_key"),
            "parent_txn_id"    => "",
            "txn_amount"       => Joomla\Utilities\ArrayHelper::getValue($data, "max_total_amount_of_all_payments", 0, "float"),
            "txn_currency"     => Joomla\Utilities\ArrayHelper::getValue($data, "currency_code", "", "string"),
            "txn_status"       => $this->getPaymentStatus($data),
            "txn_date"         => $date->toSql(),
            "status_reason"    => $this->getStatusReason($data),
            "extra_data"       => $extraData
        );

        // Check Project ID and Transaction ID
        if (!$transaction["project_id"] or !$transaction["txn_id"]) {

            // Log data in the database
            $this->log->add(
                JText::_($this->textPrefix . "_ERROR_INVALID_TRANSACTION_DATA"),
                $this->debugType,
                $transaction
            );

            return null;
        }


        // Check currency
        if (strcmp($transaction["txn_currency"], $currency) != 0) {

            // Log data in the database
            $this->log->add(
                JText::_($this->textPrefix . "_ERROR_INVALID_TRANSACTION_CURRENCY"),
                $this->debugType,
                array("TRANSACTION DATA" => $transaction, "CURRENCY" => $currency)
            );

            return null;
        }

        return $transaction;
    }


    /**
     * Update transaction record using a data that comes for PayPal Adaptive PAY notifications.
     *
     * @param array  $data
     * @param string $preApprovalKey
     *
     * @return array|null
     */
    protected function updateTransactionDataOnPay($data, $preApprovalKey)
    {
        // Get transaction by ID
        $keys = array(
            "txn_id" => $preApprovalKey
        );

        $transaction = new Crowdfunding\Transaction(JFactory::getDbo());
        $transaction->load($keys);

        $status = $this->getPaymentStatus($data);

        // Process the status state.
        switch ($status) {
            case "completed":
                if (!$transaction->isCompleted()) {
                    $transaction->setStatus($status);
                    $transaction->setStatusReason("");
                }
                break;
        }

        // Get additional information from transaction.
        $extraData = $this->prepareNotificationExtraData($data, JText::_("PLG_CROWDFUNDINGPAYMENT_PAYPALADAPTIVE_RESPONSE_NOTE_NOTIFICATION"));
        if (!empty($extraData)) {
            $transaction->addExtraData($extraData);
        }

        $transaction->store();
    }

    /**
     * Save transaction data.
     *
     * @param array               $transactionData
     * @param Crowdfunding\Project $project
     * @param string $preApprovalKey
     *
     * @return null|array
     */
    protected function storeTransaction($transactionData, $project, $preApprovalKey)
    {
        // Get transaction by ID
        $transaction = new Crowdfunding\Transaction(JFactory::getDbo());
        $transaction->load(array("txn_id" => $preApprovalKey));

        // DEBUG DATA
        JDEBUG ? $this->log->add(JText::_($this->textPrefix . "_DEBUG_TRANSACTION_OBJECT"), $this->debugType, $transaction->getProperties()) : null;

        // Check for existed transaction record.
        if ($transaction->getId()) { // Update existed transaction record.

            // If the current status is completed,
            // stop the process to prevent overwriting data.
            if ($transaction->isCompleted()) {
                return null;
            }

            $txnStatus = Joomla\Utilities\ArrayHelper::getValue($transactionData, "txn_status");

            switch ($txnStatus) {

                case "completed":
                    $this->processCompleted($transaction, $project, $transactionData);
                    break;

                case "canceled":
                    $this->processVoided($transaction, $project, $transactionData);
                    break;
            }

            return null;

        } else { // Create the new transaction data.

            // Store the transaction data.
            $transaction->bind($transactionData);
            $transaction->store();

            // Add funds to the project.
            if ($transaction->isCompleted() or $transaction->isPending()) {
                $amount = Joomla\Utilities\ArrayHelper::getValue($transactionData, "txn_amount");
                $project->addFunds($amount);
                $project->storeFunds();
            }

            // Set transaction ID.
            $transactionData["id"] = $transaction->getId();

            return $transactionData;
        }

    }

    /**
     * @param Crowdfunding\Transaction $transaction
     * @param Crowdfunding\Project     $project
     * @param array                   $data
     *
     * @return bool
     */
    protected function processCompleted(&$transaction, &$project, &$data)
    {
        // Set a flag that shows the project is NOT funded.
        // If the status had not been completed or pending ( it might be failed, voided, created,...),
        // the project funds had not been increased. So, I will set this flag to false.
        $projectFunded = true;
        if (!$transaction->isCompleted() and !$transaction->isPending()) {
            $projectFunded = false;
        }

        // Merge existed extra data with the new one.
        if (!empty($data["extra_data"])) {
            $transaction->addExtraData($data["extra_data"]);
            unset($data["extra_data"]);
        }

        // Remove the status reason.
        if ($transaction->isCompleted()) {
            $data["pending_reason"] = "";
        }

        // Update the transaction data.
        $transaction->bind($data);
        $transaction->store();

        // If the transaction status has been changed to pending or completed,
        // I have to add funds to the project.
        if (!$projectFunded and ($transaction->isCompleted() or $transaction->isPending())) {
            $amount = Joomla\Utilities\ArrayHelper::getValue($data, "txn_amount");
            $project->addFunds($amount);
            $project->storeFunds();
        }

        return true;
    }

    /**
     * @param Crowdfunding\Transaction $transaction
     * @param Crowdfunding\Project     $project
     * @param array                   $data
     *
     * @return bool
     */
    protected function processVoided(&$transaction, &$project, &$data)
    {
        // It is possible only to void a transaction with status "pending".
        if (!$transaction->isPending()) {
            return;
        }

        // Merge existed extra data with the new one.
        if (!empty($data["extra_data"])) {
            $transaction->addExtraData($data["extra_data"]);
            unset($data["extra_data"]);
        }

        // Remove the status reason.
        $data["status_reason"] = "";

        // Update the transaction data.
        // If the current status is pending and the new status is completed,
        // only store the transaction data, updating the status to completed.
        $transaction->bind($data);
        $transaction->store();

        $amount = Joomla\Utilities\ArrayHelper::getValue($data, "txn_amount");
        $project->removeFunds($amount);
        $project->storeFunds();
    }

    /**
     * Create and return transaction object.
     *
     * @param array $data
     *
     * @return Crowdfunding\Transaction
     */
    protected function getTransaction($data)
    {
        $transactionType = Joomla\Utilities\ArrayHelper::getValue($data, "transaction_type");

        // Prepare keys used for getting transaction from DB.
        if (strcmp($transactionType, "Adaptive Payment PREAPPROVAL") == 0) {
            $keys["txn_id"] = Joomla\Utilities\ArrayHelper::getValue($data, "preapproval_key");
        } elseif (strcmp($transactionType, "Adaptive Payment PAY") == 0) {
            $keys["txn_id"] = Joomla\Utilities\ArrayHelper::getValue($data, "preapproval_key");
        } else {
            $keys = array();
        }

        // DEBUG DATA
        JDEBUG ? $this->log->add(JText::_($this->textPrefix . "_DEBUG_TRANSACTION_KEYS"), $this->debugType, $keys) : null;

        // Get transaction by ID
        $transaction = new Crowdfunding\Transaction(JFactory::getDbo());
        $transaction->load($keys);

        return $transaction;
    }

    protected function prepareLocale(&$html)
    {
        // Get country
        $countryId = $this->params->get("paypal_country");
        $country   = new Crowdfunding\Country(JFactory::getDbo());
        $country->load($countryId);

        $code  = $country->getCode();
        $code4 = $country->getCode4();

        $button    = $this->params->get("paypal_button_type", "btn_buynow_LG");
        $buttonUrl = $this->params->get("paypal_button_url");

        // Generate a button
        if (!$this->params->get("paypal_button_default", 0)) {

            if (!$buttonUrl) {

                if (strcmp("US", $code) == 0) {
                    $html[] = '<input type="image" name="submit" border="0" src="https://www.paypalobjects.com/' . $code4 . '/i/btn/' . $button . '.gif" alt="' . JText::_($this->textPrefix . "_BUTTON_ALT") . '" />';
                } else {
                    $html[] = '<input type="image" name="submit" border="0" src="https://www.paypalobjects.com/' . $code4 . '/' . $code . '/i/btn/' . $button . '.gif" alt="' . JText::_($this->textPrefix . "_BUTTON_ALT") . '" />';
                }

            } else {
                $html[] = '<input type="image" name="submit" border="0" src="' . $buttonUrl . '" alt="' . JText::_($this->textPrefix . "_BUTTON_ALT") . '">';
            }

        } else { // Default button

            $html[] = '<input type="image" name="submit" border="0" src="https://www.paypalobjects.com/en_US/i/btn/' . $button . '.gif" alt="' . JText::_($this->textPrefix . "_BUTTON_ALT") . '" />';

        }

        // Set locale
        $html[] = '<input type="hidden" name="lc" value="' . $code . '" />';
    }

    protected function getStatusReason($data)
    {
        $result = "";

        $transactionType = Joomla\Utilities\ArrayHelper::getValue($data, "transaction_type");

        if (strcmp($transactionType, "Adaptive Payment PREAPPROVAL") == 0) {
            $result = "preapproval";
        }

        return $result;
    }

    protected function getPaymentStatus($data)
    {
        $result = "pending";

        $transactionType = Joomla\Utilities\ArrayHelper::getValue($data, "transaction_type");
        $status = Joomla\Utilities\ArrayHelper::getValue($data, "status");

        if (strcmp($transactionType, "Adaptive Payment PREAPPROVAL") == 0) {

            switch ($status) {
                case "ACTIVE":
                    $approved = Joomla\Utilities\ArrayHelper::getValue($data, "approved", false, "bool");
                    if ($approved) {
                        $result = "pending";
                    }

                    break;

                case "CANCELED":
                    $result = "canceled";
                    break;
            }

        } elseif (strcmp($transactionType, "Adaptive Payment PAY") == 0) {

            switch ($status) {
                case "COMPLETED":
                    $result = "completed";
                    break;
            }
        }

        return $result;
    }

    /**
     * Prepare additional data that will be stored to the transaction record.
     * This data will be used as additional information about curren transaction.
     * It is processed by the event "onPaymentNotify".
     *
     * @param array $data
     * @param string $note
     *
     * @return array
     */
    protected function prepareNotificationExtraData($data, $note = "")
    {
        $date = new JDate();
        $trackingKey = $date->toUnix();

        $extraData = array(
            $trackingKey => array()
        );

        $keys = array(
            "payment_request_date", "action_type", "transaction_type", "sender_email",
            "starting_date", "ending_date", "max_number_of_payments", "max_amount_per_payment",
            "max_total_amount_of_all_payments", "current_total_amount_of_all_payments", "currency_code",
            "transaction", "preapproval_key", "approved", "day_of_week", "status", "current_period_attempts",
            "pay_key", "fees_payer", "pin_type", "payment_period", "notify_version", "charset",
            "log_default_shipping_address_in_transaction", "reverse_all_parallel_payments_on_error",
            "memo",
        );

        foreach ($keys as $key) {
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
     * Prepare an extra data that should be stored to database record.
     *
     * @param Prism\Payment\PayPal\Adaptive\Response $data
     * @param string $note
     *
     * @return array
     */
    protected function prepareExtraData($data, $note = "")
    {
        $date = new JDate();
        $trackingKey = $date->toUnix();

        $extraData = array(
            $trackingKey => array(
                "Acknowledgement Status" => $data->getEnvelopeProperty("ack"),
                "Timestamp" => $data->getEnvelopeProperty("timestamp"),
                "Correlation ID" => $data->getEnvelopeProperty("correlationId"),
                "NOTE" => $note
            )
        );

        return $extraData;
    }

    /**
     * Prepare credentials for sandbox or for the live server.
     *
     * @param Joomla\Registry\Registry $options
     */
    protected function prepareCredentials(&$options)
    {
        if ($this->params->get("paypal_sandbox", 1)) {
            $options->set("credentials.username", Joomla\String\String::trim($this->params->get("paypal_sandbox_api_username")));
            $options->set("credentials.password", Joomla\String\String::trim($this->params->get("paypal_sandbox_api_password")));
            $options->set("credentials.signature", Joomla\String\String::trim($this->params->get("paypal_sandbox_api_signature")));
            $options->set("credentials.app_id", Joomla\String\String::trim($this->params->get("paypal_sandbox_app_id")));
        } else {
            $options->set("credentials.username", Joomla\String\String::trim($this->params->get("paypal_api_username")));
            $options->set("credentials.password", Joomla\String\String::trim($this->params->get("paypal_api_password")));
            $options->set("credentials.signature", Joomla\String\String::trim($this->params->get("paypal_api_signature")));
            $options->set("credentials.app_id", Joomla\String\String::trim($this->params->get("paypal_app_id")));
        }
    }

    /**
     * This method prepares the list with amount receivers.
     *
     * @param object $item
     * @param float $fee
     *
     * @return array
     * @throws RuntimeException
     */
    public function getReceiverList($item, $fee)
    {
        $receiverList = array();

        $siteOwnerAmount = $item->txn_amount;

        // Payment types that must be used with fees.
        $feesPaymentTypes = array("parallel", "chained");

        // Get payment types.
        $paymentType = $this->params->get("paypal_payment_type", "simple");

        // DEBUG DATA
        JDEBUG ? $this->log->add(JText::_($this->textPrefix . "_DEBUG_PAYMENT_TYPE"), $this->debugType, $paymentType) : null;

        // If there is NO fees and it is not SIMPLE payment type,
        // return empty receiver list, because there is no logic to
        // process parallel or chained transaction without amount (a fee) for receiving.
        if (in_array($paymentType, $feesPaymentTypes) and !$fee) {
            throw new RuntimeException(JText::_($this->textPrefix . "_ERROR_FEES_NOT_SET"));
        }

        // If it is parallel or chained payment type,
        // the user must provide us his PayPal account.
        // He must provide us an email using Crowdfunding Finance.
        if (in_array($paymentType, $feesPaymentTypes)) {
            jimport("CrowdfundingFinance.Payout");
            $payout = new CrowdfundingFinance\Payout(JFactory::getDbo());
            $payout->load($item->project_id);

            // DEBUG DATA
            JDEBUG ? $this->log->add(JText::_($this->textPrefix . "_DEBUG_PAYOUT_DATA"), $this->debugType, $payout->getProperties()) : null;

            $receiverEmail = $payout->getPaypalEmail();
            if (!empty($receiverEmail)) {

                switch($paymentType) {

                    case "chained":

                        // Set the amount that the project owner will receive.
                        $projectOwnerAmount = $siteOwnerAmount;

                        // Set the amount that the site owner will receive.
                        $siteOwnerAmount = $fee;

                        // Prepare primary receiver.
                        $receiverList["receiver"][] = array(
                            "email"   => $receiverEmail,
                            "amount"  => round($projectOwnerAmount, 2),
                            "primary" => true
                        );

                        break;

                    case "parallel":

                        // Set the amount that the project owner will receive.
                        $projectOwnerAmount = $siteOwnerAmount - $fee;

                        // Set the amount that the site owner will receive.
                        $siteOwnerAmount = $fee;

                        $receiverList["receiver"][] = array(
                            "email"   => $receiverEmail,
                            "amount"  => round($projectOwnerAmount, 2),
                            "primary" => false
                        );

                        break;

                }

            }

        }

        // If the payment type is parallel or chained,
        // the user must provide himself as receiver.
        // If receiver missing, return an empty array.
        if (in_array($paymentType, $feesPaymentTypes) and empty($receiverList)) {
            throw new RuntimeException(JText::_($this->textPrefix . "_ERROR_INVALID_FIRST_RECEIVER"));
        }

        // If the payment type is parallel or chained,
        // and there is a receiver but there is no fee ( the result of the calculation of fees is 0 ),
        // I will not continue. I will not set the site owner as receiver of fee, because the fee is 0.
        // There is no logic to set more receivers which have to receive amount 0.
        if (in_array($paymentType, $feesPaymentTypes) and !$fee) {
            return $receiverList;
        }

        if ($this->params->get("paypal_sandbox", 1)) { // Simple

            $receiverList["receiver"][] = array(
                "email"   => Joomla\String\String::trim($this->params->get("paypal_sandbox_receiver_email")),
                "amount"  => round($siteOwnerAmount, 2),
                "primary" => false
            );

        } else {

            $receiverList["receiver"][] = array(
                "email"   => Joomla\String\String::trim($this->params->get("paypal_receiver_email")),
                "amount"  => round($siteOwnerAmount, 2),
                "primary" => false
            );

        }

        return $receiverList;
    }

    /**
     * Return PayPal API URL.
     *
     * @return string
     */
    protected function getApiUrl()
    {
        if ($this->params->get("paypal_sandbox", 1)) {
            return Joomla\String\String::trim($this->params->get("paypal_sandbox_api_url"));
        } else {
            return Joomla\String\String::trim($this->params->get("paypal_api_url"));
        }
    }

    /**
     * Filter the raw transaction data.
     *
     * @param array $data
     *
     * @return array
     */
    protected function filterRawPostTransaction($data)
    {
        $filter = JFilterInput::getInstance();

        $result = array();

        foreach ($data as $key => $value) {
            $key = $filter->clean($key);
            if (!is_array($value)) {
                $result[$key] = $filter->clean($value);
            } else {
                foreach ($value as $k => $v) {
                    $value[$k] = $filter->clean($v);
                }

                $result[$key] = $value;
            }
        }

        return $result;
    }
}
