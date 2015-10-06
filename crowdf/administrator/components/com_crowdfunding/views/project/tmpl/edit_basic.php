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

<?php echo $this->form->getControlGroup('title'); ?>
<?php echo $this->form->getControlGroup('alias'); ?>
<?php echo $this->form->getControlGroup('catid'); ?>
<?php echo $this->form->getControlGroup('type_id'); ?>
<?php echo $this->form->getControlGroup('location_preview'); ?>

<div class="control-group">
    <div class="control-label"><?php echo $this->form->getLabel('image'); ?></div>
    <div class="controls">
        <div class="fileupload fileupload-new" data-provides="fileupload">
        <span class="btn btn-file">
            <span class="fileupload-new"><i class="icon-folder-open"></i> <?php echo JText::_("COM_CROWDFUNDING_SELECT_FILE"); ?></span>
            <span class="fileupload-exists"><i class="icon-edit"></i> <?php echo JText::_("COM_CROWDFUNDING_CHANGE"); ?></span>
            <?php echo $this->form->getInput('image'); ?>
        </span>
            <span class="fileupload-preview"></span>
            <a href="#" class="close fileupload-exists" data-dismiss="fileupload" style="float: none">×</a>
        </div>
    </div>
</div>

<?php echo $this->form->getControlGroup('published'); ?>
<?php echo $this->form->getControlGroup('approved'); ?>
<?php echo $this->form->getControlGroup('created'); ?>
<?php echo $this->form->getControlGroup('user_id'); ?>
<?php echo $this->form->getControlGroup('id'); ?>
<?php echo $this->form->getControlGroup('short_desc'); ?>
<?php echo $this->form->getControlGroup('location_id'); ?>
