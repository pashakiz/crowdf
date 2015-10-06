<?php
/**
 * @package      Crowdfunding
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2014 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// no direct access
defined('_JEXEC') or die;
?>
<form action="<?php echo JRoute::_('index.php?option=com_crowdfundingdata'); ?>" method="post" name="adminForm" id="adminForm" class="form-validate" >
    <input type="hidden" name="task" value="" />
    <?php echo JHtml::_('form.token'); ?>
</form>

<div class="row-fluid">
    <div class="span4">
        <table class="table table-bordered">
            <tbody>
                <tr>
                    <td colspan="2">
                        <h2><?php echo $this->escape($this->item->name); ?></h2>
                    </td>
                </tr>

                <?php if(!empty($this->item->user_id)) { ?>
                <tr>
                    <td colspan="2">
                        <?php echo JText::sprintf("COM_CROWDFUNDINGDATA_REGISTERED_S", JHtml::_('date', $this->item->registerDate, JText::_('DATE_FORMAT_LC3'))); ?>
                        ( <a href="<?php echo JRoute::_("index.php?option=com_users&view=users&filter_search=id:" . $this->item->user_id); ?>">
                            <?php echo $this->escape($this->item->username); ?>
                        </a>)
                    </td>
                </tr>
                <?php } ?>

                <?php if(!empty($this->item->address)) { ?>
                    <tr>
                        <td colspan="2">
                            <strong><?php echo JText::_("COM_CROWDFUNDINGDATA_ADDRESS");?></strong>
                            <address>
                                <?php echo $this->escape($this->item->address); ?>
                            </address>
                        </td>
                    </tr>
                <?php } ?>

                <?php if(!empty($this->item->country)) { ?>
                <tr>
                    <th><?php echo JText::_("COM_CROWDFUNDINGDATA_COUNTRY");?></th>
                    <td>
                        <?php echo $this->escape($this->item->country); ?>
                    </td>
                </tr>
                <?php } ?>
                <?php if(!empty($this->item->email)) { ?>
                    <tr>
                        <th><?php echo JText::_("COM_CROWDFUNDINGDATA_EMAIL");?></th>
                        <td>
                            <?php echo $this->escape($this->item->email); ?>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>

    </div>
    <div class="span4">
        <table class="table table-bordered">
            <tbody>
            <tr>
                <td colspan="2">
                    <h3>
                        <?php echo JText::_("COM_CROWDFUNDINGDATA_TRANSACTION_INFORMATION");?>
                    </h3>
                </td>
            </tr>
            <tr>
                <th><?php echo JText::_("COM_CROWDFUNDINGDATA_PROJECT");?></th>
                <td>
                    <a href="<?php echo JRoute::_("index.php?option=com_crowdfunding&view=projects&filter_search=id:" . $this->item->project_id); ?>">
                        <?php echo $this->escape($this->item->project); ?>
                    </a>
                </td>
            </tr>
            <tr>
                <th><?php echo JText::_("COM_CROWDFUNDINGDATA_TRANSACTION_ID");?></th>
                <td>
                    <a href="<?php echo JRoute::_("index.php?option=com_crowdfunding&view=transactions&filter_search=id:" . $this->item->transaction_id); ?>">
                    <?php echo $this->escape($this->item->txn_id); ?>
                    </a>
                </td>
            </tr>
            <tr>
                <th><?php echo JText::_("COM_CROWDFUNDINGDATA_AMOUNT");?></th>
                <td>
                    <?php echo $this->amount->setValue($this->item->txn_amount)->formatCurrency(); ?>
                </td>
            </tr>
            <tr>
                <th><?php echo JText::_("COM_CROWDFUNDINGDATA_ADDITIONAL_INFORMAtION");?></th>
                <td>
                    <a href="<?php echo JRoute::_("index.php?option=com_crowdfunding&view=users&filter_search=id:" . $this->item->user_id); ?>">
                    <?php echo $this->escape($this->item->username); ?>
                    </a>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
</div>
