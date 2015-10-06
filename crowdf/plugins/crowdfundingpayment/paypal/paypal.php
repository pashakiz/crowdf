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
 * Crowdfunding PayPal payment plugin.
 *
 * @package      Crowdfunding
 * @subpackage   Plugins
 */
class plgCrowdfundingPaymentPayPal extends Crowdfunding\Payment\Plugin
{
    protected $paymentService = "paypal";

    protected $textPrefix     = "PLG_CROWDFUNDINGPAYMENT_PAYPAL";
    protected $debugType      = "PAYPAL_PAYMENT_PLUGIN_DEBUG";

    /**
     * @var JApplicationSite
     */
    protected $app;

    protected $extraDataKeys = array(
        "first_name", "last_name", "payer_id", "payer_status",
        "mc_gross", "mc_fee", "mc_currency", "payment_status", "payment_type", "payment_date",
        "txn_type", "test_ipn", "ipn_track_id", "custom", "protection_eligibility"
    );

    /**
     * This method prepares a payment gateway - buttons, forms,...
     * That gateway will be displayed on the summary page as a payment option.
     *
     * @param string    $context This string gives information about that where it has been executed the trigger.
     * @param object    $item    A project data.
     * @param Joomla\Registry\Registry $params  The parameters of the component
     *
     * @return string
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
        $pluginURI = "plugins/crowdfundingpayment/paypal";

        $notifyUrl = $this->getCallbackUrl();
        $returnUrl = $this->getReturnUrl($item->slug, $item->catslug);
        $cancelUrl = $this->getCancelUrl($item->slug, $item->catslug);

        // DEBUG DATA
        JDEBUG ? $this->log->add(JText::_($this->textPrefix . "_DEBUG_NOTIFY_URL"), $this->debugType, $notifyUrl) : null;
        JDEBUG ? $this->log->add(JText::_($this->textPrefix . "_DEBUG_RETURN_URL"), $this->debugType, $returnUrl) : null;
        JDEBUG ? $this->log->add(JText::_($this->textPrefix . "_DEBUG_CANCEL_URL"), $this->debugType, $cancelUrl) : null;

        $html   = array();
        $html[] = '<div class="well">';

        $html[] = '<h4><img src="' . $pluginURI . '/images/paypal_icon.png" width="36" height="32" alt="PayPal" />' . JText::_($this->textPrefix . "_TITLE") . '</h4>';

        // Prepare payment receiver.
        $paymentReceiverOption = $this->params->get("paypal_payment_receiver", "site_owner");
        $paymentReceiverInput = $this->preparePaymentReceiver($paymentReceiverOption, $item->id);
        if (is_null($paymentReceiverInput)) {
            $html[] = $this->generateSystemMessage(JText::_($this->textPrefix . "_ERROR_PAYMENT_RECEIVER_MISSING"));
            return implode("\n", $html);
        }

        // Display additional information.
        $html[] = '<p>' . JText::_($this->textPrefix . "_INFO") . '</p>';

        // Start the form.
        if ($this->params->get('paypal_sandbox', 1)) {
            $html[] = '<form action="' . Joomla\String\String::trim($this->params->get('paypal_sandbox_url')) . '" method="post">';
        } else {
            $html[] = '<form action="' . Joomla\String\String::trim($this->params->get('paypal_url')) . '" method="post">';
        }

        $html[] = $paymentReceiverInput;

        $html[] = '<input type="hidden" name="cmd" value="_xclick" />';
        $html[] = '<input type="hidden" name="charset" value="utf-8" />';
        $html[] = '<input type="hidden" name="currency_code" value="' . $item->currencyCode . '" />';
        $html[] = '<input type="hidden" name="amount" value="' . $item->amount . '" />';
        $html[] = '<input type="hidden" name="quantity" value="1" />';
        $html[] = '<input type="hidden" name="no_shipping" value="1" />';
        $html[] = '<input type="hidden" name="no_note" value="1" />';
        $html[] = '<input type="hidden" name="tax" value="0" />';

        // Title
        $title  = JText::sprintf($this->textPrefix . "_INVESTING_IN_S", htmlentities($item->title, ENT_QUOTES, "UTF-8"));
        $html[] = '<input type="hidden" name="item_name" value="' . $title . '" />';

        // Get payment session

        $paymentSessionContext    = Crowdfunding\Constants::PAYMENT_SESSION_CONTEXT . $item->id;
        $paymentSessionLocal      = $this->app->getUserState($paymentSessionContext);

        $paymentSession = $this->getPaymentSession(array(
            "session_id"    => $paymentSessionLocal->session_id
        ));

        // Prepare custom data
        $custom = array(
            "payment_session_id" => $paymentSession->getId(),
            "gateway"            => "PayPal"
        );

        $custom = base64_encode(json_encode($custom));
        $html[] = '<input type="hidden" name="custom" value="' . $custom . '" />';

        // Set a link to logo
        $imageUrl = Joomla\String\String::trim($this->params->get('paypal_image_url'));
        if ($imageUrl) {
            $html[] = '<input type="hidden" name="image_url" value="' . $imageUrl . '" />';
        }

        // Set URLs
        $html[] = '<input type="hidden" name="cancel_return" value="' . $cancelUrl . '" />';
        $html[] = '<input type="hidden" name="return" value="' . $returnUrl . '" />';
        $html[] = '<input type="hidden" name="notify_url" value="' . $notifyUrl . '" />';

        $this->prepareLocale($html);

        // End the form.
        $html[] = '<img alt="" border="0" width="1" height="1" src="https://www.paypal.com/en_US/i/scr/pixel.gif" >';
        $html[] = '</form>';

        // Display a sticky note if the extension works in sandbox mode.
        if ($this->params->get('paypal_sandbox', 1)) {
            $html[] = '<div class="bg-info p-10-5"><span class="glyphicon glyphicon-info-sign"></span> ' . JText::_($this->textPrefix . "_WORKS_SANDBOX") . '</div>';
        }

        $html[] = '</div>';

        return implode("\n", $html);
    }

    /**
     * This method processes transaction data that comes from PayPal instant notifier.
     *
     * @param string    $context This string gives information about that where it has been executed the trigger.
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
        /**  @var $doc JDocumentHtml */

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

