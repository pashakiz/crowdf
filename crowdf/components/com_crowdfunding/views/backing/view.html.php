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

class CrowdfundingViewBacking extends JViewLegacy
{
    /**
     * @var JDocumentHtml
     */
    public $document;

    /**
     * @var Joomla\Registry\Registry
     */
    protected $state;

    /**
     * @var Joomla\Registry\Registry
     */
    protected $params;

    protected $item;

    /**
     * @var Crowdfunding\Amount
     */
    protected $amount;

    /**
     * @var Crowdfunding\Currency
     */
    protected $currency;

    protected $imageFolder;
    protected $layout;
    protected $rewardsEnabled;
    protected $disabledButton;
    protected $loginForm;
    protected $returnUrl;
    protected $layoutData;
    protected $rewardId;
    protected $rewards;
    protected $rewardAmount;
    protected $reward;
    protected $paymentAmount;
    protected $option;

    protected $paymentSessionContext;
    protected $paymentSession;
    
    protected $wizardType;
    protected $event;
    protected $secondStepTask;
    protected $fourSteps;

    protected $pageclass_sfx;

    /**
     * @var JApplicationSite
     */
    protected $app;

    public function __construct($config)
    {
        parent::__construct($config);

        $this->app    = JFactory::getApplication();
        $this->option = $this->app->input->get("option");
    }

    public function display($tpl = null)
    {
        // Get model state.
        $this->state  = $this->get('State');
        $this->item   = $this->get("Item");

        // Get params
        $this->params = $this->state->get("params");

        if (!$this->item) {
            $this->app->enqueueMessage(JText::_("COM_CROWDFUNDING_ERROR_INVALID_PROJECT"), "notice");
            $this->app->redirect(JRoute::_(CrowdfundingHelperRoute::getDiscoverRoute(), false));
            return;
        }

        // Create an object that will contain the data during the payment process.
        $this->paymentSessionContext = Crowdfunding\Constants::PAYMENT_SESSION_CONTEXT . $this->item->id;
        $paymentSession              = $this->app->getUserState($this->paymentSessionContext);

        // Create payment session object.
        if (!$paymentSession or !isset($paymentSession->step1)) {
            $paymentSession        = new JData();
            $paymentSession->step1 = false;
        }

        // Images
        $this->imageFolder = $this->params->get("images_directory", "images/crowdfunding");

        // Get currency
        $this->currency = Crowdfunding\Currency::getInstance(JFactory::getDbo(), $this->params->get("project_currency"));
        $this->amount   = new Crowdfunding\Amount($this->params);
        $this->amount->setCurrency($this->currency);

        // Set a link that points to project page
        $filter    = JFilterInput::getInstance();
        $host      = JUri::getInstance()->toString(array('scheme', 'host'));
        $host      = $filter->clean($host);

        $this->item->link =  $host . JRoute::_(CrowdfundingHelperRoute::getDetailsRoute($this->item->slug, $this->item->catslug), false);

        // Set a link to image
        $this->item->link_image = $host . "/" . $this->imageFolder . "/" . $this->item->image;

        // Get wizard type
        $this->wizardType = $this->params->get("backing_wizard_type", "three_steps");
        $this->fourSteps  = (strcmp("four_steps", $this->wizardType) != 0) ? false : true;

        // Import "crowdfundingpayment" plugins.
        JPluginHelper::importPlugin('crowdfundingpayment');

        $this->layout = $this->getLayout();

        switch ($this->layout) {

            case "step2":
                $this->prepareStep2();
                break;

            case "payment":
                $this->preparePayment($paymentSession);
                break;

            case "share":
                $this->prepareShare($paymentSession);
                break;

            default: //  Pledge and Rewards
                $this->prepareRewards($paymentSession);
                break;
        }

        // Get project type and check for enabled rewards.
        $this->rewardsEnabled = CrowdfundingHelper::isRewardsEnabled($this->item->id);

        // Check days left. If there is no days, disable the button.
        $this->disabledButton = "";
        if (!$this->item->days_left) {
            $this->disabledButton = 'disabled="disabled"';
        }

        $this->paymentSession = $paymentSession;

        // Prepare the data of the layout
        $this->layoutData = new JData(array(
            "layout"         => $this->layout,
            "item"           => $this->item,
            "paymentSession" => $paymentSession,
            "rewards_enabled" => $this->rewardsEnabled
        ));

        $this->prepareDebugMode($paymentSession);
        $this->prepareDocument();

        parent::display($tpl);
    }

    /**
     * This method displays a content from a Crowdfunding Plugin.
     */
    protected function prepareStep2()
    {
        // Trigger the event on step 2 and display the content.
        $dispatcher = JEventDispatcher::getInstance();
        $results    = $dispatcher->trigger('onPaymentExtras', array('com_crowdfunding.payment.step2', &$this->item, &$this->params));

        $result                 = (string)array_pop($results);

        $this->event            = new stdClass();
        $this->event->onDisplay = Joomla\String\String::trim($result);
    }

