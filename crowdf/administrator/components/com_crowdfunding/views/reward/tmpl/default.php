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
<div class="row-fluid">
    <div class="span8">
        <form action="<?php echo JRoute::_('index.php?option=com_crowdfunding'); ?>" method="post" name="adminForm" id="adminForm">

            <table class="table table-bordered">
                <thead>
                <tr>
                    <th width="1%">#</th>
                    <th class="title">
                        <?php echo JText::_("COM_CROWDFUNDING_RECIPIENT"); ?>
                    </th>
                    <th width="1%">&nbsp;</th>
                    <th width="30%">
                        <?php echo JText::_("COM_CROWDFUNDING_TRANSACTION_ID"); ?>
                    </th>
                    <th width="10%" class="center hidden-phone">
                        <?php echo JText::_("JSTATUS"); ?>
                    </th>
                </tr>
                </thead>
                <tbody>
                <?php
                $i = 1;
                foreach($this->rewards as $reward) {
                    $classRow = (!$reward["reward_state"]) ? "" : 'class="success"';
                    ?>
                <tr <?php echo $classRow; ?>>
                    <td><?php echo $i; ?></td>
                    <td class="has-context">
                        <a href="<?php echo JRoute::_("index.php?option=com_crowdfunding&view=users&filter_search=id:" . (int)$reward["receiver_id"]); ?>">
                            <?php echo $this->escape($reward["name"]); ?>
                        </a>
                        <div class="small">
                            <?php echo JText::sprintf("COM_CROWDFUNDING_EMAIL_S", $this->escape($reward["email"])); ?>
                        </div>
                    </td>
                    <td>
                        <?php echo JHtml::_('crowdfundingbackend.profileIcon', $this->socialProfile, $this->rewardOwnerId); ?>
                    </td>
                    <td>
                        <a href="<?php echo JRoute::_("index.php?option=com_crowdfunding&view=transactions&filter_search=id:" . (int)$reward["transaction_id"]); ?>">
                            <?php echo $this->escape($reward["txn_id"]); ?>
                        </a>
                    </td>
                    <td class="center hidden-phone">
                        <?php echo JHtml::_('crowdfundingbackend.rewardState', $reward["reward_id"], $reward["transaction_id"], $reward["reward_state"], $this->returnUrl); ?>
                    </td>
                </tr>
                <?php
                    $i++;
                } ?>
                </tbody>
            </table>
            <input type="hidden" name="task" value=""/>
            <?php echo JHtml::_('form.token'); ?>
        </form>
    </div>
    <div class="span4">
        <table class="table table-bordered">
            <tbody>
            <tr>
                <td colspan="2">
                    <h3>
                        <?php echo JText::_("COM_CROWDFUNDING_BASIC_INFORMATION");?>
                    </h3>
                </td>
            </tr>
                <tr>
                    <td colspan="2">
                        <h4><?php echo $this->escape($this->item->title); ?></h4>
                        <p><?php echo $this->escape($this->item->description); ?></p>

                        <?php if (!empty($this->item->image)) { ?>
                        <img src="../<?php echo $this->imagesFolder . "/".$this->item->image_thumb; ?>" />
                        <?php } ?>
                    </td>
                </tr>

                <tr>
                    <td>
                        <?php echo JText::_("COM_CROWDFUNDING_NUMBER_OF_REWARDS"); ?>
                    </td>
                    <td>
                        <?php echo JHtml::_('crowdfunding.rewardsNumber', $this->item->number); ?>
                    </td>
                </tr>
                <tr>
                    <td>
                        <?php echo JText::_("COM_CROWDFUNDING_DISTRIBUTED"); ?>
                    </td>
                    <td>
                        <?php echo $this->item->distributed; ?>
                    </td>
                </tr>
                <tr>
                    <td>
                        <?php echo JText::_("COM_CROWDFUNDING_AVAILABLE"); ?>
                    </td>
                    <td>
                        <?php echo JHtml::_('crowdfunding.rewardsAvailable', $this->item->number, $this->item->distributed); ?>
                    </td>
                </tr>
                <tr>
                    <td>
                        <?php echo JText::_("COM_CROWDFUNDING_DELIVERY_UNTIL_DATE"); ?>
                    </td>
                    <td>
                        <?php echo $this->deliveryDate; ?>
                    </td>
                </tr>

            </tbody>
        </table>
    </div>
</div>
