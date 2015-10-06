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

class CrowdfundingViewCountry extends JViewLegacy
{
    /**
     * @var JDocumentHtml
     */
    public $document;

    /**
     * @var Joomla\Registry\Registry
     */
    protected $state;
    protected $item;
    protected $form;

    protected $documentTitle;
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

    /**
     * Display the view
     */
    public function display($tpl = null)
    {
        $this->state = $this->get('State');
        $this->item  = $this->get('Item');
        $this->form  = $this->get('Form');

        // Prepare actions, behaviors, scritps and document
        $this->addToolbar();
        $this->setDocument();

        parent::display($tpl);
    }

    /**
     * Add the page title and toolbar.
     *
     * @since   1.6
     */
    protected function addToolbar()
    {
        JFactory::getApplication()->input->set('hidemainmenu', true);
        $isNew = ($this->item->id == 0);

        $this->documentTitle = $isNew ? JText::_('COM_CROWDFUNDING_ADD_COUNTRY')
            : JText::_('COM_CROWDFUNDING_EDIT_COUNTRY');

        JToolbarHelper::title($this->documentTitle);

        JToolbarHelper::apply('country.apply');
        JToolbarHelper::save2new('country.save2new');
        JToolbarHelper::save('country.save');

        if (!$isNew) {
            JToolbarHelper::cancel('country.cancel', 'JTOOLBAR_CANCEL');
        } else {
            JToolbarHelper::cancel('country.cancel', 'JTOOLBAR_CLOSE');
        }
    }

    /**
     * Method to set up the document properties
     * @return void
     */
    protected function setDocument()
    {
        $this->document->setTitle($this->documentTitle);

        // Scripts
        JHtml::_('behavior.formvalidation');
        JHtml::_('behavior.tooltip');

        JHtml::_('formbehavior.chosen', 'select');

        $this->document->addScript(JURI::root() . 'media/' . $this->option . '/js/admin/' . Joomla\String\String::strtolower($this->getName()) . '.js');
    }
}
