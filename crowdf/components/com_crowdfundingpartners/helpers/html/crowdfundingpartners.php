<?php
/**
 * @package      CrowdfundingPartners
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2014 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// no direct access
defined('_JEXEC') or die;

/**
 * CrowdfundingPartners Html Helper
 *
 * @package        CrowdfundingPartners
 * @subpackage     Components
 * @since          1.6
 */
abstract class JHtmlCrowdfundingPartners
{
    /**
     * Display an avatar and link to user profile.
     *
     * @param array $partner
     * @param array $options
     *
     * @return string
     */
    public static function partner($partner, $options = array())
    {
        $html = array();

        $width = (isset($options["width"])) ? $options["width"] : 50;
        $height = (isset($options["height"])) ? $options["height"] : 50;

        if (!empty($partner["link"])) {
            $html[] = '<a href="'. JRoute::_($partner["link"]) .'"><img src="' . $partner["avatar"] .'" class="img-thumbnail" width="'.$width.'" height="'.$height.'" /></a>';
        } else {
            $html[] = '<img src="' . $partner["avatar"] .'" class="img-thumbnail" width="'.$width.'" height="'.$height.'" />';
        }

        if (!empty($partner["link"])) {
            $html[] = '<a href="'. JRoute::_($partner["link"]) .'">';
            $html[] = htmlentities($partner["name"], ENT_QUOTES, "UTF-8");
            $html[] = '</a>';
        } else {
            $html[] = htmlentities($partner["name"], ENT_QUOTES, "UTF-8");
        }

        return implode("\n", $html);
    }
}
