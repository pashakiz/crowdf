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

class CrowdfundingViewReward extends JViewLegacy
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

    /**
     * @var CrowdfundingCurrency
     */
    protected $currency;

    protected $reward;
    protected $userId;
    protected $deliveryDate;
    protected $imagesFolder;
    protected $socialProfiles;
    protected $redirectUrl;

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
        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        // Get user ID.
        $this->userId = JFactory::getUser()->get("id");

        // Get reward ID.
        $rewardId = $app->input->getInt("id");

        // Validate reward owner
        $validator = new Crowdfunding\Validator\Reward\Owner(JFactory::getDbo(), $rewardId, $this->userId);
        if (!$validator->isValid()) {
            $app->enqueueMessage(JText::_("COM_CROWDFUNDING_ERROR_INVALID_REWARD"), "notice");
            $app->redirect(JRoute::_(CrowdfundingHelperRoute::getDiscoverRoute()));
            return;
        }

        $this->items      = $this->get('Items');
        $this->state      = $this->get('State');
        $this->pagination = $this->get('Pagination');

        // Get params
        /** @var  $params Joomla\Registry\Registry */
        $params = $this->state->get("params");
        $this->params = $params;

        // Prepare an URL where user will be redirected when change the state of a reward.
        $this->redirectUrl = "index.php?option=com_crowdfunding&view=reward&id=".$rewardId;

        // Prepare filters
        $this->listOrder = $this->escape($this->state->get('list.ordering'));
        $this->listDirn  = $this->escape($this->state->get('list.direction'));
        $this->saveOrder = (strcmp($this->listOrder, 'a.ordering') != 0) ? false : true;

        // Load reward data.
        $this->reward = new Crowdfunding\Reward(JFactory::getDbo());
        $this->reward->load($rewardId);

        // Prepare reward delivery date.
        $dateValidator = new Prism\Validator\Date($this->reward->getDeliveryDate());
        $this->deliveryDate = ($dateValidator->isValid()) ? JHtml::_('date', $this->reward->getDeliveryDate(), JText::_('DATE_FORMAT_LC3')) : "--";

        // Get images folder.
        $this->imagesFolder = CrowdfundingHelper::getImagesFolderUri($this->userId);

        // Get social profile
        $socialPlatform = $this->params->get("integration_social_platform");

        if (!empty($socialPlatform)) {
            $this->prepareSocialIntegration($socialPlatform);
        }

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
            $this->params->def('page_heading', JText::_('COM_CROWDFUNDING_REWARD_DEFAULT_PAGE_TITLE'));
        }

    }

    private function preparePageTitle()
    {
        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        // Prepare page title
        $title = JText::_('COM_CROWDFUNDING_REWARD_DEFAULT_PAGE_TITLE');

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

    protected function prepareSocialIntegration($socialPlatform)
    {
        $usersIds = array();

        // Get user ids.
        foreach ($this->items as $item) {
            $usersIds[] = $item->receiver_id;
        }

        $options = array(
            "social_platform" => $socialPlatform,
            "users_ids" => $usersIds
        );

        $profileBuilder = new Prism\Integration\Profiles\Builder($options);
        $profileBuilder->build();

        $this->socialProfiles = $profileBuilder->getProfiles();

    }
}