    protected function prepareRewards(&$paymentSession)
    {
        // Create payment session ID.
        $sessionId = new Prism\String();
        $sessionId->generateRandomString(32);

        $paymentSession->session_id = (string)$sessionId;

        // Get selected reward ID
        $this->rewardId = $this->state->get("reward_id");

        // If it has been selected another reward, set the old one to 0.
        if ($this->rewardId != $paymentSession->rewardId) {
            $paymentSession->rewardId = 0;
            $paymentSession->step1    = false;
        }

        // Get amount from session
        $this->rewardAmount = (!$paymentSession->amount) ? 0 : $paymentSession->amount;

        // Get rewards
        $this->rewards = new  Crowdfunding\Rewards(JFactory::getDbo());
        $this->rewards->load(array("project_id" => $this->item->id, "state" => 1));

        // Compare amount with the amount of reward, that is selected.
        // If the amount of selected reward is larger than amount from session,
        // use the amount of selected reward.
        if (!empty($this->rewardId)) {
            foreach ($this->rewards as $reward) {
                if ($this->rewardId == $reward["id"]) {

                    if ($this->rewardAmount < $reward["amount"]) {
                        $this->rewardAmount = $reward["amount"];
                        $paymentSession->step1 = false;
                    }

                    break;
                }
            }
        }

        // Store the new values of the payment process to the user session.
        $this->app->setUserState($this->paymentSessionContext, $paymentSession);

        if (!$this->fourSteps) {
            $this->secondStepTask = "backing.process";
        } else {
            $this->secondStepTask = "backing.step2";
        }
    }

    protected function preparePayment(&$paymentSession)
    {
        // If missing the flag "step1", redirect to first step.
        if (!$paymentSession->step1) {
            $this->returnToStep1($paymentSession, JText::_("COM_CROWDFUNDING_ERROR_INVALID_AMOUNT"));
        }

        // Check for both user states. The user must have only one state - registered user or anonymous user.
        $userId  = JFactory::getUser()->get("id");
        $aUserId = $this->app->getUserState("auser_id");

        if ((!empty($userId) and !empty($aUserId)) or (empty($userId) and empty($aUserId))) {

            // Reset anonymous hash user ID and redirect to first step.
            $this->app->setUserState("auser_id", "");

            $this->returnToStep1($paymentSession);
        }

        if (!$this->item->days_left) {
            $this->returnToStep1($paymentSession, JText::_("COM_CROWDFUNDING_ERROR_PROJECT_COMPLETED"));
        }

        // Validate reward
        $this->reward = null;
        $keys         = array(
            "id"         => $paymentSession->rewardId,
            "project_id" => $this->item->id
        );

        $this->reward = new Crowdfunding\Reward(JFactory::getDbo());
        $this->reward->load($keys);

        if ($this->reward->getId()) {
            if ($this->reward->isLimited() and !$this->reward->getAvailable()) {
                $this->returnToStep1($paymentSession, JText::_("COM_CROWDFUNDING_ERROR_REWARD_NOT_AVAILABLE"));
            }
        }

        // Set the amount that will be displayed in the view.
        $this->paymentAmount = $paymentSession->amount;

        // Validate the amount.
        if (!$this->paymentAmount) {
            $this->returnToStep1($paymentSession, JText::_("COM_CROWDFUNDING_ERROR_INVALID_AMOUNT"));
        }

        // Events

        $item = new stdClass();

        $item->id             = $this->item->id;
        $item->title          = $this->item->title;
        $item->slug           = $this->item->slug;
        $item->catslug        = $this->item->catslug;
        $item->rewardId       = $paymentSession->rewardId;
        $item->amount         = $paymentSession->amount;
        $item->currencyCode   = $this->currency->getCode();

        $item->amountFormated = $this->amount->setValue($item->amount)->format();
        $item->amountCurrency = $this->amount->setValue($item->amount)->formatCurrency();

        $this->item->event    = new stdClass();

        // onBeforePaymentAuthorize
        JPluginHelper::importPlugin('crowdfundingpayment');
        $dispatcher = JEventDispatcher::getInstance();
        $results    = $dispatcher->trigger('onBeforePaymentAuthorize', array('com_crowdfunding.before.payment.authorize', &$item, &$this->amount, &$this->params));

        if (!empty($results)) {
            $this->item->event->onBeforePaymentAuthorize = trim(implode("\n", $results));
        } else { // onProjectPayment
            $results    = $dispatcher->trigger('onProjectPayment', array('com_crowdfunding.payment', &$item, &$this->params));
            $this->item->event->onProjectPayment = trim(implode("\n", $results));
        }

    }