        // DEBUG DATA
        JDEBUG ? $this->log->add(JText::_($this->textPrefix . "_DEBUG_RESPONSE"), $this->debugType, $_POST) : null;

        // Decode custom data
        $custom = Joomla\Utilities\ArrayHelper::getValue($_POST, "custom");
        $custom = json_decode(base64_decode($custom), true);

        // DEBUG DATA
        JDEBUG ? $this->log->add(JText::_($this->textPrefix . "_DEBUG_CUSTOM"), $this->debugType, $custom) : null;

        // Verify gateway. Is it PayPal?
        if (!$this->isPayPalGateway($custom)) {
            $this->log->add(
                JText::_($this->textPrefix . "_ERROR_INVALID_PAYMENT_GATEWAY"),
                $this->debugType,
                array("custom" => $custom, "_POST" => $_POST)
            );

            return null;
        }

        // Get PayPal URL
        if ($this->params->get('paypal_sandbox', 1)) {
            $url = Joomla\String\String::trim($this->params->get('paypal_sandbox_url', "https://www.sandbox.paypal.com/cgi-bin/webscr"));
        } else {
            $url = Joomla\String\String::trim($this->params->get('paypal_url', "https://www.paypal.com/cgi-bin/webscr"));
        }

        $paypalIpn       = new Prism\Payment\PayPal\Ipn($url, $_POST);
        $loadCertificate = (bool)$this->params->get("paypal_load_certificate", 0);
        $paypalIpn->verify($loadCertificate);

        // DEBUG DATA
        JDEBUG ? $this->log->add(JText::_($this->textPrefix . "_DEBUG_VERIFY_OBJECT"), $this->debugType, $paypalIpn) : null;

        // Prepare the array that have to be returned by this method.
        $result = array(
            "project"         => null,
            "reward"          => null,
            "transaction"     => null,
            "payment_session" => null,
            "payment_service" => $this->paymentService
        );

