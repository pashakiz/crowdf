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
<tr>
    <th width="1%" class="hidden-phone">
        <?php echo JHtml::_('grid.checkall'); ?>
    </th>
    <th class="title">
        <?php echo JHtml::_('grid.sort',  'COM_CROWDFUNDINGDATA_NAME', 'a.name', $this->listDirn, $this->listOrder); ?>
    </th>
    <th width="5%" class="nowrap">
        &nbsp;
    </th>
    <th width="40%" class="nowrap hidden-phone">
        <?php echo JHtml::_('grid.sort',  'COM_CROWDFUNDINGDATA_PROJECT', 'b.title', $this->listDirn, $this->listOrder); ?>
    </th>
    <th width="10%" class="nowrap hidden-phone">
        <?php echo JHtml::_('grid.sort',  'COM_CROWDFUNDINGDATA_AMOUNT', 'c.txn_amount', $this->listDirn, $this->listOrder); ?>
    </th>
    <th width="10%" class="nowrap hidden-phone">
        <?php echo JHtml::_('grid.sort',  'COM_CROWDFUNDINGDATA_COUNTRY', 'd.name', $this->listDirn, $this->listOrder); ?>
    </th>
    <th width="10%" class="nowrap hidden-phone">
        <?php echo JHtml::_('grid.sort',  'COM_CROWDFUNDINGDATA_TRANSACTION_ID', 'c.txn_id', $this->listDirn, $this->listOrder); ?>
    </th>
    <th width="5%" class="nowrap hidden-phone">
        <?php echo JHtml::_('grid.sort',  'COM_CROWDFUNDINGDATA_TRANSACTION_STATE', 'c.txn_status', $this->listDirn, $this->listOrder); ?>
    </th>
    <th width="3%" class="center nowrap hidden-phone">
        <?php echo JHtml::_('grid.sort',  'JGRID_HEADING_ID', 'a.id', $this->listDirn, $this->listOrder); ?>
    </th>
</tr>

	  