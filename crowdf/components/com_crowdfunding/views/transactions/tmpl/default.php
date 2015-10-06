<?php
/**
 * @package      Crowdfunding
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// no direct access
defined('_JEXEC') or die;?>
<div class="cftransactions<?php echo $this->pageclass_sfx;?>">
    <?php if ($this->params->get('show_page_heading', 1)) { ?>
    <h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
    <?php } ?>
    
    <form action="<?php echo JRoute::_('index.php?option=com_crowdfunding&view=transactions'); ?>" method="post" name="adminForm" id="adminForm">
    
        <table class="table table-striped table-bordered cf-transactions">
            <thead>
            	<tr>
            		<th>
            		    <?php echo JHtml::_('crowdfunding.sort',  'COM_CROWDFUNDING_PROJECT', 'b.title', $this->listDirn, $this->listOrder); ?>
        		    </th>
            		<th>
            			<?php echo JHtml::_('crowdfunding.sort',  'COM_CROWDFUNDING_AMOUNT', 'a.txn_amount', $this->listDirn, $this->listOrder); ?>
            		</th>
            		<th class="nowrap hidden-phone">
            			<?php echo JHtml::_('crowdfunding.sort',  'COM_CROWDFUNDING_INVESTOR', 'e.name', $this->listDirn, $this->listOrder); ?>
            		</th>
            		<th class="nowrap hidden-phone">
            			<?php echo JHtml::_('crowdfunding.sort',  'COM_CROWDFUNDING_BENEFICIARY', 'f.name', $this->listDirn, $this->listOrder); ?>
            		</th>
            		<th class="nowrap hidden-phone">
            			<?php echo JHtml::_('crowdfunding.sort',  'COM_CROWDFUNDING_DATE', 'a.txn_date', $this->listDirn, $this->listOrder); ?>
            		</th>
            		<th class="nowrap hidden-phone">
            			<?php echo JHtml::_('crowdfunding.sort',  'COM_CROWDFUNDING_REWARD', 'd.title', $this->listDirn, $this->listOrder); ?>
            		</th>
            		<th class="nowrap hidden-phone">
            			<?php echo JHtml::_('crowdfunding.sort',  'JGRID_HEADING_ID', 'a.id', $this->listDirn, $this->listOrder); ?>
        		    </th>
            	</tr>
            </thead>
            <tbody>
            	<?php foreach($this->items as $item) {?>
            	<tr>
            		<td class="has-context">
            			<a href="<?php echo JRoute::_(CrowdfundingHelperRoute::getDetailsRoute($item->slug, $item->catslug));?>">
            			<?php echo JHtmlString::truncate(strip_tags($item->project), 64); ?>
            		    </a>
                        <?php if(!empty($item->txn_id)) { ?>
                        <div class="font-smaller">
                            <?php echo JText::sprintf("COM_CROWDFUNDING_TRANSACTION_ID_S", $item->txn_id); ?>
                        </div>
                        <?php } ?>
        		    </td>
            		<td class="text-center"><?php echo $this->amount->setValue($item->txn_amount)->formatCurrency(); ?></td>
            		<td class="text-center hidden-phone"><?php echo JHtml::_("crowdfunding.name", $item->investor); ?></td>
            		<td class="text-center hidden-phone"><?php echo $this->escape($item->receiver); ?></td>
            		<td class="text-center hidden-phone"><?php echo JHtml::_('date', $item->txn_date, JText::_('DATE_FORMAT_LC3')); ?></td>
            		<td class="text-center hidden-phone">
            		    <?php 
            		    $canEdit = ($this->userId != $item->receiver_id) ? false : true;
            		    echo JHtml::_('crowdfunding.reward', $item->reward_id, $item->reward, $item->id, $item->reward_state, $canEdit, $this->redirectUrl); ?>
            		</td>
            		<td class="text-center hidden-phone">
            			<?php echo $item->id; ?>
            		</td>
            	</tr>
            	<?php }?>
            </tbody>
            <tfoot>
            </tfoot>
        </table>
        
        <input type="hidden" name="task" value="" />
        <input type="hidden" name="filter_order" value="<?php echo $this->listOrder; ?>" />
        <input type="hidden" name="filter_order_Dir" value="<?php echo $this->listDirn; ?>" />
        <?php echo JHtml::_('form.token'); ?>
    </form>
</div>
<?php if (($this->params->def('show_pagination', 1) == 1 || ($this->params->get('show_pagination') == 2)) && ($this->pagination->get('pages.total') > 1)) { ?>
    <div class="pagination">
    <?php if ($this->params->def('show_pagination_results', 1)) { ?>
        <p class="counter pull-right"> <?php echo $this->pagination->getPagesCounter(); ?> </p>
    <?php } ?>
    <?php echo $this->pagination->getPagesLinks(); ?> </div>
<?php } ?>