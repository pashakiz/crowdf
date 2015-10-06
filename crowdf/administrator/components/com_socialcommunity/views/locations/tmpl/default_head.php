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
	<th width="1%" class="hidden-phone">
		<?php echo JHtml::_('grid.checkall'); ?>
	</th>
	<th width="1%" style="min-width: 55px" class="nowrap center">
		<?php echo JHtml::_('grid.sort', 'JSTATUS', 'a.published', $this->listDirn, $this->listOrder); ?>
	</th>
	<th class="title">
        <?php echo JHtml::_('grid.sort',  'COM_SOCIALCOMMUNITY_NAME', 'a.name', $this->listDirn, $this->listOrder); ?>
    </th>
    <th width="10%" class="center nowrap hidden-phone">
    	<?php echo JHtml::_('grid.sort',  'COM_SOCIALCOMMUNITY_COUNTRY_CODE', 'a.country_code', $this->listDirn, $this->listOrder); ?>
    </th>
    <th width="10%" class="center nowrap hidden-phone">
        <?php echo JHtml::_('grid.sort',  'COM_SOCIALCOMMUNITY_TIMEZONE', 'a.timezone', $this->listDirn, $this->listOrder); ?>
    </th>
    <th width="10%" class="center nowrap hidden-phone">
        <?php echo JText::_("COM_SOCIALCOMMUNITY_LATITUDE"); ?>
    </th>
    <th width="10%" class="center nowrap hidden-phone">
        <?php echo JText::_("COM_SOCIALCOMMUNITY_LONGITUDE"); ?>
    </th>
    <th width="10%" class="center nowrap hidden-phone">
        <?php echo JHtml::_('grid.sort',  'COM_SOCIALCOMMUNITY_STATE_CODE', 'a.state_code', $this->listDirn, $this->listOrder); ?>
    </th>
    <th width="3%" class="center nowrap hidden-phone">
        <?php echo JHtml::_('grid.sort',  'JGRID_HEADING_ID', 'a.id', $this->listDirn, $this->listOrder); ?>
    </th>
</tr>
	  