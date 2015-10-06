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
<?php foreach ($this->items as $i => $item) {
    $ordering = ($this->listOrder == 'a.ordering');
    ?>
    <tr class="row<?php echo $i % 2; ?>">
        <td class="center hidden-phone">
            <?php echo JHtml::_('grid.id', $i, $item->id); ?>
        </td>
        <td>
            <a href="<?php echo JRoute::_("index.php?option=com_crowdfunding&view=transaction&layout=edit&id=" . $item->id); ?>"><?php echo $item->txn_id; ?></a>
            <?php if (!empty($item->parent_txn_id)) { ?>
                <div class="small">
                    <?php echo $this->escape($item->parent_txn_id); ?>
                </div>
            <?php } ?>
        </td>
        <td class="hidden-phone">
            <a href="<?php echo JRoute::_("index.php?option=com_crowdfunding&view=transactions&filter_search=pid:" . $item->project_id); ?>">
                <?php echo JHtmlString::truncate(strip_tags($item->project), 53); ?>
            </a>
        </td>
        <td class="hidden-phone">
            <?php echo JHtml::_("crowdfundingbackend.name", $item->sender, $item->investor_id); ?>
        </td>
        <td class="hidden-phone">
            <?php echo JHtml::_("crowdfundingbackend.name", $item->beneficiary, $item->receiver_id); ?>
        </td>
        <td><?php echo JHtml::_('crowdfundingbackend.transactionAmount', $item, $this->amount, $this->currencies); ?></td>
        <td class="hidden-phone"><?php echo $item->txn_date; ?></td>
        <td class="hidden-phone"><?php echo $item->service_provider; ?></td>
        <td class="hidden-phone">
            <?php echo $item->txn_status; ?>
            <?php echo JHtml::_('crowdfundingbackend.reason', $item->status_reason); ?>
        </td>
        <td class="center hidden-phone">
            <?php echo JHtml::_('crowdfundingbackend.reward', $item->reward_id, $item->reward, $item->project_id, $item->reward_state); ?>
        </td>
        <td class="center hidden-phone"><?php echo $item->id; ?></td>
    </tr>
<?php } ?>
	  