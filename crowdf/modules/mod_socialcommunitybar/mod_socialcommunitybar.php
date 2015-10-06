<?php
/**
 * @package      Social Community
 * @subpackage   Modules
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// no direct access
defined("_JEXEC") or die;

jimport("Prism.init");
jimport("SocialCommunity.init");

$doc = JFactory::getDocument();

$doc->addStyleSheet(JURI::root().'modules/mod_socialcommunitybar/css/style.css');
$doc->addScript(JURI::root()."modules/mod_socialcommunitybar/js/jquery.socialcommunitybar.js");
$js = '
    jQuery(document).ready(function() {
        jQuery("#js-sc-ntfy").SocialCommunityBar({
            resultsLimit: '.$params->get("results_limit", 5).'
        });
    });
';
$doc->addScriptDeclaration($js);

require JModuleHelper::getLayoutPath('mod_socialcommunitybar', $params->get('layout', 'default'));