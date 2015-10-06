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

    protected $item;
    protected $form;

    protected $rewardsImagesUri;
    protected $allowedImages;
    protected $projectTitle;
    protected $rewards;
    protected $deliveryDate;
    protected $socialProfile;
    protected $profileLink;
    protected $imagesFolder;
    protected $rewardOwnerId;
    protected $returnUrl;

    protected $documentTitle;
    protected $option;
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

        $this->params     = $this->state->get("params");

        // Get rewards images URI.
        if (!empty($this->item->id)) {
            $userId = CrowdfundingHelper::getUserIdByRewardId($this->item->id);
            $uri = JUri::getInstance();
            $this->rewardsImagesUri = $uri->toString(array("scheme", "host")) . "/" . CrowdfundingHelper::getImagesFolderUri($userId, JPATH_BASE);
        }

        $app = JFactory::getApplication();
        /** @var  $app JApplicationAdministrator */

        // Get project title.
        $projectId = $app->getUserState("com_crowdfunding.rewards.pid");
        $this->projectTitle = CrowdfundingHelper::getProjectTitle($projectId);

        // Get a property that give us ability to upload images.
        $this->allowedImages = $this->params->get("rewards_images", 0);

        $this->layout = $this->getLayout();

        if (strcmp("default", $this->layout) == 0) {
            $this->prepareDefaultLayout();
        }
        
        // Prepare actions, behaviors, scripts and document.
        $this->addToolbar();
        $this->setDocument();

        parent::display($tpl);
    }

    protected function prepareDefaultLayout()
    {
        $this->rewards = new Crowdfunding\User\Rewards(JFactory::getDbo());
        $this->rewards->load(array("reward_id" => $this->item->id));

        $this->rewardOwnerId = CrowdfundingHelper::getUserIdByRewardId($this->item->id);

        $dateValidator = new Prism\Validator\Date($this->item->delivery);
        $this->deliveryDate = ($dateValidator->isValid()) ? JHtml::_('date', $this->item->delivery, JText::_('DATE_FORMAT_LC3')) : "--";

        $this->imagesFolder = CrowdfundingHelper::getImagesFolderUri($this->rewardOwnerId);

        // Get social profile
        $socialPlatform = $this->params->get("integration_social_platform");

        if (!empty($socialPlatform)) {
            $options = array(
                "social_platform" => $socialPlatform,
                "user_id" => $this->rewardOwnerId
            );

            $profileBuilder = new Prism\Integration\Profile\Builder($options);
            $profileBuilder->build();

            $this->socialProfile = $profileBuilder->getProfile();
            $this->profileLink   = $this->socialProfile->getLink();
        }

        $this->returnUrl = base64_encode("index.php?option=com_crowdfunding&view=reward&id=".$this->item->id);
    }

    /**
     * Add the page title and toolbar.
     *
     * @since   1.6
     */
    protected function addToolbar()
    {
        if (strcmp("default", $this->layout) != 0) { // Layout "edit".

            JFactory::getApplication()->input->set('hidemainmenu', true);
            $isNew = ($this->item->id == 0);

            $this->documentTitle = $isNew ?
                JText::sprintf('COM_CROWDFUNDING_NEW_REWARD', $this->projectTitle) :
                JText::sprintf('COM_CROWDFUNDING_EDIT_REWARD', $this->projectTitle);

            JToolbarHelper::title($this->documentTitle);

            JToolbarHelper::apply('reward.apply');
            JToolbarHelper::save2new('reward.save2new');
            JToolbarHelper::save('reward.save');

            if (!$isNew) {
                JToolbarHelper::cancel('reward.cancel', 'JTOOLBAR_CANCEL');
            } else {
                JToolbarHelper::cancel('reward.cancel', 'JTOOLBAR_CLOSE');
            }

        } else { // Layout 'default'.
            $this->documentTitle = JText::sprintf('COM_CROWDFUNDING_VIEW_REWARD_S_PROJECT_S', $this->item->title, $this->projectTitle);
            JToolbarHelper::title($this->documentTitle);
            JToolbarHelper::cancel('reward.cancel', 'JTOOLBAR_CLOSE');
        }
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
        JHtml::_('behavior.keepalive');
        JHtml::_('behavior.formvalidation');
        JHtml::_('behavior.tooltip');
        JHtml::_('prism.ui.bootstrap2FileInput');

        JHtml::_('formbehavior.chosen', 'select');

        // Add scripts
        $this->document->addScript('../media/' . $this->option . '/js/admin/' . Joomla\String\String::strtolower($this->getName()) . '.js');
    }
}
