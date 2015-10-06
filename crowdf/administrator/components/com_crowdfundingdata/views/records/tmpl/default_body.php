<?php
/**
 * @package      CrowdfundingData
 * @subpackage   Component
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2014 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// no direct access
defined('_JEXEC') or die;
?>
<?php foreach ($this->items as $i => $item) {

    $amount = "---";

    if (!empty($item->txn_currency)) {
        $currency = $this->currencies->getCurrencyByCode($item->txn_currency);

        $this->amount->setCurrency($currency);
        $amount = $this->amount->setValue($item->txn_amount)->formatCurrency();
    }

    ?>
    <tr class="row<?php echo $i % 2; ?>">
        <td class="center hidden-phone">
            <?php echo JHtml::_('grid.id', $i, $item->id); ?>
        </td>
        <td class="has-context">
            <a href="<?php echo JRoute::_("index.php?option=com_crowdfundingdata&view=record&layout=edit&id=" . $item->id); ?>"><?php echo $this->escape($item->name); ?></a>

            <?php if (!empty($item->user_id)) { ?>
                <a href="<?php echo JRoute::_("index.php?option=com_crowdfunding&view=users&filter_search=id:" . $item->user_id); ?>" class="btn btn-mini hasTooltip" title="<?php echo JText::_("COM_CROWDFUNDINGDATA_ADDITIONAL_INFORMATION"); ?>">
                    <i class="icon-user"></i>
                </a>
            <?php } ?>

            <?php if (!empty($item->email)) { ?>
                <div class="small">
                    <?php echo JText::sprintf("COM_CROWDFUNDINGDATA_EMAIL_S", $item->email); ?>
                </div>
            <?php } ?>
        </td>
        <td>
            <a href="<?php echo JRoute::_("index.php?option=com_crowdfundingdata&view=record&id=" . $item->id); ?>" class="btn">
                <i class="icon icon-eye"></i>
            </a>
        </td>
        <td>
            <a href="<?php echo JRoute::_("index.php?option=com_crowdfunding&view=projects&filter_search=id:" . $item->project_id); ?>">
                <?php echo $this->escape($item->project); ?>
            </a>
        </td>
        <td>
            <?php echo $amount; ?>
        </td>
        <td class="nowrap hidden-phone">
            <?php echo $item->country; ?>
        </td>
        <td>
            <a href="<?php echo JRoute::_("index.php?option=com_crowdfunding&view=transactions&filter_search=id:" . $item->transaction_id); ?>">
                <?php echo $this->escape($item->txn_id); ?>
            </a>
        </td>
        <td class="nowrap hidden-phone">
            <?php echo $this->escape($item->txn_status); ?>
        </td>
        <td class="center hidden-phone">
            <?php echo $item->id;?>
        </td>
    </tr>
<?php }?>
