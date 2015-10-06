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
    protected $pagination;

    protected $amount;
    protected $rewards;

    protected $option;

    protected $listOrder;
    protected $listDirn;
    protected $saveOrder;
    protected $saveOrderingUrl;
    protected $sortFields;

    protected $sidebar;

    public function __construct($config)
    {
        parent::__construct($config);
        $this->option = JFactory::getApplication()->input->get("option");
    }

    public function display($tpl = null)
    {
        $this->state      = $this->get('State');
        $this->items      = $this->get('Items');
        $this->pagination = $this->get('Pagination');

        $this->params = $this->state->get("params");

        // Get currency
        $currency = Crowdfunding\Currency::getInstance(JFactory::getDbo(), $this->state->params->get("project_currency"));

        // Create object "Amount".
        $this->amount   = new Crowdfunding\Amount($this->params);
        $this->amount->setCurrency($currency);

        // Get projects IDs
        $projectsIds = array();
        foreach ($this->items as $item) {
            $projectsIds[] = $item->id;
        }

        // Get number of rewards.
        $projects = new Crowdfunding\Projects(JFactory::getDbo());
        $this->rewards = $projects->getRewardsNumber($projectsIds);

        // Add submenu
        CrowdfundingHelper::addSubmenu($this->getName());

        // Prepare sorting data
        $this->prepareSorting();

        // Prepare actions
        $this->addToolbar();
        $this->addSidebar();
        $this->setDocument();

        parent::display($tpl);
    }

    /**
     * Prepare sortable fields, sort values and filters.
     */
    protected function prepareSorting()
    {
        // Prepare filters
        $this->listOrder = $this->escape($this->state->get('list.ordering'));
        $this->listDirn  = $this->escape($this->state->get('list.direction'));
        $this->saveOrder = (strcmp($this->listOrder, 'a.ordering') != 0) ? false : true;

        if ($this->saveOrder) {
            $this->saveOrderingUrl = 'index.php?option=' . $this->option . '&task=' . $this->getName() . '.saveOrderAjax&format=raw';
            JHtml::_('sortablelist.sortable', $this->getName() . 'List', 'adminForm', strtolower($this->listDirn), $this->saveOrderingUrl);
        }

        $this->sortFields = array(
            'a.ordering'      => JText::_('JGRID_HEADING_ORDERING'),
            'a.published'     => JText::_('JSTATUS'),
            'a.title'         => JText::_('COM_CROWDFUNDING_TITLE'),
            'b.title'         => JText::_('COM_CROWDFUNDING_CATEGORY'),
            'a.created'       => JText::_('COM_CROWDFUNDING_CREATED'),
            'a.goal'          => JText::_('COM_CROWDFUNDING_GOAL'),
            'a.funded'        => JText::_('COM_CROWDFUNDING_FUNDED'),
            'funded_percents' => JText::_('COM_CROWDFUNDING_FUNDED_PERCENTS'),
            'a.funding_start' => JText::_('COM_CROWDFUNDING_START_DATE'),
            'a.funding_end'   => JText::_('COM_CROWDFUNDING_END_DATE'),
            'a.approved'      => JText::_('COM_CROWDFUNDING_APPROVED'),
            'd.name'          => JText::_('COM_CROWDFUNDING_OWNER'),
            'a.id'            => JText::_('JGRID_HEADING_ID')

        );
    }

    /**
     * Add a menu on the sidebar of page
     */
    protected function addSidebar()
    {
        JHtmlSidebar::setAction('index.php?option=' . $this->option . '&view=' . $this->getName());

        // Prepare options
        $approvedOptions = array(
            JHtml::_("select.option", 1, JText::_("COM_CROWDFUNDING_APPROVED")),
            JHtml::_("select.option", 0, JText::_("COM_CROWDFUNDING_DISAPPROVED")),
        );

        $featuredOptions = array(
            JHtml::_("select.option", 1, JText::_("COM_CROWDFUNDING_FEATURED")),
            JHtml::_("select.option", 0, JText::_("COM_CROWDFUNDING_NOT_FEATURED")),
        );

        JHtmlSidebar::addFilter(
            JText::_('JOPTION_SELECT_PUBLISHED'),
            'filter_state',
            JHtml::_('select.options', JHtml::_('jgrid.publishedOptions', array("archived" => false)), 'value', 'text', $this->state->get('filter.state'), true)
        );

        JHtmlSidebar::addFilter(
            JText::_('COM_CROWDFUNDING_SELECT_APPROVED_STATUS'),
            'filter_approved',
            JHtml::_('select.options', $approvedOptions, 'value', 'text', $this->state->get('filter.approved'), true)
        );

        JHtmlSidebar::addFilter(
            JText::_('COM_CROWDFUNDING_SELECT_FEATURED_STATUS'),
            'filter_featured',
            JHtml::_('select.options', $featuredOptions, 'value', 'text', $this->state->get('filter.featured'), true)
        );

        JHtmlSidebar::addFilter(
            JText::_('JOPTION_SELECT_CATEGORY'),
            'filter_category_id',
            JHtml::_('select.options', JHtml::_('category.options', 'com_crowdfunding'), 'value', 'text', $this->state->get('filter.category_id'))
        );

        $filters      = new Crowdfunding\Filters(JFactory::getDbo());
        $typesOptions = $filters->getProjectsTypes();

        JHtmlSidebar::addFilter(
            JText::_('COM_CROWDFUNDING_SELECT_TYPE'),
            'filter_type_id',
            JHtml::_('select.options', $typesOptions, 'value', 'text', $this->state->get('filter.type_id'))
        );

        $this->sidebar = JHtmlSidebar::render();
    }

    /**
     * Add the page title and toolbar.
     *
     * @since   1.6
     */
    protected function addToolbar()
    {
        // Set toolbar items for the page
        JToolbarHelper::title(JText::_('COM_CROWDFUNDING_PROJECTS_MANAGER'));
        JToolbarHelper::addNew('project.add');
        JToolbarHelper::editList('project.edit');
        JToolbarHelper::divider();
        JToolbarHelper::publishList("projects.publish");
        JToolbarHelper::unpublishList("projects.unpublish");
        JToolbarHelper::divider();
        JToolbarHelper::custom('projects.approve', "ok", "", JText::_("COM_CROWDFUNDING_APPROVE"), false);
        JToolbarHelper::custom('projects.disapprove', "ban-circle", "", JText::_("COM_CROWDFUNDING_DISAPPROVE"), false);

        JToolbarHelper::divider();

        if ($this->state->get('filter.state') == -2) {
            JToolbarHelper::deleteList('', 'projects.delete', 'JTOOLBAR_EMPTY_TRASH');
        } else {
            JToolbarHelper::trash('projects.trash');
        }

        JToolbarHelper::divider();
        JToolbarHelper::custom('projects.backToDashboard', "dashboard", "", JText::_("COM_CROWDFUNDING_DASHBOARD"), false);
    }

    /**
     * Method to set up the document properties
     *
     * @return void
     */
    protected function setDocument()
    {
        $this->document->setTitle(JText::_('COM_CROWDFUNDING_PROJECTS_MANAGER'));

        // Scripts
        JHtml::_('bootstrap.tooltip');
        JHtml::_('behavior.multiselect');

        JHtml::_('formbehavior.chosen', 'select');

        JHtml::_('prism.ui.joomlaList');
    }
}
