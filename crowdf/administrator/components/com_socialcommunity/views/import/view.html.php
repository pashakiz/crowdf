<?php
/**
 * @package      SocialCommunity
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// No direct access
defined('_JEXEC') or die;

class SocialCommunityViewImport extends JViewLegacy
{

    /**
     * @var JDocumentHtml
     */
    public $document;

    /**
     * @var Joomla\Registry\Registry
     */
    protected $state;

    protected $form;

    protected $option;

    protected $importType;
    protected $uploadTask;
    protected $legend;

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
        $this->form  = $this->get('Form');

        $this->importType = $this->state->get("import.context");

        switch ($this->importType) {
            case "locations":
                $this->legend     = JText::_("COM_SOCIALCOMMUNITY_IMPORT_LOCATIONS_DATA");
                $this->uploadTask = "import.locations";
                break;

            case "countries":
                $this->legend     = JText::_("COM_SOCIALCOMMUNITY_IMPORT_COUNTRIES_DATA");
                $this->uploadTask = "import.countries";
                break;

            case "states":
                $this->legend     = JText::_("COM_SOCIALCOMMUNITY_IMPORT_STATES_DATA");
                $this->uploadTask = "import.states";
                break;

            default: // Currencies
                $this->legend     = JText::_("COM_SOCIALCOMMUNITY_IMPORT_CURRENCY_DATA");
                $this->uploadTask = "import.currencies";
                break;

        }

        // Add submenu
        SocialCommunityHelper::addSubmenu($this->importType);

        // Prepare actions
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
        // Set toolbar items for the page
        JToolbarHelper::title(JText::_('COM_SOCIALCOMMUNITY_IMPORT_MANAGER'));

        // Upload
        JToolbarHelper::custom($this->uploadTask, "upload", "", JText::_("COM_SOCIALCOMMUNITY_UPLOAD"), false);

        JToolbarHelper::divider();
        JToolbarHelper::cancel('import.cancel', 'JTOOLBAR_CANCEL');

    }

    /**
     * Method to set up the document properties
     *
     * @return void
     */
    protected function setDocument()
    {
        $this->document->setTitle(JText::_('COM_SOCIALCOMMUNITY_IMPORT_MANAGER'));

        // Scripts
        JHtml::_('behavior.formvalidation');

        JHtml::_('bootstrap.tooltip');
        JHtml::_('prism.ui.bootstrap2FileInput');

        $this->document->addScript('../media/' . $this->option . '/js/admin/' . JString::strtolower($this->getName()) . '.js');
    }
}
