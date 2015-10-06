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
<div class="span8">
    <table class="table table-bordered">
        <thead>
        <tr>
            <th width="1%">#</th>
            <th class="title">
                <?php echo JText::_("COM_CROWDFUNDING_REWARD"); ?>
            </th>
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
                    <a href="<?php echo JRoute::_("index.php?option=com_crowdfunding&view=rewards&pid=".(int)$reward["project_id"]."&filter_search=id:" . (int)$reward["reward_id"]); ?>">
                        <?php echo $this->escape($reward["reward_name"]); ?>
                    </a>

                    <div class="small">
                        <?php echo JText::_("COM_CROWDFUNDING_PROJECT"); ?>:
                        <a href="<?php echo JRoute::_("index.php?option=com_crowdfunding&view=projects&filter_search=id:" . (int)$reward["project_id"]); ?>">
                            <?php echo $this->escape($reward["project"]); ?>
                        </a>
                    </div>
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
</div>

