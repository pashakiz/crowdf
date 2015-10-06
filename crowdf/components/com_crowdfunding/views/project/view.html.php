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

class CrowdfundingViewProject extends JViewLegacy
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

    protected $form;
    protected $item;
    protected $items;

    protected $amount;
    protected $currency;
    protected $userId;
    protected $disabledButton;
    protected $layout;
    protected $debugMode;
    protected $rewardsEnabled = true;
    protected $rewardsEnabledViaType = true;
    protected $article;
    protected $pathwayName;
    protected $numberOfTypes;
    protected $isNew;
    protected $imageFolder;
    protected $minAmount;
    protected $maxAmount;
    protected $minDays;
    protected $maxDays;
    protected $checkedDate;
    protected $checkedDays;
    protected $pitchImage;
    protected $pWidth;
    protected $pHeight;
    protected $imageSmall;
    protected $fundingDuration;
    protected $dateFormat;
    protected $dateFormatCalendar;
    protected $rewardsImagesEnabled;
    protected $rewardsImagesUri;
    protected $projectId;
    protected $images;
    protected $rewards;

    protected $imageWidth;
    protected $imageHeight;
    protected $titleLength;
    protected $descriptionLength;
    protected $returnUrl;
    protected $isImageExists = false;
    protected $imagePath;
    protected $displayRemoveButton = "none";

    protected $wizardType;
    protected $layoutData = array();
    protected $sixSteps = false;
    protected $statistics = array();

    protected $option;

    protected $pageclass_sfx;

    /**
     * @var JApplicationSite
     */
    protected $app;

    public function __construct($config)
    {
        parent::__construct($config);

        $this->app    = JFactory::getApplication();
        $this->option = $this->app->input->getCmd("option");
    }

    /**
     * Display the view.
     *
     * @param mixed $tpl
     *
     * @return string
     */
    public function display($tpl = null)
    {
        $this->userId = JFactory::getUser()->get("id");
        if (!$this->userId) {
            $this->setLayout("intro");
        }

        // Get component params.
        $this->params = $this->app->getParams($this->option);

        $this->rewardsEnabled = (bool)$this->params->get("rewards_enabled", 1);
        $this->disabledButton = "";

        $this->layout = $this->getLayout();

        switch ($this->layout) {

            case "funding":
                $this->prepareFunding();
                break;

            case "story":
                $this->prepareStory();
                break;

            case "rewards":
                $this->prepareRewards();
                break;

            case "extras":
                $this->prepareExtras();
                break;

            case "manager":
                $this->prepareManager();
                break;

            case "intro":
                $this->prepareIntro();
                break;

            default: // Basic data for project
                $this->prepareBasic();
                break;
        }

        // Get wizard type
        $this->wizardType     = $this->params->get("project_wizard_type", "five_steps");
        $this->sixSteps       = (strcmp("six_steps", $this->wizardType) != 0) ? false : true;

        $this->layoutData = array(
            "layout"  => $this->layout,
            "item_id" => (!empty($this->item->id)) ? $this->item->id : 0,
            "rewards_enabled" => $this->rewardsEnabled
        );

        $this->prepareDebugMode();
        $this->prepareDocument();

        parent::display($tpl);
    }

    /**
     * Check the system for debug mode
     */
    protected function prepareDebugMode()
    {
        // Check for maintenance (debug) state
        $params          = $this->state->get("params");
        $this->debugMode = $params->get("debug_project_adding_disabled", 0);
        if ($this->debugMode) {
            $msg = Joomla\String\String::trim($params->get("debug_disabled_functionality_msg"));
            if (!$msg) {
                $msg = JText::_("COM_CROWDFUNDING_DEBUG_MODE_DEFAULT_MSG");
            }
            $this->app->enqueueMessage($msg, "notice");

            $this->disabledButton = 'disabled="disabled"';
        }
    }

    /**
     * Check the system for debug mode.
     */
    protected function prepareProjectType()
    {
        $this->rewardsEnabledViaType = true;

        if (!empty($this->item->type_id)) {
            $type = new Crowdfunding\Type(JFactory::getDbo());
            $type->load($this->item->type_id);

            if ($type->getId() and !$type->isRewardsEnabled()) {
                $this->rewardsEnabledViaType = false;
                $this->disabledButton = 'disabled="disabled"';
            }
        }
    }

    /**
     * Display default page
     */
    protected function prepareIntro()
    {
        $model        = JModelLegacy::getInstance("Intro", "CrowdfundingModel", $config = array('ignore_request' => false));
        /** @var $model CrowdfundingModelIntro */

        // Get state
        /** @var  $state Joomla\Registry\Registry */
        $state = $model->getState();
        $this->state = $state;

        // Get params
        /** @var  $params Joomla\Registry\Registry */
        $params = $this->state->get("params");
        $this->params = $params;

        $articleId     = $this->params->get("project_intro_article", 0);
        $this->article = $model->getItem($articleId);

        $this->pathwayName = JText::_("COM_CROWDFUNDING_START_PROJECT_BREADCRUMB");
    }

    protected function prepareBasic()
    {
        $model = JModelLegacy::getInstance("Project", "CrowdfundingModel", $config = array('ignore_request' => false));
        /** @var $model CrowdfundingModelProject */

        // Get state
        $this->state = $model->getState();
        /** @var  $this->state Joomla\Registry\Registry */

        // Get params
        $this->params = $this->state->get("params");
        /** @var  $this->params Joomla\Registry\Registry */

        // Get item
        $itemId     = $this->state->get('project.id');
        $this->item = $model->getItem($itemId, $this->userId);

        // Set a flag that describes the item as new.
        $this->isNew = false;
        if (!$this->item->id) {
            $this->isNew = true;
        }

        $this->form = $model->getForm();

        // Get types
        $types               = Crowdfunding\Types::getInstance(JFactory::getDbo());
        $this->numberOfTypes = count($types);

        // Prepare images
        $this->imageFolder = $this->params->get("images_directory", "images/crowdfunding");

        if (!$this->item->get("image")) {
            $this->imagePath     = "media/com_crowdfunding/images/no_image.png";
            $this->displayRemoveButton = "none";
        } else {
            $this->imagePath     = $this->imageFolder."/".$this->item->get("image");
            $this->displayRemoveButton = "inline";
        }

        $this->pathwayName = JText::_("COM_CROWDFUNDING_STEP_BASIC");

        // Remove the temporary pictures if they exists.
        $this->removeTemporaryImages($model);
    }

    protected function prepareFunding()
    {
        $model = JModelLegacy::getInstance("Funding", "CrowdfundingModel", $config = array('ignore_request' => false));
        /** @var $model CrowdfundingModelFunding */

        // Get state
        $this->state = $model->getState();
        /** @var  $state Joomla\Registry\Registry */

        // Get item
        $itemId     = $this->state->get('funding.id');
        $this->item = $model->getItem($itemId, $this->userId);

        // Check if the item exists.
        if (!$this->isValid()) {
            return;
        }

        $this->form = $model->getForm();

        // Get currency
        $currency     = Crowdfunding\Currency::getInstance(JFactory::getDbo(), $this->params->get("project_currency"));
        $this->amount = new Crowdfunding\Amount($this->params);
        $this->amount->setCurrency($currency);

        // Set minimum values - days, amount,...
        $this->minAmount = $this->params->get("project_amount_minimum", 100);
        $this->maxAmount = $this->params->get("project_amount_maximum");

        $this->minDays = $this->params->get("project_days_minimum", 30);
        $this->maxDays = $this->params->get("project_days_maximum");

        // Prepare funding duration type
        $this->prepareFundingDurationType();

        $this->pathwayName = JText::_("COM_CROWDFUNDING_STEP_FUNDING");
    }

    protected function prepareFundingDurationType()
    {
        $this->fundingDuration = $this->params->get("project_funding_duration");

        switch ($this->fundingDuration) {

            case "days": // Only days type is enabled
                $this->checkedDays = 'checked="checked"';
                break;

            case "date": // Only date type is enabled
                $this->checkedDate = 'checked="checked"';
                break;

            default: // Both ( days and date ) types are enabled

                $this->checkedDays = 0;
                $this->checkedDate = "";

                $dateValidator = new Prism\Validator\Date($this->item->funding_end);

                if (!empty($this->item->funding_days)) {
                    $this->checkedDays = 'checked="checked"';
                    $this->checkedDate = '';
                } elseif ($dateValidator->isValid($this->item->funding_end)) {
                    $this->checkedDays = '';
                    $this->checkedDate = 'checked="checked"';
                }

                // If missing both, select days.
                if (!$this->checkedDays and !$this->checkedDate) {
                    $this->checkedDays = 'checked="checked"';
                }
                break;

        }
    }

    protected function prepareStory()
    {
        $model = JModelLegacy::getInstance("Story", "CrowdfundingModel", $config = array('ignore_request' => false));
        /** @var $model CrowdfundingModelStory */

        // Get state
        $this->state = $model->getState();
        /** @var  $state Joomla\Registry\Registry */

        // Get item
        $itemId     = $this->state->get('story.id');
        $this->item = $model->getItem($itemId, $this->userId);

        // Check if the item exists.
        if (!$this->isValid()) {
            return;
        }

        $this->form   = $model->getForm();

        $this->imageFolder = $this->params->get("images_directory", "images/crowdfunding");
        $this->pitchImage  = $this->item->get("pitch_image");

        $this->pWidth  = $this->params->get("pitch_image_width", 600);
        $this->pHeight = $this->params->get("pitch_image_height", 400);

        $this->pathwayName = JText::_("COM_CROWDFUNDING_STEP_STORY");
    }

    protected function prepareRewards()
    {
        $model           = JModelLegacy::getInstance("Rewards", "CrowdfundingModel", $config = array('ignore_request' => false));

        // Get state
        $this->state     = $model->getState();

        // Get params
        $this->projectId = $this->state->get("rewards.project_id");

        // Check if rewards are enabled.
        if (!$this->rewardsEnabled) {
            $this->app->enqueueMessage(JText::_("COM_CROWDFUNDING_ERROR_REWARDS_DISABLED"), "notice");
            $this->app->redirect(JRoute::_(CrowdfundingHelperRoute::getFormRoute($this->projectId, "manager"), false));
            return;
        }

        $this->items     = $model->getItems($this->projectId);

        // Get project and validate it
        $project = Crowdfunding\Project::getInstance(JFactory::getDbo(), $this->projectId);
        $project = $project->getProperties();

        $this->item = Joomla\Utilities\ArrayHelper::toObject($project);

        // Check if the item exists.
        if (!$this->isValid()) {
            return;
        }

        // Create a currency object.
        $this->currency = Crowdfunding\Currency::getInstance(JFactory::getDbo(), $this->params->get("project_currency"));
        $this->amount   = new Crowdfunding\Amount($this->params);
        $this->amount->setCurrency($this->currency);

        // Get date format
        $this->dateFormat         = CrowdfundingHelper::getDateFormat();
        $this->dateFormatCalendar = CrowdfundingHelper::getDateFormat(true);

        $language = JFactory::getLanguage();
        $languageTag = $language->getTag();

        $js = '
            // Rewards calendar date format.
            var projectWizard = {
                dateFormat: "' . $this->dateFormatCalendar . '",
                locale: "'. substr($languageTag, 0, 2) .'"
            };
        ';
        $this->document->addScriptDeclaration($js);

        // Prepare rewards images.
        $this->rewardsImagesEnabled = $this->params->get("rewards_images", 0);
        $this->rewardsImagesUri     = CrowdfundingHelper::getImagesFolderUri($this->userId);

        $this->prepareProjectType();

        $this->pathwayName = JText::_("COM_CROWDFUNDING_STEP_REWARDS");
    }

    protected function prepareManager()
    {
        $model = JModelLegacy::getInstance("Manager", "CrowdfundingModel", $config = array('ignore_request' => false));
        /** @var $model CrowdfundingModelManager */

        // Get state
        $this->state = $model->getState();
        /** @var  $state Joomla\Registry\Registry */

        $this->imageWidth        = $this->params->get("image_width", 200);
        $this->imageHeight       = $this->params->get("image_height", 200);
        $this->titleLength       = $this->params->get("discover_title_length", 0);
        $this->descriptionLength = $this->params->get("discover_description_length", 0);

        // Get the folder with images
        $this->imageFolder = $this->params->get("images_directory", "images/crowdfunding");

        // Filter the URL.
        $uri = JUri::getInstance();

        $filter    = JFilterInput::getInstance();
        $this->returnUrl = $filter->clean($uri->toString());

        // Get item
        $itemId     = $this->state->get('manager.id');

        // Get currency
        $currency     = Crowdfunding\Currency::getInstance(JFactory::getDbo(), $this->params->get("project_currency"));
        $this->amount = new Crowdfunding\Amount($this->params);
        $this->amount->setCurrency($currency);

        $this->item = $model->getItem($itemId, $this->userId);

        // Check if the item exists.
        if (!$this->isValid()) {
            return;
        }

        $statistics = new Crowdfunding\Statistics\Project(JFactory::getDbo(), $this->item->id);
        $this->statistics = array(
            "updates"  => $statistics->getUpdatesNumber(),
            "comments" => $statistics->getCommentsNumber(),
            "funders"  => $statistics->getTransactionsNumber(),
        );

        // Get rewards
        $this->rewards = new Crowdfunding\Rewards(JFactory::getDbo());
        $this->rewards->load(array("project_id" => $this->item->id));

        $this->pathwayName = JText::_("COM_CROWDFUNDING_STEP_MANAGER");
    }

    protected function prepareExtras()
    {
        $model = JModelLegacy::getInstance("Extras", "CrowdfundingModel", $config = array('ignore_request' => false));
        /** @var $model CrowdfundingModelManager */

        // Get state
        /** @var  $state Joomla\Registry\Registry */
        $this->state = $model->getState();

        // Get item
        $itemId     = $this->state->get('extras.id');
        $this->item = $model->getItem($itemId, $this->userId);

        // Check if the item exists.
        if (!$this->isValid()) {
            return;
        }

        $this->pathwayName = JText::_("COM_CROWDFUNDING_STEP_EXTRAS");

        // Events
        JPluginHelper::importPlugin('crowdfunding');
        $dispatcher = JEventDispatcher::getInstance();
        $results    = $dispatcher->trigger('onExtrasDisplay', array('com_crowdfunding.project.extras', &$this->item, &$this->params));

        $this->item->event                   = new stdClass();
        $this->item->event->onExtrasDisplay = trim(implode("\n", $results));
    }

    /**
     * Prepares the document
     */
    protected function prepareDocument()
    {
        // Prepare page suffix
        $this->pageclass_sfx = htmlspecialchars($this->params->get('pageclass_sfx'));

        $menus = $this->app->getMenu();

        // Because the application sets a default page title,
        // we need to get it from the menu item itself
        $menu = $menus->getActive();

        if ($menu) {
            $this->params->def('page_heading', $menu->title);
        } else {
            $this->params->def('page_heading', JText::_('COM_CROWDFUNDING_RAISE_DEFAULT_PAGE_TITLE'));
        }

        // Prepare page title
        $title = $menu->title;
        if (!$title) {
            $title = $this->app->get('sitename');

        // Set site name if it is necessary ( the option 'sitename' = 1 )
        } elseif ($this->app->get('sitename_pagetitles', 0)) {
            $title = JText::sprintf('JPAGETITLE', $this->app->get('sitename'), $title);

        // Item title to the browser title.
        } else {
            if (!empty($this->item)) {
                $title .= " | " . $this->escape($this->item->title);
            }
        }

        $this->document->setTitle($title);

        // Meta Description
        $this->document->setDescription($this->params->get('menu-meta_description'));

        // Meta keywords
        $this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));

        // Add current layout into breadcrumbs.
        $pathway = $this->app->getPathway();
        $pathway->addItem($this->pathwayName);

        // Scripts
        if ($this->userId) {
            JHtml::_('behavior.core');
            JHtml::_('behavior.keepalive');

            if ($this->params->get("enable_chosen", 1)) {
                JHtml::_('formbehavior.chosen', '.cf-advanced-select');
            }
        }

        switch ($this->layout) {

            case "rewards":

                // Load language string in JavaScript
                JText::script('COM_CROWDFUNDING_QUESTION_REMOVE_REWARD');
                JText::script('COM_CROWDFUNDING_QUESTION_REMOVE_IMAGE');
                JText::script('COM_CROWDFUNDING_PICK_IMAGE');

                // Scripts

                if ($this->params->get("rewards_images", 0)) {
                    JHtml::_('prism.ui.bootstrap3Fileinput');
                }

                JHtml::_('prism.ui.pnotify');
                JHtml::_("prism.ui.joomlaHelper");
                $this->document->addScript('media/' . $this->option . '/js/site/project_rewards.js');

                break;

            case "funding":
                JHtml::_('prism.ui.parsley');
                JHtml::_('prism.ui.bootstrap3Datepicker');

                $this->document->addScript('media/' . $this->option . '/js/site/project_funding.js');

                // Load language string in JavaScript
                JText::script('COM_CROWDFUNDING_THIS_VALUE_IS_REQUIRED');

                break;

            case "story":

                // Scripts
                JHtml::_('prism.ui.bootstrap3FileInput');

                // Include translation of the confirmation question for image removing.
                JText::script('COM_CROWDFUNDING_QUESTION_REMOVE_IMAGE');
                JText::script('COM_CROWDFUNDING_PICK_IMAGE');
                JText::script('COM_CROWDFUNDING_REMOVE');

                $this->document->addScript('media/' . $this->option . '/js/site/project_story.js');

                break;

            case "manager":

                $this->document->addScript('media/' . $this->option . '/js/site/project_manager.js');

                // Load language string in JavaScript
                JText::script('COM_CROWDFUNDING_QUESTION_LAUNCH_PROJECT');
                JText::script('COM_CROWDFUNDING_QUESTION_STOP_PROJECT');

                break;

            case "extras":

                break;

            default: // Basic

                if ($this->userId) {

                    JHtml::_('prism.ui.bootstrapMaxLength');
                    JHtml::_('prism.ui.bootstrap3Typeahead');
                    JHtml::_('prism.ui.parsley');
                    JHtml::_('prism.ui.cropper');
                    JHtml::_('prism.ui.fileupload');
                    JHtml::_('prism.ui.pnotify');
                    JHtml::_("prism.ui.joomlaHelper");

                    $this->document->addScript('media/' . $this->option . '/js/site/project_basic.js');

                    // Load language string in JavaScript
                    JText::script('COM_CROWDFUNDING_QUESTION_REMOVE_IMAGE');

                    // Provide image size.
                    $js = "
                    var cfImageWidth  = " . $this->params->get("image_width", 200) . ";
                    var cfImageHeight = " . $this->params->get("image_height", 200) . ";
                ";

                    $this->document->addScriptDeclaration($js);
                }
                break;
        }
    }

    /**
     * Check if item exists.
     *
     * @return bool
     */
    protected function isValid()
    {
        if (!$this->item->id or ($this->item->user_id != $this->userId)) {
            $this->app->enqueueMessage(JText::_("COM_CROWDFUNDING_ERROR_SOMETHING_WRONG"), "notice");
            $this->app->redirect(JRoute::_(CrowdfundingHelperRoute::getDiscoverRoute()));
            return false;
        }

        return true;
    }

    /**
     * Remove the temporary images if a user upload or crop a picture,
     * but he does not store it or reload the page.
     *
     * @param CrowdfundingModelProject $model
     */
    protected function removeTemporaryImages($model)
    {
        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        // Remove old image if it exists.
        $oldImage = $app->getUserState(Crowdfunding\Constants::TEMPORARY_IMAGE_CONTEXT);
        if (!empty($oldImage)) {
            $temporaryFolder = CrowdfundingHelper::getTemporaryImagesFolder();
            $oldImage = JPath::clean($temporaryFolder . "/" . basename($oldImage));
            if (JFile::exists($oldImage)) {
                JFile::delete($oldImage);
            }
        }

        // Set the name of the image in the session.
        $app->setUserState(Crowdfunding\Constants::TEMPORARY_IMAGE_CONTEXT, null);

        // Remove the temporary images if they exist.
        $temporaryImages = $app->getUserState(Crowdfunding\Constants::CROPPED_IMAGES_CONTEXT);
        if (!empty($temporaryImages)) {
            $temporaryFolder = CrowdfundingHelper::getTemporaryImagesFolder();
            $model->removeTemporaryImages($temporaryImages, $temporaryFolder);
        }

        // Reset the temporary images.
        $app->setUserState(Crowdfunding\Constants::CROPPED_IMAGES_CONTEXT, null);
    }
}
