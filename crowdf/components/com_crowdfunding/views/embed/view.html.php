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

class CrowdfundingViewEmbed extends JViewLegacy
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

    protected $amount;
    protected $imageFolder;
    protected $embedLink;
    protected $socialProfileLink;
    protected $displayCreator;
    protected $embedCode;
    protected $form;

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

        // Get model state.
        $this->state = $this->get('State');
        $this->item  = $this->get("Item");

        // Get params
        /** @var  $params Joomla\Registry\Registry */
        $params = $this->state->get("params");
        $this->params = $params;

        $this->imageFolder = $this->params->get("images_directory", "images/crowdfunding");

        if (!$this->item) {
            $app->enqueueMessage(JText::_("COM_CROWDFUNDING_ERROR_INVALID_PROJECT"), "notice");
            $app->redirect(JRoute::_(CrowdfundingHelperRoute::getDiscoverRoute(), false));
            return;
        }

        // Get currency
        // Get currency
        $currency     = Crowdfunding\Currency::getInstance(JFactory::getDbo(), $this->params->get("project_currency"));
        $this->amount = new Crowdfunding\Amount($this->params);
        $this->amount->setCurrency($currency);

        // Integrate with social profile.
        $this->displayCreator = $this->params->get("integration_display_creator", true);

        // Prepare integration. Load avatars and profiles.
        if ($this->displayCreator and !empty($this->item->user_id)) {
            $socialProfilesBuilder = new Prism\Integration\Profile\Builder(
                array(
                    "social_platform" => $this->params->get("integration_social_platform"),
                    "user_id" => $this->item->user_id
                )
            );
            $socialProfilesBuilder->build();

            $socialProfile = $socialProfilesBuilder->getProfile();
            $this->socialProfileLink  = (!$socialProfile) ? null : $socialProfile->getLink();
        }

        // Set a link to project page
        $uri              = JUri::getInstance();
        $host             = $uri->toString(array("scheme", "host"));
        $this->item->link = $host . JRoute::_(CrowdfundingHelperRoute::getDetailsRoute($this->item->slug, $this->item->catslug), false);

        // Set a link to image
        $this->item->link_image = $host . "/" . $this->imageFolder . "/" . $this->item->image;

        $layout = $this->getLayout();
        switch ($layout) {

            case "email":

                if (!$this->params->get("security_display_friend_form", 0)) {
                    $app->enqueueMessage(JText::_("COM_CROWDFUNDING_ERROR_CANT_SEND_MAIL"), "notice");
                    $app->redirect(JRoute::_($this->item->link, false));

                    return;
                }

                $this->prepareEmailForm($this->item);

                break;

            default: // Embed HTML code
                $this->embedCode = $this->prepareEmbedCode($this->item, $host);
                break;

        }

        $this->prepareDocument();

        parent::display($tpl);
    }

    /**
     * Generate HTML code for embeding.
     *
     * @param object $item
     * @param string $host
     *
     * @return string
     */
    protected function prepareEmbedCode($item, $host)
    {
        // Generate embed link
        $embedLink = $host . JRoute::_(CrowdfundingHelperRoute::getEmbedRoute($item->slug, $item->catslug) . "&layout=widget&tmpl=component", false);

        return '<iframe src="' . $embedLink . '" width="280px" height="560px" frameborder="0" scrolling="no"></iframe>';
    }

    /**
     * Display a form that will be used for sending mail to friend
     *
     * @param object $item
     */
    protected function prepareEmailForm($item)
    {
        $model = JModelLegacy::getInstance("FriendMail", "CrowdfundingModel", $config = array('ignore_request' => false));

        // Prepare default content of the form
        $formData = array(
            "id"      => $item->id,
            "subject" => JText::sprintf("COM_CROWDFUNDING_SEND_FRIEND_DEFAULT_SUBJECT", $item->title),
            "message" => JText::sprintf("COM_CROWDFUNDING_SEND_FRIEND_DEFAULT_MESSAGE", $item->link)
        );

        // Set user data
        $user = JFactory::getUser();
        if (!empty($user->id)) {
            $formData["sender_name"] = $user->name;
            $formData["sender"]      = $user->email;
        }

        $this->form = $model->getForm($formData);

        // Scripts
        JHtml::_('behavior.tooltip');
        JHtml::_('behavior.formvalidation');
    }

    /**
     * Prepare the document
     */
    protected function prepareDocument()
    {
        $app = JFactory::getApplication();
        /** @var $app JApplicationSite * */

        // Escape strings for HTML output
        $this->pageclass_sfx = htmlspecialchars($this->params->get('pageclass_sfx'));

        // Prepare page heading
        $this->preparePageHeading();

        // Prepare page heading
        $this->preparePageTitle();

        if ($this->params->get('menu-meta_description')) {
            $this->document->setDescription($this->params->get('menu-meta_description'));
        } else {
            $this->document->setDescription($this->item->short_desc);
        }

        if ($this->params->get('menu-meta_keywords')) {
            $this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
        }

        if ($this->params->get('robots')) {
            $this->document->setMetadata('robots', $this->params->get('robots'));
        }

        // Breadcrumb
        $pathway           = $app->getPathWay();
        $currentBreadcrumb = JHtmlString::truncate($this->item->title, 16);
        $pathway->addItem($currentBreadcrumb, '');

        // Add scripts
        JHtml::_('jquery.framework');
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
            $this->params->def('page_heading', JText::sprintf('COM_CROWDFUNDING_DETAILS_DEFAULT_PAGE_TITLE', $this->item->title));
        }
    }

    private function preparePageTitle()
    {
        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        // Prepare page title
        $layout = $this->getLayout();
        if (strcmp("email", $layout) == 0) {
            $title = $this->item->title . " | " . JText::_("COM_CROWDFUNDING_EMAIL_TO_FRIEND");
        } else {
            $title = $this->item->title . " | " . JText::_("COM_CROWDFUNDING_EMBED_CODE");
        }

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
