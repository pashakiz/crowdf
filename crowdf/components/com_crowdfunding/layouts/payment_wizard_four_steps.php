<?php
/**
 * @package      Crowdfunding
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// no direct access
defined('_JEXEC') or die;

$active = array("rewards" => false, "step2" => false, "payment" => false, "share" => false);

switch ($displayData->layout) {
    case "default":
        $active["rewards"] = true;
        break;
    case "step2":
        $active["step2"] = true;
        break;
    case "payment":
        $active["payment"] = true;
        break;
    case "share":
        $active["share"] = true;
        break;
}
?>
<div class="navbar navbar-default" role="navigation">
    <div class="container-fluid">
        <div class="navbar-header">
            <a class="navbar-brand" href="javascript:void(0);"><?php echo JText::_("COM_CROWDFUNDING_INVESTMENT_PROCESS"); ?></a>
        </div>

        <div class="collapse navbar-collapse">
            <ul class="nav navbar-nav">
                <li <?php echo ($active["rewards"]) ? 'class="active"' : ''; ?>>
                    <a href="<?php echo JRoute::_(CrowdfundingHelperRoute::getBackingRoute($displayData->item->slug, $displayData->item->catslug)); ?>">
                        (1) <?php echo (!$displayData->rewards_enabled) ? JText::_("COM_CROWDFUNDING_STEP_PLEDGE") : JText::_("COM_CROWDFUNDING_STEP_PLEDGE_REWARDS"); ?>
                    </a>
                </li>

                <li <?php echo ($active["step2"]) ? 'class="active"' : ''; ?>>
                    <?php if (!empty($displayData->paymentSession->step1)) { ?>
                        <a href="<?php echo JRoute::_(CrowdfundingHelperRoute::getBackingRoute($displayData->item->slug, $displayData->item->catslug, "step2")); ?>">
                            (2) <?php echo JText::_("COM_CROWDFUNDING_LAYOUT_PAYMENT_WIZARD_STEP2_TITLE"); ?>
                        </a>
                    <?php } else { ?>
                        <a href="javascript: void(0);"
                           class="disabled">(2) <?php echo JText::_("COM_CROWDFUNDING_LAYOUT_PAYMENT_WIZARD_STEP2_TITLE"); ?></a>
                    <?php } ?>
                </li>

                <li <?php echo ($active["payment"]) ? 'class="active"' : ''; ?>>
                    <?php if (!empty($displayData->paymentSession->step1)) { ?>
                        <a href="<?php echo JRoute::_(CrowdfundingHelperRoute::getBackingRoute($displayData->item->slug, $displayData->item->catslug, "payment")); ?>">
                            (3) <?php echo JText::_("COM_CROWDFUNDING_STEP_PAY"); ?>
                        </a>
                    <?php } else { ?>
                        <a href="javascript: void(0);"
                           class="disabled">(3) <?php echo JText::_("COM_CROWDFUNDING_STEP_PAY"); ?></a>
                    <?php } ?>
                </li>

                <li <?php echo ($active["share"]) ? 'class="active"' : ''; ?>>
                    <?php if (!empty($displayData->paymentSession->step2)) { ?>
                        <a href="<?php echo JRoute::_(CrowdfundingHelperRoute::getBackingRoute($displayData->item->slug, $displayData->item->catslug, "share")); ?>">
                            (4) <?php echo JText::_("COM_CROWDFUNDING_STEP_SHARE"); ?>
                        </a>
                    <?php } else { ?>
                        <a href="javascript: void(0);"
                           class="disabled">(4) <?php echo JText::_("COM_CROWDFUNDING_STEP_SHARE"); ?></a>
                    <?php } ?>
                </li>
            </ul>
        </div>
    </div>
</div>
