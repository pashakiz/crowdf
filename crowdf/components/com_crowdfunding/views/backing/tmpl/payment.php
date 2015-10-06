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
?>
<div class="cfbacking<?php echo $this->params->get("pageclass_sfx"); ?>">
    <?php if ($this->params->get('show_page_heading', 1)) : ?>
        <h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-12">
            <?php
            if (strcmp("three_steps", $this->wizardType) == 0) {
                $layout = new JLayoutFile('payment_wizard');
            } else {
                $layout = new JLayoutFile('payment_wizard_four_steps');
            }
            echo $layout->render($this->layoutData);
            ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h2><?php echo JText::_("COM_CROWDFUNDING_INVESTMENT_SUMMARY"); ?></h2>
                </div>
                <div class="panel-body">
                    <p><?php
                        $amount = $this->amount->setValue($this->paymentAmount)->formatCurrency();
                        echo JText::sprintf("COM_CROWDFUNDING_INVESTMENT_AMOUNT", $amount); ?></p>

                    <p><?php echo JText::_("COM_CROWDFUNDING_FUNDING_TYPE_" . Joomla\String\String::strtoupper($this->item->funding_type)); ?></p>

                    <p class="bg-info p-5">
                        <span class="glyphicon glyphicon-info-sign"></span>
                        <?php
                        $endDate = JHtml::_('date', $this->item->funding_end, JText::_('DATE_FORMAT_LC3'));
                        if ($this->item->funding_type == "FIXED") {
                            $goal = $this->amount->setValue($this->item->goal)->formatCurrency();
                            echo JText::sprintf("COM_CROWDFUNDING_FUNDING_TYPE_INFO_FIXED", $goal, $endDate);
                        } else {
                            echo JText::sprintf("COM_CROWDFUNDING_FUNDING_TYPE_INFO_FLEXIBLE", $endDate);
                        }
                        ?></p>
                </div>
            </div>

            <?php if ($this->rewardsEnabled) { ?>
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h2><?php echo JText::_("COM_CROWDFUNDING_SELECTED_REWARD"); ?></h2>
                </div>
                <div class="panel-body">
                    <?php if (!$this->paymentSession->rewardId) { ?>
                        <p><?php echo JText::_("COM_CROWDFUNDING_NO_SELECTED_REWARD"); ?></p>
                    <?php } else { ?>
                        <h3><?php echo $this->escape($this->reward->getTitle()); ?></h3>
                        <p><?php echo $this->escape($this->reward->getDescription()); ?></p>
                    <?php } ?>
                </div>
            </div>
            <?php } ?>

            <?php if (!empty($this->item->event->onBeforePaymentAuthorize)) { ?>
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4><?php echo JText::_("COM_CROWDFUNDING_PAYMENTS_NOT_ALLOWED"); ?></h4>
                </div>
                <div class="panel-body">
                    <?php echo $this->item->event->onBeforePaymentAuthorize; ?>
                </div>
            </div>
            <?php } ?>

            <?php if (!empty($this->item->event->onProjectPayment)) { ?>
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h2><?php echo JText::_("COM_CROWDFUNDING_PAYMENT_METHODS"); ?></h2>
                </div>
                <div class="panel-body">
                    <?php echo $this->item->event->onProjectPayment; ?>
                </div>
            </div>
            <?php } ?>

        </div>
    </div>
</div>