        if ($paypalIpn->isVerified()) {

            // Get currency
            $currency   = Crowdfunding\Currency::getInstance(JFactory::getDbo(), $params->get("project_currency"));

            // Get payment session data
            $paymentSessionId = Joomla\Utilities\ArrayHelper::getValue($custom, "payment_session_id", 0, "int");
            $paymentSession = $this->getPaymentSession(array("id" => $paymentSessionId));

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

            // Set the receiver of funds.
            $validData["receiver_id"] = $project->getUserId();

            // Save transaction data.
            // If it is not completed, return empty results.
            // If it is complete, continue with process transaction data
            $transactionData = $this->storeTransaction($validData, $project);
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

            // Generate object of data, based on the transaction properties.
            $result["transaction"] = Joomla\Utilities\ArrayHelper::toObject($transactionData);

            // Generate object of data based on the project properties.
            $properties        = $project->getProperties();
            $result["project"] = Joomla\Utilities\ArrayHelper::toObject($properties);

            // Generate object of data based on the reward properties.
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
                array("error message" => $paypalIpn->getError(), "paypalVerify" => $paypalIpn, "_POST" => $_POST)
            );

        }

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
        if (strcmp("com_crowdfunding.notify.paypal", $context) != 0) {
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

    /**
     * Validate PayPal transaction.
     *
     * @param array  $data
     * @param string $currency
     * @param Crowdfunding\Payment\Session  $paymentSession
     *
     * @return array
     */
    protected function validateData($data, $currency, $paymentSession)
    {
        $txnDate = Joomla\Utilities\ArrayHelper::getValue($data, "payment_date");
        $date    = new JDate($txnDate);

        // Prepare transaction data
        $transaction = array(
            "investor_id"      => (int)$paymentSession->getUserId(),
            "project_id"       => (int)$paymentSession->getProjectId(),
            "reward_id"        => ($paymentSession->isAnonymous()) ? 0 : (int)$paymentSession->getRewardId(),
            "service_provider" => "PayPal",
            "txn_id"           => Joomla\Utilities\ArrayHelper::getValue($data, "txn_id", null, "string"),
            "txn_amount"       => Joomla\Utilities\ArrayHelper::getValue($data, "mc_gross", null, "float"),
            "txn_currency"     => Joomla\Utilities\ArrayHelper::getValue($data, "mc_currency", null, "string"),
            "txn_status"       => Joomla\String\String::strtolower(Joomla\Utilities\ArrayHelper::getValue($data, "payment_status", null, "string")),
            "txn_date"         => $date->toSql(),
            "extra_data"       => $this->prepareExtraData($data)
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


        // Check payment receiver.
        $allowedReceivers = array(
            Joomla\String\String::strtolower(Joomla\Utilities\ArrayHelper::getValue($data, "business")),
            Joomla\String\String::strtolower(Joomla\Utilities\ArrayHelper::getValue($data, "receiver_email")),
            Joomla\String\String::strtolower(Joomla\Utilities\ArrayHelper::getValue($data, "receiver_id"))
        );

        // Get payment receiver.
        $paymentReceiverOption = $this->params->get("paypal_payment_receiver", "site_owner");
        $paymentReceiver       = $this->getPaymentReceiver($paymentReceiverOption, $transaction["project_id"]);

        if (!in_array($paymentReceiver, $allowedReceivers)) {
            // Log data in the database
            $this->log->add(
                JText::_($this->textPrefix . "_ERROR_INVALID_RECEIVER"),
                $this->debugType,
                array("TRANSACTION DATA" => $transaction, "RECEIVER" => $paymentReceiver, "RECEIVER DATA" => $allowedReceivers)
            );

            return null;
        }

        return $transaction;
    }

    /**
     * Save transaction data.
     *
     * @param array     $transactionData
     * @param object    $project
     *
     * @return null|array
     */
    protected function storeTransaction($transactionData, $project)
    {
        // Get transaction by txn ID
        $keys        = array(
            "txn_id" => Joomla\Utilities\ArrayHelper::getValue($transactionData, "txn_id")
        );
        $transaction = new Crowdfunding\Transaction(JFactory::getDbo());
        $transaction->load($keys);

        // DEBUG DATA
        JDEBUG ? $this->log->add(JText::_($this->textPrefix . "_DEBUG_TRANSACTION_OBJECT"), $this->debugType, $transaction->getProperties()) : null;

        // Check for existed transaction
        if ($transaction->getId()) {

            // If the current status if completed,
            // stop the payment process.
            if ($transaction->isCompleted()) {
                return null;
            }

        }

        // Add extra data.
        if (isset($transactionData["extra_data"])) {
            if (!empty($transactionData["extra_data"])) {
                $transaction->addExtraData($transactionData["extra_data"]);
            }

            unset($transactionData["extra_data"]);
        }

        // Store the new transaction data.
        $transaction->bind($transactionData);
        $transaction->store();

        // If it is not completed (it might be pending or other status),
        // stop the process. Only completed transaction will continue
        // and will process the project, rewards,...
        if (!$transaction->isCompleted()) {
            return null;
        }

        // Set transaction ID.
        $transactionData["id"] = $transaction->getId();

        // If the new transaction is completed,
        // update project funded amount.
        $amount = Joomla\Utilities\ArrayHelper::getValue($transactionData, "txn_amount");
        $project->addFunds($amount);
        $project->storeFunds();

        return $transactionData;
    }

    protected function isPayPalGateway($custom)
    {
        $paymentGateway = Joomla\Utilities\ArrayHelper::getValue($custom, "gateway");

        if (strcmp("PayPal", $paymentGateway) != 0) {
            return false;
        }

        return true;
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
                    $html[] = '<input type="image" name="submit" border="0" src="https://www.paypalobjects.com/' . $code4 . '/i/btn/' . $button . '.gif" alt="' . JText::_($this->textPrefix . "_BUTTON_ALT") . '">';
                } else {
                    $html[] = '<input type="image" name="submit" border="0" src="https://www.paypalobjects.com/' . $code4 . '/' . $code . '/i/btn/' . $button . '.gif" alt="' . JText::_($this->textPrefix . "_BUTTON_ALT") . '">';
                }

            } else {
                $html[] = '<input type="image" name="submit" border="0" src="' . $buttonUrl . '" alt="' . JText::_($this->textPrefix . "_BUTTON_ALT") . '">';
            }

        } else { // Default button

            $html[] = '<input type="image" name="submit" border="0" src="https://www.paypalobjects.com/en_US/i/btn/' . $button . '.gif" alt="' . JText::_($this->textPrefix . "_BUTTON_ALT") . '">';

        }

        // Set locale
        $html[] = '<input type="hidden" name="lc" value="' . $code . '" />';
    }

    /**
     * Prepare a form element of payment receiver.
     *
     * @param $paymentReceiverOption
     * @param $itemId
     *
     * @return null|string
     */
    protected function preparePaymentReceiver($paymentReceiverOption, $itemId)
    {
        if ($this->params->get('paypal_sandbox', 1)) {
            return '<input type="hidden" name="business" value="' . Joomla\String\String::trim($this->params->get('paypal_sandbox_business_name')) . '" />';
        } else {

            if (strcmp("site_owner", $paymentReceiverOption) == 0) { // Site owner
                return '<input type="hidden" name="business" value="' . Joomla\String\String::trim($this->params->get('paypal_business_name')) . '" />';
            } else {

                if (!JComponentHelper::isEnabled("com_crowdfundingfinance")) {
                    return null;
                } else {

                    $payout = new CrowdfundingFinance\Payout(JFactory::getDbo());
                    $payout->load($itemId);

                    if (!$payout->getPaypalEmail()) {
                        return null;
                    }

                    return '<input type="hidden" name="business" value="' . Joomla\String\String::trim($payout->getPaypalEmail()) . '" />';

                }

            }

        }

    }

    /**
     * Return payment receiver.
     *
     * @param $paymentReceiverOption
     * @param $itemId
     *
     * @return null|string
     */
    protected function getPaymentReceiver($paymentReceiverOption, $itemId)
    {
        if ($this->params->get('paypal_sandbox', 1)) {
            return Joomla\String\String::strtolower(Joomla\String\String::trim($this->params->get('paypal_sandbox_business_name')));
        } else {

            if (strcmp("site_owner", $paymentReceiverOption) == 0) { // Site owner
                return Joomla\String\String::strtolower(Joomla\String\String::trim($this->params->get('paypal_business_name')));
            } else {

                if (!JComponentHelper::isEnabled("com_crowdfundingfinance")) {
                    return null;
                } else {
                    
                    $payout = new CrowdfundingFinance\Payout(JFactory::getDbo());
                    $payout->load($itemId);

                    if (!$payout->getPaypalEmail()) {
                        return null;
                    }

                    return Joomla\String\String::strtolower(Joomla\String\String::trim($payout->getPaypalEmail()));
                }

            }

        }

    }
}
