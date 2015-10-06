<?php
/**
 * @package      CrowdfundingFiles
 * @subpackage   Component
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2014 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// no direct access
defined('_JEXEC') or die;
?>
<?php foreach ($this->items as $i => $item) {
    $mediaFolder = CrowdfundingFilesHelper::getMediaFolderUri($item->user_id);
    ?>
    <tr class="row<?php echo $i % 2; ?>">
        <td class="center hidden-phone">
            <?php echo JHtml::_('grid.id', $i, $item->id); ?>
        </td>
        <td class="has-context">
            <a href="<?php echo JRoute::_("index.php?option=com_crowdfundingfiles&view=file&layout=edit&id=" . $item->id); ?>">
            <?php echo $this->escape($item->title); ?>
            </a>
        </td>
        <td>
            <a class="btn" href="<?php echo $mediaFolder . "/". $item->filename; ?>" download>
                <i class="icon-download"></i>
            </a>
            <?php echo $this->escape($item->filename); ?>
        </td>
        <td>
            <a href="<?php echo JRoute::_("index.php?option=com_crowdfunding&view=projects&filter_search=id:" . $item->project_id); ?>">
                <?php echo $this->escape($item->project); ?>
            </a>
        </td>
        <td>
            <a href="<?php echo JRoute::_("index.php?option=com_crowdfunding&view=users&filter_search=id:" . $item->user_id); ?>">
                <?php echo $this->escape($item->user); ?>
            </a>
        </td>
        <td class="center hidden-phone">
            <?php echo $item->id;?>
        </td>
    </tr>
<?php }?>
