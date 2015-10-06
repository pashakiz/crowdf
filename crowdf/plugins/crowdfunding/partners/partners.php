<?php
/**
 * @package         CrowdfundingPartners
 * @subpackage      Plugins
 * @author          Todor Iliev
 * @copyright       Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license         http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// no direct access
defined('_JEXEC') or die;

jimport("Crowdfunding.init");
jimport("CrowdfundingPartners.init");

/**
 * Crowdfunding Partners Plugin
 *
 * @package        CrowdfundingPartners
 * @subpackage     Plugins
 */
class plgCrowdfundingPartners extends JPlugin
{
    protected $autoloadLanguage = true;

    /**
     * @var JApplicationSite
     */
    protected $app;

    /**
     * @var Joomla\Registry\Registry
     */
    public $params;

    protected $version = "2.0";

    /**
     * This method prepares a code that will be included to step "Extras" on project wizard.
     *
     * @param string    $context This string gives information about that where it has been executed the trigger.
     * @param object    $item    A project data.
     * @param Joomla\Registry\Registry $params  The parameters of the component
     *
     * @return null|string
     */
    public function onExtrasDisplay($context, &$item, &$params)
    {
        if (strcmp("com_crowdfunding.project.extras", $context) != 0) {
            return null;
        }

        if ($this->app->isAdmin()) {
            return null;
        }

        $doc = JFactory::getDocument();
        /**  @var $doc JDocumentHtml */

        // Check document type
        $docType = $doc->getType();
        if (strcmp("html", $docType) != 0) {
            return null;
        }
        
        if (empty($item->user_id)) {
            return null;
        }

        $partners = new CrowdfundingPartners\Partners(JFactory::getDbo());
        $partners->load($item->id);

        // Get a social platform for integration
        $socialPlatform = $params->get("integration_social_platform");

        // Prepare avatars.
        $this->prepareIntegration($partners, $socialPlatform);

        // Load jQuery
        JHtml::_("jquery.framework");
        JHtml::_("prism.ui.pnotify");
        JHtml::_('prism.ui.joomlaHelper');

        // Include the translation of the confirmation question.
        JText::script('PLG_CROWDFUNDING_PARTNERS_DELETE_QUESTION');

        // Get the path for the layout file
        $path = JPath::clean(JPluginHelper::getLayoutPath('crowdfunding', 'partners'));

        // Render the login form.
        ob_start();
        include $path;
        $html = ob_get_clean();

        return $html;
    }
    
    protected function prepareIntegration($partners, $socialPlatform)
    {
        $defaultAvatar = "media/com_crowdfunding/images/no-profile.png";

        if (!empty($socialPlatform)) {
            $usersIds = array();

            foreach ($partners as $partner) {
                $usersIds[] = $partner["partner_id"];
            }

            $config = array(
                "social_platform" => $socialPlatform,
                "users_ids" => $usersIds
            );

            $socialProfilesBuilder = new Prism\Integration\Profiles\Builder($config);
            $socialProfilesBuilder->build();

            $socialProfiles = $socialProfilesBuilder->getProfiles();

            foreach ($partners as $key => $value) {
                $value["avatar"] = $socialProfiles->getAvatar($value["partner_id"], $this->params->get("image_size", "small"));
                $partners[$key] = $value;
            }

        } else { // Set default avatar

            foreach ($partners as $key => $value) {
                $value["avatar"] = $defaultAvatar;
                $partners[$key]  = $value;
            }

        }
    }
}
