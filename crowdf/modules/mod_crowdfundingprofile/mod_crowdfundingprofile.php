<?php
/**
 * @package      Crowdfunding
 * @subpackage   Modules
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// no direct access
defined("_JEXEC") or die;

$moduleclassSfx = htmlspecialchars($params->get('moduleclass_sfx'));

jimport("Prism.init");
jimport("Crowdfunding.init");
JLoader::register("CrowdfundingProfileModuleHelper", JPATH_ROOT . "/modules/mod_crowdfundingprofile/helper.php");

$option = $app->input->get("option");
$view   = $app->input->get("view");

// If option is not "com_crowdfunding" and view is not "details",
// do not display anything.
if ((strcmp($option, "com_crowdfunding") != 0) or (strcmp($view, "details") != 0)) {
    echo JText::_("MOD_CROWDFUNDINGPROFILE_ERROR_INVALID_VIEW");
    return;
}

$projectId = $app->input->getInt("id");
if (!$projectId) {
    echo JText::_("MOD_CROWDFUNDINGPROFILE_ERROR_INVALID_PROJECT");
    return;
}

// Get data about user.
$profile = CrowdfundingProfileModuleHelper::getData($projectId);

// Get component parameters
$componentParams = JComponentHelper::getParams("com_crowdfunding");
/** @var  $componentParams Joomla\Registry\Registry */

// Create profile object.
$socialProfiles = null;

// Get a social platform for integration.
$socialPlatform = $componentParams->get("integration_social_platform");

$imageSize = $params->get("image_size", "small");
$imageLink = $params->get("image_link", true);

$profileImage = null;
$profileLink  = null;
$profileLocation  = null;
$profileCountryCode  = null;

if (!empty($socialPlatform)) {

    $config = array(
        "social_platform" => $socialPlatform,
        "user_id" => $profile["user_id"]
    );

    $socialProfileBuilder = new Prism\Integration\Profile\Builder($config);
    $socialProfileBuilder->build();

    $socialProfile = $socialProfileBuilder->getProfile();

    if (!empty($socialProfile)) {
        $profileImage = $socialProfile->getAvatar($imageSize);
        $profileLink  = $socialProfile->getLink();
        $profileLocation  = $socialProfile->getLocation();
        $profileCountryCode  = $socialProfile->getCountryCode();
    }
}

$proofVerified = false;
if ($params->get("display_account_state", 0) and JComponentHelper::isEnabled("com_identityproof")) {

    jimport("IdentityProof.init");
    $proof = new IdentityProof\User(JFactory::getDbo());
    $proof->load(array("user_id" => $profile["user_id"]));

    if ($proof->isVerified()) {
        $proofVerified  = true;
    }
}

if (!empty($socialProfile)) {
    require JModuleHelper::getLayoutPath('mod_crowdfundingprofile', $params->get('layout', 'default'));
}
