<?php
/**
 * @package      CrowdfundingPayment
 * @subpackage   Plugins
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

/**
 * CrowdfundingPayment - Fraud Prevention validates some states during payment process.
 *
 * @package      CrowdfundingPayment
 * @subpackage   Plugins
 */
class plgCrowdfundingPaymentFraudPrevention extends JPlugin
{
    protected $autoloadLanguage = true;

    /**
     * @var JApplicationSite
     */
    protected $app;

    /**
     * This method does some validations before the system to provide payment methods.
     *
     * @param string    $context This string gives information about that where it has been executed the trigger.
     * @param object    $item    A project data.
     * @param Crowdfunding\Amount    $amount  An amount object.
     * @param Joomla\Registry\Registry $params  The parameters of the component
     *
     * @return null|string
     */
    public function onBeforePaymentAuthorize($context, &$item, &$amount, &$params)
    {
        if (strcmp("com_crowdfunding.before.payment.authorize", $context) != 0) {
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

        $html = array();

        // Display login form
        if (!$userId) {
            $html[] = '<p class="bg-warning p-5">';
            $html[] = '<span class="glyphicon glyphicon-warning-sign"></span>';
            $html[] = JText::_("PLG_CROWDFUNDINGPAYMENT_FRAUD_PREVENTION_ERROR_NOT_REGISTERED");
            $html[] = '</p>';
        }

        // Verifications

        // Get component parameters
        $componentParams = JComponentHelper::getParams("com_crowdfundingfinance");
        /** @var  $componentParams Joomla\Registry\Registry */

        // Verify maximum allowed amount for contribution.

        $allowedContributedAmount = $amount->setValue($componentParams->get("protection_max_contributed_amount"))->parse();

        // Validate maximum allowed amount.
        if ($allowedContributedAmount and ($allowedContributedAmount < (float)$item->amount)) {
            $html[] = '<p class="bg-warning p-5">';
            $html[] = '<span class="glyphicon glyphicon-warning-sign"></span>';
            $html[] = JText::sprintf("PLG_CROWDFUNDINGPAYMENT_FRAUD_PREVENTION_ERROR_CONTRIBUTION_AMOUNT_S", $amount->setValue($allowedContributedAmount)->formatCurrency());
            $html[] = '</p>';
        }

        // Verify the number of payments per project.

        $allowedPaymentsPerProject = (int)$componentParams->get("protection_payments_per_project");
        if (!empty($allowedPaymentsPerProject)) {
            $userStatistics = new Crowdfunding\Statistics\User(JFactory::getDbo(), $userId);

            $paymentsPerProject = (int)$userStatistics->getNumberOfPayments($item->id);

            // Validate number of payments per project.
            if ($paymentsPerProject >= $allowedPaymentsPerProject) {
                $html[] = '<p class="bg-warning p-5">';
                $html[] = '<span class="glyphicon glyphicon-warning-sign"></span>';
                $html[] = JText::sprintf("PLG_CROWDFUNDINGPAYMENT_FRAUD_PREVENTION_ERROR_PAYMENT_PER_PROJECT_D", $allowedPaymentsPerProject);
                $html[] = '</p>';
            }

        }

        return (!empty($html)) ? implode("\n", $html) : null;
    }
}
