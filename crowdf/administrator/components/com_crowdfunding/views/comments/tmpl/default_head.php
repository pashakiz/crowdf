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
        <?php echo JText::_("COM_CROWDFUNDING_COMMENT"); ?>
    </th>
    <th width="30%" class="center nowrap hidden-phone">
        <?php echo JHtml::_('grid.sort', 'COM_CROWDFUNDING_PROJECT', 'b.title', $this->listDirn, $this->listOrder); ?>
    </th>
    <th width="10%" class="center nowrap hidden-phone">
        <?php echo JHtml::_('grid.sort', 'JDATE', 'a.record_date', $this->listDirn, $this->listOrder); ?>
    </th>
    <th width="3%" class="center nowrap hidden-phone">
        <?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'a.id', $this->listDirn, $this->listOrder); ?>
    </th>
</tr>
	  