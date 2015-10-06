<?php
/**
 * @package      Crowdfunding
 * @subpackage   Modules
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// no direct access
defined('_JEXEC') or die;

?>
<div class="cf-modprofile<?php echo $moduleclassSfx; ?>">
    <?php if (!empty($profile)) { ?>
    <div>
        <?php if (!empty($profileImage)) { ?>
        <div class="pull-left">
            <?php if ($params->get("image_link", 0)) { ?>
            <a href="<?php echo $profileLink; ?>">
            <?php } ?>
                <img src="<?php echo $profileImage; ?>"
                     alt="<?php echo htmlspecialchars($profile["name"], ENT_QUOTES, "UTF-8"); ?>"
                     width="<?php echo $imageSize; ?>" height="<?php echo $imageSize; ?>"/>
            <?php if ($params->get("image_link", 0)) { ?>
            </a>
            <?php } ?>
        </div>
        <?php } ?>

        <div class="pull-left pl-10">
            <h5>
                <?php echo JHtml::_("crowdfunding.profileName", $profile["name"], $profileLink, $proofVerified); ?>
            </h5>

            <?php if ($params->get("display_location", 0)) {
                echo JHtml::_("crowdfunding.profileLocation", $profileLocation, $profileCountryCode);
            }?>
        </div>
        <div class="clearfix"></div>
    </div>
    <?php } ?>
</div>