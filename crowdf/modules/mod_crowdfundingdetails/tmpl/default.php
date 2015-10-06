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
<div class="cfmdetails<?php echo $moduleclassSfx; ?>">
    <?php if ($project->getId()) { ?>
        <div class="thumbnail cf-project">
            <img src="<?php echo $imageFolder . "/" . $project->getImage(); ?>" alt="<?php echo htmlspecialchars($project->getTitle(), ENT_QUOTES, "UTF-8"); ?>" width="<?php echo $imageWidth; ?>" height="<?php echo $imageHeight; ?>" />
            <div class="caption">
                <h3>
                    <a href="<?php echo JRoute::_(CrowdfundingHelperRoute::getDetailsRoute($project->getSlug(), $project->getCatSlug())); ?>">
                        <?php echo htmlspecialchars($project->getTitle(), ENT_QUOTES, "UTF-8"); ?>
                    </a>
                </h3>
                <span class="font-xxsmall">
                    <?php
                    echo JText::_("MOD_CROWDFUNDINGDETAILS_BY");
                    if (!empty($socialProfileLink)) {?>
                        <a href="<?php echo $socialProfileLink; ?>"><?php echo htmlspecialchars($user->name, ENT_QUOTES, "UTF-8"); ?></a>
                    <?php } else { ?>
                        <?php echo htmlspecialchars($user->name, ENT_QUOTES, "UTF-8"); ?>
                    <?php } ?>
                </span>

                <p><?php echo htmlspecialchars($project->getShortDesc(), ENT_QUOTES, "UTF-8"); ?></p>
            </div>

            <div class="cf-caption-info absolute-bottom">
                <?php echo JHtml::_("crowdfunding.progressbar", $fundedPercents, $project->getDaysLeft(), $project->getFundingType()); ?>
                <div class="row">
                    <div class="col-md-4">
                        <div class="bolder"><?php echo $project->getFundedPercent(); ?>%</div>
                        <div class="text-uppercase"><?php echo JText::_("MOD_CROWDFUNDINGDETAILS_FUNDED"); ?></div>
                    </div>
                    <div class="col-md-4">
                        <div class="bolder"><?php echo $raised; ?></strong></div>
                        <div class="text-uppercase"><?php echo JText::_("MOD_CROWDFUNDINGDETAILS_RAISED"); ?></div>
                    </div>
                    <div class="col-md-4">
                        <div class="bolder"><?php echo $project->getDaysLeft(); ?></strong></div>
                        <div class="text-uppercase"><?php echo JText::_("MOD_CROWDFUNDINGDETAILS_DAYS_LEFT"); ?></div>
                    </div>
                </div>
            </div>
        </div>
    <?php } ?>
</div>