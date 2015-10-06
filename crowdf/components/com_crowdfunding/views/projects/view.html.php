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

class CrowdfundingViewProjects extends JViewLegacy
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

    protected $items;

    protected $amount;
    protected $listOrder;
    protected $listDirn;
    protected $saveOrder;

    protected $option;

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
        $userId = JFactory::getUser()->get("id");
        if (!$userId) {
            $this->app->enqueueMessage(JText::_("COM_CROWDFUNDING_ERROR_NOT_LOG_IN"), "notice");
            $this->app->redirect(JRoute::_("index.php?option=com_users&view=login", false));
            return;
        }

        // Initialise variables
        $this->items  = $this->get('Items');
        $this->state  = $this->get('State');

        $this->params = $this->state->get('params');

        if (!empty($this->items)) {
            // Get currency
            $currency     = Crowdfunding\Currency::getInstance(JFactory::getDbo(), $this->params->get("project_currency"));
            $this->amount = new Crowdfunding\Amount($this->params);
            $this->amount->setCurrency($currency);
        }

        // Prepare filters
        $this->listOrder = $this->escape($this->state->get('list.ordering'));
        $this->listDirn  = $this->escape($this->state->get('list.direction'));
        $this->saveOrder = (strcmp($this->listOrder, 'a.ordering') != 0) ? false : true;

        $this->prepareDocument();

        parent::display($tpl);
    }

    /**
     * Prepare document.
     *
     */
    protected function prepareDocument()
    {
        //Escape strings for HTML output
        $this->pageclass_sfx = htmlspecialchars($this->params->get('pageclass_sfx'));

        // Prepare page heading
        $this->preparePageHeading();

        // Prepare page heading
        $this->preparePageTitle();

        // Meta Description
        if ($this->params->get('menu-meta_description')) {
            $this->document->setDescription($this->params->get('menu-meta_description'));
        }

        // Meta keywords
        if ($this->params->get('menu-meta_keywords')) {
            $this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
        }

        if ($this->params->get('robots')) {
            $this->document->setMetadata('robots', $this->params->get('robots'));
        }

        // Scripts
        JHtml::_('bootstrap.tooltip');
    }

    private function preparePageHeading()
    {
        // Because the application sets a default page title,
        // we need to get it from the menu item itself
        $menus = $this->app->getMenu();
        $menu  = $menus->getActive();

        // Prepare page heading
        if ($menu) {
            $this->params->def('page_heading', $this->params->get('page_title', $menu->title));
        } else {
            $this->params->def('page_heading', JText::_('COM_CROWDFUNDING_PROJECTS_DEFAULT_PAGE_TITLE'));
        }
    }

    private function preparePageTitle()
    {
        // Prepare page title
        $title = $this->params->get('page_title', '');

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
}
