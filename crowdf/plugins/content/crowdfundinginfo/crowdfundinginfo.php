<?php
/**
 * @package         Crowdfunding
 * @subpackage      Plugins
 * @author          Todor Iliev
 * @copyright       Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license         http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.plugin.plugin');

/**
 * Crowdfunding Info Plugin
 *
 * @package        Crowdfunding
 * @subpackage     Plugins
 */
class plgContentCrowdfundingInfo extends JPlugin
{
    /**
     * @var Joomla\Registry\Registry
     */
    public $params;

    /**
     * @param string    $context
     * @param object    $item
     * @param Joomla\Registry\Registry    $params
     * @param int $page
     *
     * @return null|string
     */
    public function onContentAfterDisplay($context, &$item, &$params, $page = 0)
    {
        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        if ($app->isAdmin()) {
            return null;
        }

        $doc = JFactory::getDocument();
        /**  @var $doc JDocumentHtml */

        // Check document type
        $docType = $doc->getType();
        if (strcmp("html", $docType) != 0) {
            return null;
        }

        if (strcmp("com_crowdfunding.details", $context) != 0) {
            return null;
        }

        $user = JFactory::getUser($item->user_id);

        // Load language
        $this->loadLanguage();

        if ($this->params->get("display_location", 0) or $this->params->get("display_map", 0)) {
            $location = $this->getLocation($item->location_id);
        }

        // Social Profile Integration

        $componentParams = JComponentHelper::getParams("com_crowdfunding");
        /** @var  $componentParams Joomla\Registry\Registry */

        // Check for verified user account.
        $proofVerified = false;
        if ($this->params->get("display_account_state", 0) and JComponentHelper::isEnabled("com_identityproof")) {

            jimport("IdentityProof.init");
            $proof = new IdentityProof\User(JFactory::getDbo());
            $proof->load(array("user_id" => $user->get("id")));

            if ($proof->isVerified()) {
                $proofVerified  = true;
            }
        }

        // Get profile
        $socialPlatform = $componentParams->get("integration_social_platform");
        if (!empty($socialPlatform)) { // Get social profile

            $config = array(
                "social_platform" => $socialPlatform,
                "user_id"         => $user->get("id")
            );
            $profileBuilder       = new Prism\Integration\Profile\Builder($config);
            $profileBuilder->build();

            $socialProfile         = $profileBuilder->getProfile();
            $profileLink           = $socialProfile->getLink();

            // Prepare the avatar
            $socialAvatar        = $socialProfile->getAvatar($this->params->get("image_size", "small"));
            $socialLocation      = $socialProfile->getLocation();

            if ($socialProfile->getCountryCode()) {
                $socialLocation .= ", ". $socialProfile->getCountryCode();
            }

        } else { // Set default values
            $profileLink      = "";
            $socialAvatar     = $params->get("integration_avatars_default", "/media/com_crowdfunding/images/no-profile.png");
            $socialLocation   = "";
        }

        // END Social Profile Integration

        // Preparing HTML output

        // Prepare map
        $mapCode = "";
        if ($this->params->get("display_map", 0) and !empty($location)) {
            $mapCode = $this->getMapCode($doc, $location);
        }

        // Prepare output

        // Get the path for the layout file
        $path = JPluginHelper::getLayoutPath('content', 'crowdfundinginfo', 'default');

        // Render the login form.
        ob_start();
        include $path;
        $html = ob_get_clean();

        return $html;
    }

    /**
     * @param JDocument $doc
     * @param object $location
     *
     * @return string
     */
    private function getMapCode($doc, $location)
    {
        // Set Google map API key and load the script
        $apiKey = "";
        if ($this->params->get("google_maps_key")) {
            $apiKey = "&amp;key=" . $apiKey;
        }
        $doc->addScript("//maps.googleapis.com/maps/api/js?sensor=false" . $apiKey);

        // Put the JS code that initializes the map.
        $js = '
        function initialize() {
                
            var cfLatlng = new google.maps.LatLng(' . $location->latitude . ', ' . $location->longitude . ');
                
            var map_canvas = document.getElementById("crowdf_map_canvas");
            var map_options = {
              center: cfLatlng,
              disableDefaultUI: true,
              zoom: 8,
              mapTypeId: google.maps.MapTypeId.ROADMAP
            }
            var map = new google.maps.Map(map_canvas, map_options)
                    
            var marker = new google.maps.Marker({
                position: cfLatlng,
                map: map
            });
                    
              
          }
        google.maps.event.addDomListener(window, "load", initialize);
        ';

        $doc->addScriptDeclaration($js);

        // Put the map element style
        $style =
            '#crowdf_map_canvas {
                width:  ' . $this->params->get("google_maps_width", 300) . 'px;
            height: ' . $this->params->get("google_maps_height", 300) . 'px;
        }';
        $doc->addStyleDeclaration($style);

        // Prepare the HTML code
        $code = '
            <div class="col-md-5">
                <div id="crowdf_map_canvas"></div>
            </div>';

        return $code;
    }

    private function getLocation($id)
    {
        $db = JFactory::getDbo();
        /** @var $db JDatabaseDriver */

        $query = $db->getQuery(true);
        $query
            ->select("a.name, a.latitude, a.longitude, a.country_code")
            ->from($db->quoteName("#__crowdf_locations", "a"))
            ->where("a.id = " . (int)$id)
            ->where("a.published = ". Prism\Constants::PUBLISHED);

        $db->setQuery($query);
        $result = $db->loadObject();

        return $result;
    }
}