    protected function prepareShare(&$paymentSession)
    {
        // Get amount from session that will be displayed in the view.
        $this->paymentAmount = $paymentSession->amount;

        // Get reward
        $this->reward = null;
        if (!empty($paymentSession->rewardId)) {

            $keys = array(
                "id"         => $paymentSession->rewardId,
                "project_id" => $this->item->id
            );

            $this->reward = new Crowdfunding\Reward(JFactory::getDbo());
            $this->reward->load($keys);
        }

        // Events
        JPluginHelper::importPlugin('content');
        $dispatcher = JEventDispatcher::getInstance();

        $offset = 0;

        $results = $dispatcher->trigger('onContentAfterDisplay', array('com_crowdfunding.payment.share', &$this->item, &$this->params, $offset));

        $this->item->event                      = new stdClass();
        $this->item->event->afterDisplayContent = trim(implode("\n", $results));

        // Reset anonymous hash user ID.
        $this->app->setUserState("auser_id", "");

        // Initialize the payment process object.
        $paymentSession        = new JData();
        $paymentSession->step1 = false;
        $this->app->setUserState($this->paymentSessionContext, $paymentSession);
    }

    /**
     * Check the system for debug mode
     *
     * @param JData
     */
    protected function prepareDebugMode(&$paymentSession)
    {
        // Check for maintenance (debug) state.
        $params = $this->state->get("params");
        if ($params->get("debug_payment_disabled", 0)) {
            $msg = Joomla\String\String::trim($params->get("debug_disabled_functionality_msg"));
            if (!$msg) {
                $msg = JText::_("COM_CROWDFUNDING_DEBUG_MODE_DEFAULT_MSG");
            }
            $this->app->enqueueMessage($msg, "notice");

            $this->disabledButton = 'disabled="disabled"';

            // Store the new values of the payment process to the user session.
            $paymentSession->step1 = false;
            $this->app->setUserState($this->paymentSessionContext, $paymentSession);
        }

    }

    /**
     * Prepare the document
     */
    protected function prepareDocument()
    {
        // Escape strings for HTML output
        $this->pageclass_sfx = htmlspecialchars($this->params->get('pageclass_sfx'));

        // Prepare page heading
        $this->preparePageHeading();

        // Prepare page heading
        $this->preparePageTitle();

        if ($this->params->get('menu-meta_description')) {
            $this->document->setDescription($this->params->get('menu-meta_description'));
        } else {
            $this->document->setDescription($this->item->short_desc);
        }

        if ($this->params->get('menu-meta_keywords')) {
            $this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
        }

        if ($this->params->get('robots')) {
            $this->document->setMetadata('robots', $this->params->get('robots'));
        }

        // Breadcrumb
        $pathway           = $this->app->getPathWay();
        $currentBreadcrumb = JHtmlString::truncate($this->item->title, 16);
        $pathway->addItem($currentBreadcrumb, '');

        // Scripts
        JHtml::_('jquery.framework');
        $this->document->addScript('media/' . $this->option . '/js/site/backing.js');
    }

    protected function preparePageHeading()
    {
        // Because the application sets a default page title,
        // we need to get it from the menu item itself
        $menus = $this->app->getMenu();
        $menu  = $menus->getActive();

        // Prepare page heading
        if ($menu) {
            $this->params->def('page_heading', $this->params->get('page_title', $menu->title));
        } else {
            $this->params->def('page_heading', JText::sprintf('COM_CROWDFUNDING_BACKING_DEFAULT_PAGE_TITLE', $this->item->title));
        }

    }

    protected function preparePageTitle()
    {
        // Prepare page title
        $title = JText::sprintf("COM_CROWDFUNDING_INVESTING_IN", $this->escape($this->item->title));

        switch ($this->getLayout()) {

            case "payment":
                $title .= " | " . JText::_("COM_CROWDFUNDING_PAYMENT_METHODS");
                break;

            case "share":
                $title .= " | " . JText::_("COM_CROWDFUNDING_SHARE");
                break;

        }

        // Add title before or after Site Name
        if (!$title) {
            $title = $this->app->get('sitename');
        } elseif ($this->app->get('sitename_pagetitles', 0) == 1) {
            $title = JText::sprintf('JPAGETITLE', $this->app->get('sitename'), $title);
        } elseif ($this->app->get('sitename_pagetitles', 0) == 2) {
            $title = JText::sprintf('JPAGETITLE', $title, $this->app->get('sitename'));
        }

        $this->document->setTitle($title);
    }

    protected function returnToStep1(&$paymentSession, $message = "")
    {
        // Reset the flag for step 1
        $paymentSession->step1 = false;
        $this->app->setUserState($this->paymentSessionContext, $paymentSession);

        if (!empty($message)) {
            $this->app->enqueueMessage($message, "notice");
        }
        $this->app->redirect(JRoute::_(CrowdfundingHelperRoute::getBackingRoute($this->item->slug, $this->item->catslug), false));
    }
}
