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

$width = $componentParams->get("rewards_image_square_width", 50);
$height = $componentParams->get("rewards_image_square_height", 50);
?>
<?php if (count($rewards) > 0) { ?>
    <div class="cfrewards<?php echo $moduleclassSfx; ?>">

        <div class="reward_title center"><?php echo JText::_("MOD_CROWDFUNDINGREWARDS_PLEDGE_REWARDS"); ?></div>
        <?php foreach ($rewards as $reward) { ?>
            <div class="reward">

                <?php if (!empty($reward["image_square"])) { ?>
                    <div class="row-fluid">
                        <div class="span3">
                            <?php
                            $thumb = $rewardsImagesUri . "/" . $reward["image_square"];
                            $image = $rewardsImagesUri . "/" . $reward["image"];
                            echo CrowdfundingRewardsModuleHelper::image($thumb, $image, $width, $height);
                            ?>
                        </div>
                        <div class="span9">
                            <a href="<?php echo JRoute::_(CrowdfundingHelperRoute::getBackingRoute($project->getSlug(), $project->getCatSlug(), "default", $reward["id"])); ?>">
                                <span class="ramount">
                                <?php echo JText::sprintf("MOD_CROWDFUNDINGREWARDS_INVEST_MORE", $amount->setValue($reward["amount"])->formatCurrency()); ?>
                                </span>
                                <span class="rtitle"><?php echo htmlspecialchars($reward["title"], ENT_QUOTES, "UTF-8"); ?></span>
                                <span class="rdesc"><?php echo htmlspecialchars($reward["description"], ENT_QUOTES, "UTF-8"); ?></span>
                            </a>
                        </div>
                    </div>
                <?php } else { ?>
                <a href="<?php echo JRoute::_(CrowdfundingHelperRoute::getBackingRoute($project->getSlug(), $project->getCatSlug(), "default", $reward["id"])); ?>">
                    <span class="ramount">
                    <?php echo JText::sprintf("MOD_CROWDFUNDINGREWARDS_INVEST_MORE", $amount->setValue($reward["amount"])->formatCurrency()); ?>
                    </span>
                    <span class="rtitle"><?php echo htmlspecialchars($reward["title"], ENT_QUOTES, "UTF-8"); ?></span>
                    <span class="rdesc"><?php echo htmlspecialchars($reward["description"], ENT_QUOTES, "UTF-8"); ?></span>
                </a>
                <?php } ?>

                <?php if ($additionalInfo) { ?>
                    <hr />
                    <?php if ($params->get("display_funders", 0)) {
                        $reward["funders"] = (empty($reward["funders"])) ? 0 : (int)$reward["funders"];
                        ?>
                        <div class="cf-rewards-backers"><?php echo JText::plural("MOD_CROWDFUNDINGREWARDS_BACKERS", $reward["funders"]); ?></div>
                    <?php } ?>

                    <?php if ($params->get("display_claimed", 0) and !empty($reward["distributed"])) { ?>
                        <div class="cf-rewards-claimed">
                            <?php
                            if ($reward["distributed"] < $reward["number"]) {
                                echo JText::sprintf("MOD_CROWDFUNDINGREWARDS_CLAIMED", $reward["distributed"], $reward["number"]);
                            } else {
                                echo JText::sprintf("MOD_CROWDFUNDINGREWARDS_CLAIMED_ALL_DONE", $reward["distributed"], $reward["number"]);
                            }
                            ?>
                        </div>
                    <?php } ?>

                    <?php
                    if ($params->get("display_delivery_date", 0)) {
                        $deliveryDate = new Prism\Validator\Date($reward["delivery"]);
                        if ($deliveryDate->isValid()) {
                            echo '<div class="cf-rewards-delivery">' . JText::sprintf("MOD_CROWDFUNDINGREWARDS_ESTIMATED_DELIVERY", JHtml::_('date', $reward["delivery"], JText::_('DATE_FORMAT_LC3'))). '</div>';
                        }
                    }?>
                <?php } ?>
            </div>
        <?php } ?>
    </div>
<?php } ?>