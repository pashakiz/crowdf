<?php
/**
 * @package      CrowdfundingPayment
 * @subpackage   Plugins
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// no direct access
defined('_JEXEC') or die;

/**
 * CrowdfundingPayment - Login Plug-in displays a login form on step 2 of the payment wizard.
 *
 * @package      CrowdfundingPayment
 * @subpackage   Plugins
 */
class plgCrowdfundingPaymentLogin extends JPlugin
{
    protected $autoloadLanguage = true;

    /**
     * @var JApplicationSite
     */
    protected $app;

    protected $loginForm;
    protected $returnUrl;

    protected $rewardId;
    protected $amount;
    protected $terms;

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

        // Get user ID.
        $userId  = JFactory::getUser()->get("id");

        // Display login form
        if (!$userId) {

            // Get the form.
            JForm::addFormPath(JPATH_COMPONENT . '/models/forms');
            JForm::addFieldPath(JPATH_COMPONENT . '/models/fields');

            $form = JForm::getInstance('com_users.login', 'login', array('load_data' => false), false, false);

            $this->loginForm = $form;

            $this->returnUrl = CrowdfundingHelperRoute::getBackingRoute($item->slug, $item->catslug);

            // Get the path for the layout file
            $path = JPluginHelper::getLayoutPath('crowdfundingpayment', 'login');

            // Render the login form.
            ob_start();
            include $path;
            $html = ob_get_clean();

        } else { // Redirect to step "Payment".

            $componentParams = JComponentHelper::getParams("com_crowdfunding");
            /** @var  $componentParams Joomla\Registry\Registry */

            // Get the payment process object and
            // store the selected data from the user.
            $paymentProcessContext    = Crowdfunding\Constants::PAYMENT_SESSION_CONTEXT . $item->id;
            $paymentSession           = $this->app->getUserState($paymentProcessContext);

            $this->rewardId = $paymentSession->rewardId;
            $this->amount   = $paymentSession->amount;
            $this->terms    = $paymentSession->terms;

            // Get the path for the layout file
            $path = JPluginHelper::getLayoutPath('crowdfundingpayment', 'login', 'redirect');

            // Render the login form.
            ob_start();
            include $path;
            $html = ob_get_clean();

            // Include JavaScript code to redirect user to next step.

            $processUrl    = JUri::base()."index.php?option=com_crowdfunding&task=backing.process&id=".(int)$item->id."&rid=".(int)$this->rewardId."&amount=".rawurldecode($this->amount)."&".JSession::getFormToken(). "=1";

            // Set the value of terms of use condition.
            if ($componentParams->get("backing_terms", 0) and !empty($this->terms)) {
                $processUrl .= "&terms=1";
            }

            $filter = JFilterInput::getInstance();
            $processUrl = $filter->clean($processUrl);

            $js = '
jQuery(document).ready(function() {
     window.location.replace("'.$processUrl.'");
});';
            $doc->addScriptDeclaration($js);
        }

        return $html;
    }

    /**
     * This method is used from the system to authorize step 2,
     * when you use a payment wizard in four steps.
     * If this method return true, the system will continue to step 2.
     *
     * @param string    $context This string gives information about that where it has been executed the trigger.
     * @param object $item
     * @param Joomla\Registry\Registry $params
     * @param JUser $user
     *
     * @return bool
     */
    public function onPaymentAuthorize($context, &$item, &$params, &$user)
    {
        if (strcmp("com_crowdfunding.payment.authorize", $context) != 0) {
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

        return true;
    }
}
