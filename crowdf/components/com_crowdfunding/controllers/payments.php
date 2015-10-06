<?php
/**
 * @package      Crowdfunding
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// no direct access
defined('_JEXEC') or die;

/**
 * This controller provides functionality
 * that helps to payment plugins to prepare their payment data.
 *
 * @package        Crowdfunding
 * @subpackage     Payments
 *
 */
class CrowdfundingControllerPayments extends JControllerLegacy
{
    protected $log;

    protected $paymentProcessContext;
    protected $paymentProcess;

    protected $projectId;

    public function __construct($config = array())
    {
        parent::__construct($config);

        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        // Get project id.
        $this->projectId = $this->input->getUint("pid");

        // Prepare log object
        $registry = JRegistry::getInstance("com_crowdfunding");
        /** @var  $registry Joomla\Registry\Registry */

        $fileName  = $registry->get("logger.file");
        $tableName = $registry->get("logger.table");

        $file = JPath::clean(JFactory::getApplication()->get("log_path") . DIRECTORY_SEPARATOR . $fileName);

        $this->log = new Prism\Log\Log();
        $this->log->addWriter(new Prism\Log\Writer\Database(JFactory::getDbo(), $tableName));
        $this->log->addWriter(new Prism\Log\Writer\File($file));

        // Create an object that contains a data used during the payment process.
        $this->paymentProcessContext = Crowdfunding\Constants::PAYMENT_SESSION_CONTEXT . $this->projectId;
        $this->paymentProcess        = $app->getUserState($this->paymentProcessContext);

    }

