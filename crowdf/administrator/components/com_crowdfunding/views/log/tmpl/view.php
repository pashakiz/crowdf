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
<div class="row-fluid">

    <h2><?php echo JText::_("COM_CROWDFUNDING_TITLE"); ?></h2>
    <p class="well"><?php echo $this->escape($this->item->title); ?></p>

    <h2><?php echo JText::_("COM_CROWDFUNDING_MESSAGE"); ?></h2>
    <pre><?php echo $this->escape($this->item->data); ?></pre>

    <h2><?php echo JText::_("COM_CROWDFUNDING_LOG_TYPE"); ?></h2>
    <p class="well"><i><?php echo $this->escape($this->item->type); ?></i></p>

    <form action="<?php echo JRoute::_('index.php?option=com_crowdfunding'); ?>" method="post" name="adminForm"
          id="adminForm" class="form-validate">
        <input type="hidden" name="task" value=""/>
        <input type="hidden" name="cid[]" value="<?php echo (int)$this->item->id; ?>"/>
        <?php echo JHtml::_('form.token'); ?>
    </form>
</div>