<?php
/**
 * @package      CrowdfundingPartners
 * @subpackage   Component
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2014 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// no direct access
defined('_JEXEC') or die;

class CrowdfundingPartnersViewDashboard extends JViewLegacy
{
    /**
     * @var JDocumentHtml
     */
    public $document;

    protected $version;
    protected $itprismVersion;

    protected $option;

    protected $sidebar;

    public function __construct($config)
    {
        parent::__construct($config);
        $this->option = JFactory::getApplication()->input->get("option");
    }

    public function display($tpl = null)
    {
        $this->version = new CrowdfundingPartners\Version();

        // Load ITPrism library version
        if (!class_exists("Prism\\Version")) {
            $this->itprismVersion = JText::_("COM_CROWDFUNDINGPARTNERS_PRISM_LIBRARY_DOWNLOAD");
        } else {
            $itprismVersion       = new Prism\Version();
            $this->itprismVersion = $itprismVersion->getShortVersion();
        }

        // Add submenu
        CrowdfundingPartnersHelper::addSubmenu($this->getName());

        $this->addToolbar();
        $this->addSidebar();
        $this->setDocument();

        parent::display($tpl);
    }

    /**
     * Add a menu on the sidebar of page
     */
    protected function addSidebar()
    {
        $this->sidebar = JHtmlSidebar::render();
    }

    /**
     * Add the page title and toolbar.
     *
     * @since   1.6
     */
    protected function addToolbar()
    {
        JToolBarHelper::title(JText::_("COM_CROWDFUNDINGPARTNERS_DASHBOARD"));

//        JToolbarHelper::preferences('com_crowdfundingpartners');
//        JToolbarHelper::divider();

        // Help button
        $bar = JToolBar::getInstance('toolbar');
        $bar->appendButton('Link', 'help', JText::_('JHELP'), JText::_('COM_CROWDFUNDINGPARTNERS_HELP_URL'));
    }

    /**
     * Method to set up the document properties
     *
     * @return void
     */
    protected function setDocument()
    {
        $this->document->setTitle(JText::_('COM_CROWDFUNDINGPARTNERS_DASHBOARD'));
    }
}
