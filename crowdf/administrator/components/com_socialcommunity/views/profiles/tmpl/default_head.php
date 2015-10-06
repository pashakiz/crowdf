<?php
/**
 * @package      SocialCommunity
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// no direct access
defined('_JEXEC') or die;
?>
<tr>
    <th width="1%" class="nowrap center hidden-phone">
        <?php echo JHtml::_('grid.checkall'); ?>
    </th>
    <th width="10%" class="nowrap center">
	     <?php echo JText::_("COM_SOCIALCOMMUNITY_PROFILE"); ?>
    </th>
	<th class="title" >
	     <?php echo JHtml::_('grid.sort',  'COM_SOCIALCOMMUNITY_NAME', 'a.name', $this->listDirn, $this->listOrder); ?>
	</th>
	<th width="10%" class="nowrap center hidden-phone">
	    <?php echo JText::_("COM_SOCIALCOMMUNITY_COUNTRY"); ?>
	</th>
	<th width="10%" class="nowrap center hidden-phone">
	    <?php echo JText::_("COM_SOCIALCOMMUNITY_IMAGE"); ?>
	</th>
	<th width="10%" class="nowrap center hidden-phone">
	     <?php echo JHtml::_('grid.sort',  'COM_SOCIALCOMMUNITY_REGISTERED', 'b.registerDate', $this->listDirn, $this->listOrder); ?>
	</th>
    <th width="1%" class="nowrap center hidden-phone">
         <?php echo JHtml::_('grid.sort',  'JGRID_HEADING_ID', 'a.id', $this->listDirn, $this->listOrder); ?>
    </th>
</tr>
	  