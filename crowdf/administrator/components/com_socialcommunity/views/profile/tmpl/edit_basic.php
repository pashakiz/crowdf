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
<div class="control-group">
    <div class="control-label"><?php echo $this->form->getLabel('name'); ?></div>
	<div class="controls"><?php echo $this->form->getInput('name'); ?></div>
</div>
<div class="control-group">
    <div class="control-label"><?php echo $this->form->getLabel('alias'); ?></div>
	<div class="controls"><?php echo $this->form->getInput('alias'); ?></div>
</div>
<div class="control-group">
    <div class="control-label"><?php echo $this->form->getLabel('id'); ?></div>
	<div class="controls"><?php echo $this->form->getInput('id'); ?></div>
</div>
<div class="control-group">
    <div class="control-label"><?php echo $this->form->getLabel('bio'); ?></div>
	<div class="controls"><?php echo $this->form->getInput('bio'); ?></div>
</div>
<div class="control-group">
    <div class="control-label"><?php echo $this->form->getLabel('birthday'); ?></div>
	<?php echo $this->form->getInput('birthday'); ?>
</div>
<div class="control-group">
    <div class="control-label"><?php echo $this->form->getLabel('gender'); ?></div>
	<div class="controls"><?php echo $this->form->getInput('gender'); ?></div>
</div>
<div>
    <div class="control-label"><?php echo $this->form->getLabel('photo'); ?></div>
	<div class="controls"><?php echo $this->form->getInput('photo'); ?></div>
</div>