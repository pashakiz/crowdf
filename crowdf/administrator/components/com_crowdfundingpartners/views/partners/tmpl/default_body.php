<?php
/**
 * @package      CrowdfundingPartners
 * @subpackage   Component
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2014 Todor Iliev <todor@itprism.com>. All rights reserved.
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
            <a href="<?php echo JRoute::_("index.php?option=com_crowdfundingpartners&view=partner&layout=edit&id=" . $item->id); ?>">
            <?php echo $this->escape($item->name); ?>
            </a>
        </td>
        <td>
            <a href="<?php echo JRoute::_("index.php?option=com_crowdfunding&view=projects&filter_search=id:" . $item->project_id); ?>">
                <?php echo $this->escape($item->project); ?>
            </a>
        </td>
        <td class="center hidden-phone">
            <?php echo $item->id;?>
        </td>
    </tr>
<?php }?>
