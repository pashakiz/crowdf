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

class CrowdfundingViewLog extends JViewLegacy
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

    protected $files;

    protected $documentTitle;
    protected $option;

    protected $includeFiles = array(
        "/error_log",
        "/php_errorlog"
    );

    public function __construct($config)
    {
        parent::__construct($config);
        $this->option = JFactory::getApplication()->input->get("option");
    }

    public function display($tpl = null)
    {
        $this->state = $this->get('State');

        $layout = $this->getLayout();

        switch ($layout) {

            case "view":
                $this->item = $this->get('Item');
                break;

            case "files":
                $this->files = new Crowdfunding\Log\Files($this->includeFiles);
                $this->files->load();
                break;

        }

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

        $layout = $this->getLayout();

        switch ($layout) {

            case "view":
                $this->documentTitle = JText::_('COM_CROWDFUNDING_VIEW_LOG_DATA');
                JToolbarHelper::custom("logs.delete", "delete", "", JText::_("JTOOLBAR_DELETE"), false);
                break;

            case "files":

                $this->documentTitle = JText::_('COM_CROWDFUNDING_VIEW_LOG_FILES');

                $bar = JToolbar::getInstance('toolbar');
                $bar->appendButton('Link', 'refresh', JText::_("COM_CROWDFUNDING_RELOAD"), JRoute::_("index.php?option=com_crowdfunding&view=log&layout=files"));

                break;

        }

        JToolbarHelper::title($this->documentTitle);
        JToolbarHelper::cancel('log.cancel', 'JTOOLBAR_CLOSE');
    }

    /**
     * Method to set up the document properties
     *
     * @return void
     */
    protected function setDocument()
    {
        $this->document->setTitle($this->documentTitle);

        $layout = $this->getLayout();

        // Scripts
        JHtml::_('jquery.framework');
        JHtml::_('bootstrap.tooltip');

        switch ($layout) {

            case "files":

                // HTML Helpers
                JHtml::_('prism.ui.pnotify');

                JHtml::_("prism.ui.joomlaHelper");

                // Load language string in JavaScript
                JText::script('COM_CROWDFUNDING_DELETE_FILE_QUESTION');

                break;

        }

        $this->document->addScript('../media/' . $this->option . '/js/admin/' . Joomla\String\String::strtolower($this->getName()) . '.js');
    }
}
