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
<?php foreach ($this->items as $i => $item) {?>
	<tr class="row<?php echo $i % 2; ?>">
		<td class="center hidden-phone">
            <?php echo JHtml::_('grid.id', $i, $item->id); ?>
        </td>
		<td>
			<a href="<?php echo JRoute::_("index.php?option=com_socialcommunity&view=country&layout=edit&id=" .(int)$item->id); ?>" ><?php echo $item->name; ?></a>
		</td>
		<td class="center"><?php echo $item->code; ?></td>
		<td class="center hidden-phone"><?php echo $item->code4; ?></td>
		<td class="center hidden-phone"><?php echo $item->latitude; ?></td>
		<td class="center hidden-phone"><?php echo $item->longitude; ?></td>
		<td class="center hidden-phone"><?php echo $item->timezone; ?></td>
        <td class="center hidden-phone"><?php echo $item->id;?></td>
	</tr>
<?php } ?>
	  