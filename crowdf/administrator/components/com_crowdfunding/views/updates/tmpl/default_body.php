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
<?php foreach ($this->items as $i => $item) { ?>
    <tr class="row<?php echo $i % 2; ?>">
        <td class="center hidden-phone">
            <?php echo JHtml::_('grid.id', $i, $item->id); ?>
        </td>
        <td>
            <a href="<?php echo JRoute::_("index.php?option=com_crowdfunding&view=update&layout=edit&id=" . $item->id); ?>"><?php echo $item->title; ?></a>
        </td>
        <td class="center hidden-phone">
            <a href="<?php echo JRoute::_("index.php?option=com_crowdfunding&view=projects&filter_search:id=" . $item->project_id); ?>"><?php echo $item->project; ?></a>
        </td>
        <td class="center hidden-phone">
            <?php echo JHTML::_('date', $item->record_date, JText::_('DATE_FORMAT_LC3')); ?>
        </td>
        <td class="cente hidden-phone hidden-phone">
            <?php echo $item->id; ?>
        </td>
    </tr>
<?php } ?>
	  