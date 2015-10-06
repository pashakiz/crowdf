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
<tr>
    <th width="1%" class="hidden-phone">
        <?php echo JHtml::_('grid.checkall'); ?>
    </th>
    <th width="1%" style="min-width: 55px" class="nowrap center">
        <?php echo JHtml::_('grid.sort', 'JSTATUS', 'a.published', $this->listDirn, $this->listOrder); ?>
    </th>
    <th class="title">
        <?php echo JHtml::_('grid.sort', 'COM_CROWDFUNDING_TITLE', 'a.title', $this->listDirn, $this->listOrder); ?>
    </th>
    <th width="1%" style="min-width: 55px">
        &nbsp;
    </th>
    <th width="10%" class="center nowrap">
        <?php echo JHtml::_('grid.sort', 'COM_CROWDFUNDING_AMOUNT', 'a.amount', $this->listDirn, $this->listOrder); ?>
    </th>
    <th width="10%" class="center nowrap hidden-phone">
        <?php echo JHtml::_('grid.sort', 'COM_CROWDFUNDING_NUMBER', 'a.number', $this->listDirn, $this->listOrder); ?>
    </th>
    <th width="5%" class="center nowrap hidden-phone">
        <?php echo JHtml::_('grid.sort', 'COM_CROWDFUNDING_DISTRIBUTED', 'a.distributed', $this->listDirn, $this->listOrder); ?>
    </th>
    <th width="5%" class="center nowrap hidden-phone">
        <?php echo JHtml::_('grid.sort', 'COM_CROWDFUNDING_AVAILABLE', 'available', $this->listDirn, $this->listOrder); ?>
    </th>
    <th width="10%" class="center nowrap hidden-phone">
        <?php echo JHtml::_('grid.sort', 'COM_CROWDFUNDING_DELIVERY', 'a.delivery', $this->listDirn, $this->listOrder); ?>
    </th>
    <th width="3%"
        class="center nowrap hidden-phone"><?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'a.id', $this->listDirn, $this->listOrder); ?></th>
</tr>
	  