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

class CrowdfundingViewFeatured extends JViewLegacy
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

    protected $items = null;
    protected $pagination = null;

    protected $amount;
    protected $numberInRow;
    protected $imageFolder;
    protected $displayCreator;
    protected $socialProfiles;
    protected $layoutData;

    protected $option;

    protected $pageclass_sfx;

    public function __construct($config)
    {
        parent::__construct($config);
        $this->option = JFactory::getApplication()->input->getCmd("option");
    }

    public function display($tpl = null)
    {
        // Initialise variables
        $this->state      = $this->get("State");
        $this->items      = $this->get('Items');
        $this->pagination = $this->get('Pagination');

        // Get params
        $this->params = $this->state->get("params");
        /** @var  $this->params Joomla\Registry\Registry */

        $this->numberInRow = $this->params->get("featured_items_row", 3);
        $this->items       = CrowdfundingHelper::prepareItems($this->items, $this->numberInRow);

        // Get the folder with images
        $this->imageFolder = $this->params->get("images_directory", "images/crowdfunding");

        // Get currency
        $currency     = Crowdfunding\Currency::getInstance(JFactory::getDbo(), $this->params->get("project_currency"));
        $this->amount = new Crowdfunding\Amount($this->params);
        $this->amount->setCurrency($currency);

        $this->displayCreator = $this->params->get("integration_display_creator", true);

        // Prepare integration. Load avatars and profiles.
        if (!empty($this->displayCreator)) {
            $socialProfilesBuilder = new Prism\Integration\Profiles\Builder(
                array(
                    "social_platform" => $this->params->get("integration_social_platform"),
                    "users_ids" => CrowdfundingHelper::fetchUserIds($this->items)
                )
            );

            $socialProfilesBuilder->build();

            $this->socialProfiles = $socialProfilesBuilder->getProfiles();
        }

        $this->layoutData = array(
            "items" => $this->items,
            "params" => $this->params,
            "amount" => $this->amount,
            "socialProfiles" => $this->socialProfiles,
            "imageFolder" => $this->imageFolder,
            "titleLength" => $this->params->get("discover_title_length", 0),
            "descriptionLength" => $this->params->get("discover_description_length", 0),
            "span"  => (!empty($this->numberInRow)) ? round(12 / $this->numberInRow) : 4
        );

        $this->prepareDocument();

        parent::display($tpl);
    }

    /**
     * Prepares the document
     */
    protected function prepareDocument()
    {
        // Prepare page suffix
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
            $this->params->def('page_heading', JText::_('COM_CROWDFUNDING_FEaTURED_DEFAULT_PAGE_TITLE'));
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
