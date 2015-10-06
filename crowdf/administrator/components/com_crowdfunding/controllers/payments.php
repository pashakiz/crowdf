<?php
/**
 * @package      Crowdfunding
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2013 Todor Iliev <todor@itprism.com>. All rights reserved.
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
 */
class CrowdfundingControllerPayments extends JControllerLegacy
{
    protected $option;
    protected $log;

    protected $paymentProcessContext;
    protected $paymentProcess;

    protected $projectId;

    protected $text_prefix = "COM_Crowdfunding";

    public function __construct($config = array())
    {
        parent::__construct($config);

        $this->option = $this->input->getCmd("option");

        // Prepare log object
        $registry = JRegistry::getInstance("com_crowdfunding");
        /** @var $registry Joomla\Registry\Registry */

        $fileName  = $registry->get("logger.file");
        $tableName = $registry->get("logger.table");

        $file = JPath::clean(JFactory::getApplication()->get("log_path") . DIRECTORY_SEPARATOR . $fileName);

        $this->log = new Prism\Log\Log();
        $this->log->addWriter(new Prism\Log\Writer\Database(JFactory::getDbo(), $tableName));
        $this->log->addWriter(new Prism\Log\Writer\File($file));

    }

    /**
     * Method to get a model object, loading it if required.
     *
     * @param    string $name   The model name. Optional.
     * @param    string $prefix The class prefix. Optional.
     * @param    array  $config Configuration array for model. Optional.
     *
     * @return    object    The model.
     * @since    1.5
     */
    public function getModel($name = 'Payments', $prefix = '', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);

        return $model;
    }

    public function docapture()
    {
        // Get component parameters
        $params = JComponentHelper::getParams("com_crowdfunding");
        /** @var $params Joomla\Registry\Registry */

        // Check for disabled payment functionality
        if ($params->get("debug_payment_disabled", 0)) {
            throw new Exception(JText::_($this->text_prefix . "_ERROR_PAYMENT_HAS_BEEN_DISABLED_MESSAGE"));
        }

        $app = JFactory::getApplication();
        /** @var $app JApplicationAdministrator */

        $cid    = $this->input->get("cid", array(), "array");

        JArrayHelper::toInteger($cid);

        $messages = array();

        // Trigger the event
        try {

            if (!empty($cid)) {

                $options = array(
                    "ids" => $cid,
                    "txn_status" => "pending"
                );

                $items = new Crowdfunding\Transactions(JFactory::getDbo());
                $items->load($options);

                if (count($items) == 0) {
                    throw new UnexpectedValueException(JText::_($this->text_prefix . "_ERROR_INVALID_TRANSACTIONS"));
                }

                // Import Crowdfunding Payment Plugins
                $dispatcher = JEventDispatcher::getInstance();
                JPluginHelper::importPlugin('crowdfundingpayment');

                foreach ($items as $item) {

                    $context = $this->option . '.payments.capture.' . Joomla\String\String::strtolower(str_replace(" ", "", $item->service_provider));

                    // Trigger onContentPreparePayment event.
                    $results = $dispatcher->trigger("onPaymentsCapture", array($context, &$item, &$params));

                    foreach ($results as $message) {
                        if (!is_null($message) and is_array($message)) {
                            $messages[] = $message;
                        }
                    }
                }
            }

        } catch (UnexpectedValueException $e) {

            $this->setMessage($e->getMessage(), "notice");
            $this->setRedirect(JRoute::_("index.php?option=" . $this->option . "&view=transactions", false));
            return;

        } catch (Exception $e) {

            // Store log data in the database
            $this->log->add(
                JText::_($this->text_prefix . "_ERROR_SYSTEM"),
                "CONTROLLER_PAYMENTS_DOCAPTURE_ERROR",
                $e->getMessage()
            );

            throw new Exception(JText::_($this->text_prefix . "_ERROR_SYSTEM"));
        }

        // Set messages.
        if (!empty($messages)) {
            foreach ($messages as $message) {
                $app->enqueueMessage($message["text"], $message["type"]);
            }
        }

        $this->setRedirect(JRoute::_("index.php?option=" . $this->option . "&view=transactions", false));

    }

    public function dovoid()
    {
        // Get component parameters
        $params = JComponentHelper::getParams("com_crowdfunding");
        /** @var $params Joomla\Registry\Registry */

        // Check for disabled payment functionality
        if ($params->get("debug_payment_disabled", 0)) {
            throw new Exception(JText::_($this->text_prefix . "_ERROR_PAYMENT_HAS_BEEN_DISABLED_MESSAGE"));
        }

        $app = JFactory::getApplication();
        /** @var $app JApplicationAdministrator */

        $cid    = $this->input->get("cid", array(), "array");

        JArrayHelper::toInteger($cid);

        $messages = array();
        
        try {

            if (!empty($cid)) {

                $options = array(
                    "ids" => $cid,
                    "txn_status" => "pending"
                );

                $items = new Crowdfunding\Transactions(JFactory::getDbo());
                $items->load($options);

                if (count($items) == 0) {
                    throw new UnexpectedValueException(JText::_($this->text_prefix . "_ERROR_INVALID_TRANSACTIONS"));
                }

                // Import Crowdfunding Payment Plugins
                $dispatcher = JEventDispatcher::getInstance();
                JPluginHelper::importPlugin('crowdfundingpayment');

                foreach ($items as $item) {

                    $context = $this->option . '.payments.void.' . Joomla\String\String::strtolower(str_replace(" ", "", $item->service_provider));

                    // Trigger onContentPreparePayment event.
                    $results = $dispatcher->trigger("onPaymentsVoid", array($context, &$item, &$params));

                    foreach ($results as $message) {
                        if (!is_null($message) and is_array($message)) {
                            $messages[] = $message;
                        }
                    }

                }


            }

        } catch (UnexpectedValueException $e) {

            $this->setMessage($e->getMessage(), "notice");
            $this->setRedirect(JRoute::_("index.php?option=" . $this->option . "&view=transactions", false));

            return;

        } catch (Exception $e) {

            // Store log data in the database
            $this->log->add(
                JText::_($this->text_prefix . "_ERROR_SYSTEM"),
                "CONTROLLER_PAYMENTS_DOCAPTURE_ERROR",
                $e->getMessage()
            );

            throw new Exception(JText::_($this->text_prefix . "_ERROR_SYSTEM"));

        }

        // Set messages.
        if (!empty($messages)) {
            foreach ($messages as $message) {
                $app->enqueueMessage($message["text"], $message["type"]);
            }
        }

        $this->setRedirect(JRoute::_("index.php?option=" . $this->option . "&view=transactions", false));
    }
}
