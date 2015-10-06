<?php
/**
 * @package      Crowdfunding
 * @subpackage   Modules
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined('_JEXEC') or die;

class CrowdfundingRewardsModuleHelper
{
    public static function image($thumb, $image, $width = 50, $height = 50)
    {
        $html[] = '<a href="' . $image . '" class="js-rewards-images-gallery">';
        $html[] = '<img src="' . $thumb . '" width="' . (int)$width . '" height="' . (int)$height . '" />';
        $html[] = '</a>';

        return implode("\n", $html);

    }
}
