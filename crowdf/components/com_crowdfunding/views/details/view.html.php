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

class CrowdfundingViewDetails extends JViewLegacy
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

    protected $imageFolder;
    protected $screen;
    protected $items;
    protected $form;
    protected $userId;
    protected $isOwner;
    protected $avatarsSize;
    protected $socialProfiles;
    protected $defaultAvatar;
    protected $onCommentAfterDisplay;
    protected $commentsEnabled;
    protected $amount;
    protected $displayAmounts;

    protected $option;

    protected $pageclass_sfx;

    public function __construct($config)
    {
        parent::__construct($config);
        $this->option = JFactory::getApplication()->input->get("option");
    }

    public function display($tpl = null)
    {
        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        // Get model state.
        $this->state  = $this->get('State');
        $this->item   = $this->get("Item");

        // Get params
        $this->params = $this->state->get("params");
        /** @var  $this->params Joomla\Registry\Registry */

        $model  = $this->getModel();
        $userId = JFactory::getUser()->get("id");

        if (!$this->item or $model->isRestricted($this->item, $userId)) {
            $app->enqueueMessage(JText::_("COM_CROWDFUNDING_ERROR_INVALID_PROJECT"), "notice");
            $app->redirect(JRoute::_('index.php?option=com_crowdfunding&view=discover', false));
            return;
        }

        // Get the path to the images.
        $this->imageFolder = $this->params->get("images_directory", "images/crowdfunding");

        $this->defaultAvatar = JUri::base() . $this->params->get("integration_avatars_default");
        $this->avatarsSize   = $this->params->get("integration_avatars_size", "small");

        // Prepare the link that points to project page.
        $host             = JUri::getInstance()->toString(array("scheme", "host"));
        $this->item->link = $host . JRoute::_(CrowdfundingHelperRoute::getDetailsRoute($this->item->slug, $this->item->catslug));

        // Prepare the link that points to project image.
        $this->item->link_image = $host . "/" . $this->imageFolder . "/" . $this->item->image;

        // Get the current screen.
        $this->screen = $app->input->getCmd("screen", "home");

        $this->prepareDocument();

        // Import content plugins
        JPluginHelper::importPlugin('content');

        switch ($this->screen) {

            case "updates":
                $this->prepareUpdatesScreen();
                break;

            case "comments":
                $this->prepareCommentsScreen();
                break;

            case "funders":
                $this->prepareFundersScreen();
                break;

            default: // Home
                break;
        }

        // Events
        $dispatcher        = JEventDispatcher::getInstance();
        $this->item->event = new stdClass();
        $offset            = 0;

        $results                                 = $dispatcher->trigger('onContentBeforeDisplay', array('com_crowdfunding.details', &$this->item, &$this->params, $offset));
        $this->item->event->beforeDisplayContent = trim(implode("\n", $results));

        $results                                 = $dispatcher->trigger('onContentAfterDisplayMedia', array('com_crowdfunding.details', &$this->item, &$this->params, $offset));
        $this->item->event->onContentAfterDisplayMedia = trim(implode("\n", $results));

        $results                                  = $dispatcher->trigger('onContentAfterDisplay', array('com_crowdfunding.details', &$this->item, &$this->params, $offset));
        $this->item->event->onContentAfterDisplay = trim(implode("\n", $results));

        // Count hits
        $model->hit($this->item->id);

        parent::display($tpl);
    }

    protected function prepareUpdatesScreen()
    {
        $model       = JModelLegacy::getInstance("Updates", "CrowdfundingModel", $config = array('ignore_request' => false));
        $this->items = $model->getItems();
        $this->form  = $model->getForm();

        $this->userId  = JFactory::getUser()->id;
        $this->isOwner = ($this->userId != $this->item->user_id) ? false : true;

        // Get users IDs
        $usersIds = array();
        foreach ($this->items as $item) {
            $usersIds[] = $item->user_id;
        }

        // Prepare social integration.
        $this->socialProfiles = CrowdfundingHelper::prepareIntegrations($this->params->get("integration_social_platform"), $usersIds);

        // Scripts
        JHtml::_('behavior.keepalive');
        JHtml::_('behavior.formvalidation');
        JHtml::_('prism.ui.pnotify');

        JHtml::_("prism.ui.joomlaHelper");

        $this->document->addScript('media/' . $this->option . '/js/site/updates.js');
    }

    protected function prepareCommentsScreen()
    {
        $this->commentsEnabled = $this->params->get("comments_enabled", 1);

        // Initialize default comments functionality.
        if ($this->commentsEnabled) {
            $model       = JModelLegacy::getInstance("Comments", "CrowdfundingModel", $config = array('ignore_request' => false));
            $this->items = $model->getItems();
            $this->form  = $model->getForm();

            $this->userId  = JFactory::getUser()->get("id");
            $this->isOwner = ($this->userId != $this->item->user_id) ? false : true;

            // Get users IDs
            $usersIds = array();
            foreach ($this->items as $item) {
                $usersIds[] = $item->user_id;
            }

            $usersIds = array_unique($usersIds);

            // Prepare social integration.
            $this->socialProfiles = CrowdfundingHelper::prepareIntegrations($this->params->get("integration_social_platform"), $usersIds);

            // Scripts
            JHtml::_('behavior.keepalive');
            JHtml::_('behavior.formvalidation');
            JHtml::_('prism.ui.pnotify');

            JHtml::_("prism.ui.joomlaHelper");

            $this->document->addScript('media/' . $this->option . '/js/site/comments.js');
        }

        // Trigger comments plugins.
        $dispatcher        = JEventDispatcher::getInstance();

        $results = $dispatcher->trigger('onContentAfterDisplay', array('com_crowdfunding.comments', &$this->item, &$this->params));
        $this->onCommentAfterDisplay = trim(implode("\n", $results));
    }

    protected function prepareFundersScreen()
    {
        $model       = JModelLegacy::getInstance("Funders", "CrowdfundingModel", $config = array('ignore_request' => false));
        $this->items = $model->getItems();

        // Get users IDs
        $usersIds = array();
        foreach ($this->items as $item) {
            $usersIds[] = $item->id;
        }

        $usersIds = array_filter($usersIds);

        // Create a currency object if I have to display funders amounts.
        $this->displayAmounts = $this->params->get("funders_display_amounts", 0);
        if ($this->displayAmounts) {
            $currency = Crowdfunding\Currency::getInstance(JFactory::getDbo(), $this->params->get("project_currency"));
            $this->amount = new Crowdfunding\Amount($this->params);
            $this->amount->setCurrency($currency);
        }

        // Prepare social integration.
        $this->socialProfiles = CrowdfundingHelper::prepareIntegrations($this->params->get("integration_social_platform"), $usersIds);
    }

    /**
     * Prepare the document
     */
    protected function prepareDocument()
    {
        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        // Escape strings for HTML output
        $this->pageclass_sfx = htmlspecialchars($this->params->get('pageclass_sfx'));

        // Prepare page heading
        $this->preparePageHeading();

        // Prepare page heading
        $this->preparePageTitle();

        // Meta description
        $this->document->setDescription($this->item->short_desc);

        if ($this->params->get('menu-meta_keywords')) {
            $this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
        }

        if ($this->params->get('robots')) {
            $this->document->setMetadata('robots', $this->params->get('robots'));
        }

        // Breadcrumb
        $pathway           = $app->getPathWay();
        $currentBreadcrumb = JHtmlString::truncate($this->item->title, 32);
        $pathway->addItem($currentBreadcrumb, '');

        // Add scripts
        JHtml::_('jquery.framework');
    }

    private function preparePageHeading()
    {
        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        // Because the application sets a default page title,
        // we need to get it from the menu item itself
        $menus = $app->getMenu();
        $menu  = $menus->getActive();

        // Prepare page heading
        if ($menu) {
            $this->params->def('page_heading', $this->params->get('page_title', $menu->title));
        } else {
            $this->params->def('page_heading', JText::sprintf('COM_CROWDFUNDING_DETAILS_DEFAULT_PAGE_TITLE', $this->item->title));
        }
    }

    private function preparePageTitle()
    {
        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        // Prepare page title
//        $title = $this->params->get('page_title', $this->item->title);
        $title = $this->item->title;

        switch ($this->screen) {

            case "updates":
                $title .= " | " . JText::_("COM_CROWDFUNDING_UPDATES");
                break;

            case "comments":
                $title .= " | " . JText::_("COM_CROWDFUNDING_COMMENTS");
                break;

            case "funders":
                $title .= " | " . JText::_("COM_CROWDFUNDING_FUNDERS");
                break;

        }

        // Add title before or after Site Name
        if (!$title) {
            $title = $app->get('sitename');
        } elseif ($app->get('sitename_pagetitles', 0) == 1) {
            $title = JText::sprintf('JPAGETITLE', $app->get('sitename'), $title);
        } elseif ($app->get('sitename_pagetitles', 0) == 2) {
            $title = JText::sprintf('JPAGETITLE', $title, $app->get('sitename'));
        }

        $this->document->setTitle($title);

    }
}
