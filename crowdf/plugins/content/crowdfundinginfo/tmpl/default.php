<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Content.pagenavigation
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>
<div class="row border-thin-solid">
    <div class="col-md-4">
        <h5><?php echo JText::_("PLG_CONTENT_CROWDFUNDINGINFO_PROJECT_BY"); ?></h5>
        <div class="media">
            <div class="media-left">
                <?php echo JHtml::_("crowdfunding.profileAvatar", $socialAvatar, $profileLink); ?>
            </div>
            <div class="media-body">
                <h6 class="media-heading">
                    <?php echo JHtml::_("crowdfunding.profileName", $user->get("name"), $profileLink, $proofVerified); ?>
                </h6>
                <?php if ($this->params->get("user_display_location", 0)) { ?>
                <p class="cf-location"><?php echo $socialLocation; ?></p>
                <?php } ?>
            </div>
        </div>
    </div>

    <?php echo $mapCode; ?>

    <?php if (($this->params->get("display_location", 0) and !empty($location)) or $this->params->get("display_dates", 0)) {?>
    <div class="col-md-3">
        <?php if ($this->params->get("display_location", 0) and !empty($location)) { ?>
        <h5><?php echo JText::_("PLG_CONTENT_CROWDFUNDINGINFO_CAMPAIGN_LOCATION"); ?></h5>
        <p class="cf-location"><?php echo $location->name . ", " . $location->country_code; ?></p>
        <?php } ?>

        <?php if ($this->params->get("display_dates", 0)) { ?>
        <h5><?php echo JText::_("PLG_CONTENT_CROWDFUNDINGINFO_FUNDING_PERIOD"); ?></h5>
        <p><?php echo JText::sprintf("PLG_CONTENT_CROWDFUNDINGINFO_DISPLAY_START_DATE", JHtml::_("crowdfunding.date", $item->funding_start, JText::_("DATE_FORMAT_LC3"))); ?></p>
        <p><?php echo JText::sprintf("PLG_CONTENT_CROWDFUNDINGINFO_DISPLAY_END_DATE", JHtml::_("crowdfunding.date", $item->funding_end, JText::_("DATE_FORMAT_LC3"))); ?></p>
        <?php } ?>
    </div>
    <?php } ?>

</div>
