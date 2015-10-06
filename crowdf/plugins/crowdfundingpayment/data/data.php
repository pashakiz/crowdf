<?php
/**
 * @package         CrowdfundingData
 * @subpackage      Plugins
 * @author          Todor Iliev
 * @copyright       Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license         http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// no direct access
defined('_JEXEC') or die;

jimport("CrowdfundingData.init");

/**
 * Crowdfunding Data Plugin
 *
 * @package        CrowdfundingData
 * @subpackage     Plugins
 */
class plgCrowdfundingPaymentData extends Crowdfunding\Payment\Plugin
{
    protected $autoloadLanguage = true;

    /**
     * @var JApplicationSite
     */
    protected $app;

    protected $form;

    protected $name;
    protected $version = "2.0";
    protected $debugType  = "CROWDFUNDINGDATA_DATA_PLUGIN_DEBUG";
    protected $textPrefix = "PLG_CROWDFUNDINGPAYMENT_DATA";

    protected $itemId = 0;
    protected $terms;

    /**
     * @var Joomla\Registry\Registry
     */
    public $params;

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
    public function onPaymentExtras($context, &$item, &$params)
    {
        if (strcmp("com_crowdfunding.payment.step2", $context) != 0) {
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

        // Load language file of the component.
        $language = JFactory::getLanguage();
        $language->load('com_crowdfundingdata', CROWDFUNDINGDATA_PATH_COMPONENT_SITE);

        $componentParams = JComponentHelper::getParams("com_crowdfunding");
        /** @var  $componentParams Joomla\Registry\Registry */

        // Get payment session.
        $paymentSessionContext    = Crowdfunding\Constants::PAYMENT_SESSION_CONTEXT . $item->id;
        $paymentSession           = $this->app->getUserState($paymentSessionContext);

        if (!isset($paymentSession->step1)) {
            $path = JPath::clean(JPluginHelper::getLayoutPath('crowdfundingpayment', 'data', 'error'));

            // Render error layout.
            ob_start();
            include $path;
            return ob_get_clean();
        }

        // Get the value of therms and conditions.
        $this->terms              = $paymentSession->terms;

        // Check for duplication of session ID.
        $this->prepareSessionId($item);

        // Load the form.
        JForm::addFormPath(CROWDFUNDINGDATA_PATH_COMPONENT_SITE . '/models/forms');
        JForm::addFieldPath(CROWDFUNDINGDATA_PATH_COMPONENT_SITE . '/models/fields');

        $form = JForm::getInstance('com_crowdfundingdata.record', 'record', array('control' => "jform", 'load_data' => false));

        // Prepare default name of a user.
        $user    = JFactory::getUser();
        if ($user->get("id")) {
            $form->setValue("name", null, $user->get("name"));
        }

        // Set item id to the form.
        $form->setValue("project_id", null, $item->id);

        $this->form = $form;

        // Load jQuery
        JHtml::_("jquery.framework");

        // Include Chosen
        if ($this->params->get("enable_chosen", 0)) {
            JHtml::_('formbehavior.chosen', '#jform_country_id');
        }

        // Get the path for the layout file
        $path = JPath::clean(JPluginHelper::getLayoutPath('crowdfundingpayment', 'data'));

        // Render the form.
        ob_start();
        include $path;
        $html = ob_get_clean();

        return $html;
    }

    /**
     * Check for duplication of session ID.
     * If the session ID exists, generate new one.
     *
     * @param object $item
     */
    protected function prepareSessionId(&$item)
    {
        // Get the payment session object and session ID.
        $paymentSessionContext    = Crowdfunding\Constants::PAYMENT_SESSION_CONTEXT . $item->id;
        $paymentSession           = $this->app->getUserState($paymentSessionContext);

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query
            ->select("COUNT(*)")
            ->from($db->quoteName("#__cfdata_records", "a"))
            ->where("a.session_id = " . $db->quote($paymentSession->session_id));

        $db->setQuery($query, 0, 1);
        $result = $db->loadResult();

        if (!empty($result)) {

            // Create payment session ID.
            $sessionId = new Prism\String();
            $sessionId->generateRandomString(32);

            $paymentSession->session_id = (string)$sessionId;

            $this->app->setUserState($paymentSessionContext, $paymentSession);

        }
    }

    /**
     * This method is executed after complete payment.
     * It is used to be stored the transaction ID and the investor ID in data record.
     *
     * @param object $context
     * @param object $transaction Transaction data
     * @param Joomla\Registry\Registry $params Component parameters
     * @param object $project Project data
     * @param object $reward Reward data
     * @param object $paymentSession Payment session object.
     *
     * @return void
     */
    public function onAfterPayment($context, &$transaction, &$params, &$project, &$reward, &$paymentSession)
    {
        if (0 !== strpos($context, "com_crowdfunding.notify")) {
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

        // Load record data from database.
        $keys = array(
            "session_id" => $paymentSession->session_id
        );

        $record = new CrowdfundingData\Record(JFactory::getDbo());
        $record->load($keys);

        if (!$record->getId()) {
            return null;
        }

        // Set transaction ID.
        if (!empty($transaction->id)) {
            $record->setTransactionId($transaction->id);
        }

        // Set user ID.
        if (!empty($transaction->investor_id)) {
            $record->setUserId($transaction->investor_id);
        }

        $record->store();
    }
}
