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

class CrowdfundingViewCategories extends JViewLegacy
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
    protected $pagination;

    protected $allowedLayouts = array("grid", "list");
    protected $templateView;
    protected $descriptionLength;
    protected $displayProjectsNumber;
    protected $projectsNumber;
    protected $numberInRow;

    protected $listOrder;
    protected $listDirn;
    protected $saveOrder;

    protected $option;

    protected $pageclass_sfx;

    public function __construct($config)
    {
        parent::__construct($config);
        $this->option = JFactory::getApplication()->input->get("option");
    }

    public function display($tpl = null)
    {
        $this->items      = $this->get('Items');
        $this->state      = $this->get('State');
        $this->pagination = $this->get('Pagination');

        $this->params     = $this->state->get("params");

        $this->displayProjectsNumber = $this->params->get("categories_display_projects_number", 0);
        $this->numberInRow = $this->params->get("categories_categories_row", 3);

        // Get description length
        $this->descriptionLength = $this->params->get("categories_description_length", 128);

        // Load projects number.
        if ($this->displayProjectsNumber) {
            $ids = array();
            foreach ($this->items as $item) {
                $ids[] = $item->id;
            }

            $categories = new Crowdfunding\Categories();
            $categories->setDb(JFactory::getDbo());

            $this->projectsNumber = $categories->getProjectsNumber($ids, array("state" => Prism\Constants::PUBLISHED, "approved" => Prism\Constants::APPROVED));
        }

        // Prepare items parameters.
        if (!empty($this->items)) {
            $this->items = CrowdfundingHelper::prepareCategories($this->items, $this->numberInRow);
        }

        // Get layout
        $layout = $this->params->get("categories_layout", "grid");
        $this->templateView = (in_array($layout, $this->allowedLayouts)) ? $layout : "grid";

        // Get params
        /** @var  $params Joomla\Registry\Registry */
        $params = $this->state->get("params");
        $this->params = $params;

        // Prepare filters
        $this->listOrder = $this->escape($this->state->get('list.ordering'));
        $this->listDirn  = $this->escape($this->state->get('list.direction'));
        $this->saveOrder = (strcmp($this->listOrder, 'a.ordering') != 0) ? false : true;

        $this->prepareDocument();

        parent::display($tpl);
    }

    /**
     * Prepare document
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
            $this->params->def('page_heading', JText::_('COM_CROWDFUNDING_CATEGORIES_DEFAULT_PAGE_TITLE'));
        }
    }

    private function preparePageTitle()
    {
        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        // Prepare page title
        $title = $this->params->get('page_title', '');

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