    /**
     * Method to get a model object, loading it if required.
     *
     * @param    string $name   The model name. Optional.
     * @param    string $prefix The class prefix. Optional.
     * @param    array  $config Configuration array for model. Optional.
     *
     * @return    CrowdfundingModelPayments    The model.
     * @since    1.5
     */
    public function getModel($name = 'Payments', $prefix = 'CrowdfundingModel', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);
        return $model;
    }

    /**
     * Invoke the plugin method onPaymentsCheckout.
     *
     * @throws UnexpectedValueException
     * @throws Exception
     */
    public function checkout()
    {
        // Check for request forgeries.
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        // Get component parameters
        $params = JComponentHelper::getParams("com_crowdfunding");
        /** @var  $params Joomla\Registry\Registry */

        // Check for disabled payment functionality
        if ($params->get("debug_payment_disabled", 0)) {
            throw new Exception(JText::_("COM_CROWDFUNDING_ERROR_PAYMENT_HAS_BEEN_DISABLED_MESSAGE"));
        }

        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        $output = array();

        // Get payment gateway name.
        $paymentService = $this->input->get("payment_service");
        if (!$paymentService) {
            throw new UnexpectedValueException(JText::_("COM_CROWDFUNDING_ERROR_INVALID_PAYMENT_GATEWAY"));
        }

        // Set the name of the payment service to session.
        $this->paymentProcess->paymentService = $paymentService;

        // Trigger the event
        try {

            // Prepare project object.
            $model   = $this->getModel();
            $item    = $model->prepareItem($this->projectId, $params, $this->paymentProcess);

            $context = 'com_crowdfunding.payments.checkout.' . Joomla\String\String::strtolower($paymentService);

            // Import Crowdfunding Payment Plugins
            $dispatcher = JEventDispatcher::getInstance();
            JPluginHelper::importPlugin('crowdfundingpayment');

            // Trigger onContentPreparePayment event.
            $results = $dispatcher->trigger("onPaymentsCheckout", array($context, &$item, &$params));

            // Get the result, that comes from the plugin.
            if (!empty($results)) {
                foreach ($results as $result) {
                    if (!is_null($result) and is_array($result)) {
                        $output = & $result;
                        break;
                    }
                }
            }

        } catch (UnexpectedValueException $e) {

            $this->setMessage($e->getMessage(), "notice");
            $this->setRedirect(JRoute::_(CrowdfundingHelperRoute::getDiscoverRoute(), false));

            return;

        } catch (Exception $e) {

            // Store log data in the database
            $this->log->add(
                JText::_("COM_CROWDFUNDING_ERROR_SYSTEM"),
                "CONTROLLER_PAYMENTS_CHECKOUT_ERROR",
                $e->getMessage()
            );

            throw new Exception(JText::_("COM_CROWDFUNDING_ERROR_SYSTEM"));

        }

        $redirectUrl = Joomla\Utilities\ArrayHelper::getValue($output, "redirect_url");
        $message     = Joomla\Utilities\ArrayHelper::getValue($output, "message");
        if (!$redirectUrl) {
            throw new UnexpectedValueException(JText::_("COM_CROWDFUNDING_ERROR_INVALID_REDIRECT_URL"));
        }

        // Store the payment process data into the session.
        $app->setUserState($this->paymentProcessContext, $this->paymentProcess);

        if (!$message) {
            $this->setRedirect($redirectUrl);
        } else {
            $this->setRedirect($redirectUrl, $message, "notice");
        }

    }

    public function docheckout()
    {
        // Get component parameters
        $params = JComponentHelper::getParams("com_crowdfunding");
        /** @var  $params Joomla\Registry\Registry */

        // Check for disabled payment functionality
        if ($params->get("debug_payment_disabled", 0)) {
            throw new Exception(JText::_("COM_CROWDFUNDING_ERROR_PAYMENT_HAS_BEEN_DISABLED_MESSAGE"));
        }

        $output = array();

        // Get the name of the payment service.
        $paymentService = $this->paymentProcess->paymentService;

        // Trigger the event
        try {

            // Prepare project object.
            $model   = $this->getModel();
            $item    = $model->prepareItem($this->projectId, $params, $this->paymentProcess);

            $context = 'com_crowdfunding.payments.docheckout.' . Joomla\String\String::strtolower($paymentService);

            // Import Crowdfunding Payment Plugins
            $dispatcher = JEventDispatcher::getInstance();
            JPluginHelper::importPlugin('crowdfundingpayment');

            // Trigger onContentPreparePayment event.
            $results = $dispatcher->trigger("onPaymentsDoCheckout", array($context, &$item, &$params));

            // Get the result, that comes from the plugin.
            if (!empty($results)) {
                foreach ($results as $result) {
                    if (!is_null($result) and is_array($result)) {
                        $output = & $result;
                        break;
                    }
                }
            }

        } catch (UnexpectedValueException $e) {

            $this->setMessage($e->getMessage(), "notice");
            $this->setRedirect(JRoute::_(CrowdfundingHelperRoute::getDiscoverRoute(), false));
            return;

        } catch (Exception $e) {

            // Store log data in the database
            $this->log->add(
                JText::_("COM_CROWDFUNDING_ERROR_SYSTEM"),
                "CONTROLLER_PAYMENTS_DOCHECKOUT_ERROR",
                $e->getMessage()
            );

            throw new Exception(JText::_("COM_CROWDFUNDING_ERROR_SYSTEM"));
        }

        $redirectUrl = Joomla\Utilities\ArrayHelper::getValue($output, "redirect_url");
        if (!$redirectUrl) {
            throw new UnexpectedValueException(JText::_("COM_CROWDFUNDING_ERROR_INVALID_REDIRECT_URL"));
        }

        $this->setRedirect($redirectUrl);
    }

    /**
     * Invoke the plugin method onPaymentsCompleteCheckout.
     *
     * @throws UnexpectedValueException
     * @throws Exception
     */
    public function completeCheckout()
    {
        // Get component parameters
        $params = JComponentHelper::getParams("com_crowdfunding");
        /** @var  $params Joomla\Registry\Registry */

        // Check for disabled payment functionality
        if ($params->get("debug_payment_disabled", 0)) {
            throw new Exception(JText::_("COM_CROWDFUNDING_ERROR_PAYMENT_HAS_BEEN_DISABLED_MESSAGE"));
        }

        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        $output = array();

        // Get payment gateway name.
        $paymentService = $this->input->get("payment_service");
        if (!$paymentService) {
            throw new UnexpectedValueException(JText::_("COM_CROWDFUNDING_ERROR_INVALID_PAYMENT_GATEWAY"));
        }

        // Set the name of the payment service to session.
        $this->paymentProcess->paymentService = $paymentService;

        // Trigger the event
        try {

            $context = 'com_crowdfunding.payments.completeCheckout.' . Joomla\String\String::strtolower($paymentService);

            // Import Crowdfunding Payment Plugins
            $dispatcher = JEventDispatcher::getInstance();
            JPluginHelper::importPlugin('crowdfundingpayment');

            // Trigger onContentPreparePayment event.
            $results = $dispatcher->trigger("onPaymentsCompleteCheckout", array($context, &$params));

            // Get the result, that comes from the plugin.
            if (!empty($results)) {
                foreach ($results as $result) {
                    if (!is_null($result) and is_array($result)) {
                        $output = & $result;
                        break;
                    }
                }
            }

        } catch (UnexpectedValueException $e) {

            $this->setMessage($e->getMessage(), "notice");
            $this->setRedirect(JRoute::_(CrowdfundingHelperRoute::getDiscoverRoute(), false));

            return;

        } catch (Exception $e) {

            // Store log data in the database
            $this->log->add(
                JText::_("COM_CROWDFUNDING_ERROR_SYSTEM"),
                "CONTROLLER_PAYMENTS_CHECKOUT_ERROR",
                $e->getMessage()
            );

            throw new Exception(JText::_("COM_CROWDFUNDING_ERROR_SYSTEM"));

        }

        $redirectUrl = Joomla\Utilities\ArrayHelper::getValue($output, "redirect_url");
        $message     = Joomla\Utilities\ArrayHelper::getValue($output, "message");
        if (!$redirectUrl) {
            throw new UnexpectedValueException(JText::_("COM_CROWDFUNDING_ERROR_INVALID_REDIRECT_URL"));
        }

        // Store the payment process data into the session.
        $app->setUserState($this->paymentProcessContext, $this->paymentProcess);

        if (!$message) {
            $this->setRedirect($redirectUrl);
        } else {
            $this->setRedirect($redirectUrl, $message, "notice");
        }

    }
}
