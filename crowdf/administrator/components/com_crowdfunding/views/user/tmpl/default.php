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
<form action="<?php echo JRoute::_('index.php?option=com_crowdfunding'); ?>" method="post" name="adminForm" id="adminForm" class="form-validate" >
    <input type="hidden" name="task" value="" />
    <?php echo JHtml::_('form.token'); ?>
</form>

<div class="row-fluid">
    <div class="span4">
        <img src="../../media/com_crowdfunding/images/no_image.png" class="img-rounded">
        <h2><?php echo JHtml::_('crowdfundingbackend.profileLink', $this->socialProfile, $this->item->name, $this->item->id); ?></h2>
            <?php //echo $this->escape($this->item->name); ?>

        <div class="small">
            <?php echo JText::sprintf("COM_CROWDFUNDING_REGISTERED_S", JHtml::_('date', $this->item->registerDate, JText::_('DATE_FORMAT_LC3'))); ?>
        </div>
    </div>
    <div class="span4">
        <h2><?php echo JText::_("COM_CROWDFUNDING_BASIC_INFORMATION");?></h2>
        <table class="table table-bordered">
            <tbody>
            <tr>
                <th><?php echo JText::_("COM_CROWDFUNDING_PROJECTS");?></th>
                <td>
                    <a href="<?php echo JRoute::_("index.php?option=com_crowdfunding&view=projects&filter_search=uid:" . $this->item->id); ?>">
                    <?php echo (int)$this->projects; ?>
                    </a>
                </td>
            </tr>
            <tr>
                <th><?php echo JText::_("COM_CROWDFUNDING_INVESTED_AMOUNT");?></th>
                <td>
                    <?php echo $this->amount->setValue($this->investedAmount)->formatCurrency(); ?>
                    <div class="small">
                        <a href="<?php echo JRoute::_("index.php?option=com_crowdfunding&view=transactions&filter_search=sid:" . $this->item->id); ?>">
                            <?php echo JText::sprintf("COM_CROWDFUNDING_TRANSACTIONS_N", $this->investedTransactions); ?>
                        </a>
                    </div>
                </td>
            </tr>
            <tr>
                <th><?php echo JText::_("COM_CROWDFUNDING_RECEIVED_AMOUNT");?></th>
                <td>
                    <?php echo $this->amount->setValue($this->receivedAmount)->formatCurrency(); ?>
                    <div class="small">
                        <a href="<?php echo JRoute::_("index.php?option=com_crowdfunding&view=transactions&filter_search=bid:" . $this->item->id); ?>">
                            <?php echo JText::sprintf("COM_CROWDFUNDING_TRANSACTIONS_N", $this->receivedTransactions); ?>
                        </a>
                    </div>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
</div>

<h3><?php echo JText::_("COM_CROWDFUNDING_REWARDS"); ?></h3>
<div class="row-fluid">
<?php echo $this->loadTemplate("rewards"); ?>
</div>