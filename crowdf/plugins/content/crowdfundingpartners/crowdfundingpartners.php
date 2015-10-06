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
class plgContentCrowdfundingPartners extends JPlugin
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

    /**
     * Generate and display a list of team members on details page.
     *
     * @param string $context
     * @param object $item
     * @param Joomla\Registry\Registry $params
     *
     * @return null|string
     */
    public function onContentAfterDisplay($context, &$item, &$params)
    {
        if (strcmp("com_crowdfunding.details", $context) != 0) {
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

        $html = "";

        $partners = new CrowdfundingPartners\Partners(JFactory::getDbo());
        $partners->load($item->id);

        if (0 < count($partners)) {

            // Include the project owner to the team.
            if ($this->params->get("display_owner", 0)) {
                $user = JFactory::getUser($item->user_id);

                $owner = array(
                    "name"       => $user->get("name"),
                    "project_id" => $item->id,
                    "partner_id" => $item->user_id
                );

                $partners->add($owner);
            }

            // Get a social platform for integration
            $socialPlatform = $params->get("integration_social_platform");

            // Prepare avatars.
            $this->prepareIntegration($partners, $socialPlatform);

            // Get the path for the layout file
            $path = JPath::clean(JPluginHelper::getLayoutPath('content', 'crowdfundingpartners'));

            // Render the login form.
            ob_start();
            include $path;
            $html = ob_get_clean();
        }

        return $html;
    }

    /**
     * Generate links to a user avatar and profile.
     *
     * @param CrowdfundingPartners\Partners $partners
     * @param string $socialPlatform
     */
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
                $value["link"]   = $socialProfiles->getLink($value["partner_id"]);

                $partners[$key]  = $value;
            }

        } else { // Set default avatar

            foreach ($partners as $key => $value) {
                $value["avatar"] = $defaultAvatar;
                $value["link"]   = "";

                $partners[$key]  = $value;
            }

        }
    }
}
