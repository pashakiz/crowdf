<?php
/**
 * @package      CrowdfundingData
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2014 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// no direct access
defined('_JEXEC') or die;

class CrowdfundingDataViewRecord extends JViewLegacy
{
    /**
     * @var JDocumentHtml
     */
    public $document;

    protected $state;
    protected $item;
    protected $form;

    protected $documentTitle;
    protected $option;

    protected $amount;
    protected $layout;

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

        $this->layout = $this->getLayout();

        if (strcmp($this->layout, "edit") != 0) {
            $currency = new Crowdfunding\Currency(JFactory::getDbo());
            $currency->loadByCode($this->item->txn_currency);

            $crowdfundingParams = JComponentHelper::getParams("com_crowdfunding");

            $this->amount = new Crowdfunding\Amount($crowdfundingParams);
            $this->amount->setCurrency($currency);
        }

        // Prepare actions, behaviors, scripts and document
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

        if (strcmp($this->layout, "edit") != 0) {
            $this->documentTitle = JText::_('COM_CROWDFUNDINGDATA_VIEW_RECORD');
        } else {
            $this->documentTitle = JText::_('COM_CROWDFUNDINGDATA_EDIT_RECORD');

            JToolbarHelper::apply('record.apply');
            JToolbarHelper::save('record.save');
        }

        JToolbarHelper::cancel('record.cancel', 'JTOOLBAR_CANCEL');

        JToolbarHelper::title($this->documentTitle);

    }

    /**
     * Method to set up the document properties
     *
     * @return void
     */
    protected function setDocument()
    {
        $this->document->setTitle($this->documentTitle);

        // Scripts
        JHtml::_('behavior.formvalidation');
        JHtml::_('behavior.tooltip');

        JHtml::_('formbehavior.chosen', 'select');

        $this->document->addScript('../media/' . $this->option . '/js/admin/' . Joomla\String\String::strtolower($this->getName()) . '.js');
    }
}
