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

if (strcmp("five_steps", $this->wizardType) == 0) {
    $layout      = new JLayoutFile('project_wizard');
} else {
    $layout      = new JLayoutFile('project_wizard_six_steps');
}
echo $layout->render($this->layoutData);
?>
<div class="row">
    <form action="<?php echo JRoute::_('index.php?option=com_crowdfunding'); ?>" method="post" name="projectForm" id="crowdf-story-form" class="form-validate" enctype="multipart/form-data">
        
        <div class="col-md-12">
        
            <div class="form-group">
                <?php echo $this->form->getLabel('pitch_video'); ?>
                <?php echo $this->form->getInput('pitch_video'); ?>
				<span class="help-block"><?php echo JText::_("COM_CROWDFUNDING_FIELD_FUNDING_VIDEO_HELP_BLOCK");?></span>
            </div>

            <div class="form-group">
            <?php echo $this->form->getLabel('pitch_image'); ?>
            <?php echo $this->form->getInput('pitch_image'); ?>
            <span class="help-block">(PNG, JPG, or GIF - <?php echo $this->pWidth; ?> x <?php echo $this->pHeight; ?> pixels) </span>
            </div>

            <?php if(!empty($this->pitchImage)) {?>
            <img src="<?php echo $this->imageFolder."/".$this->pitchImage;?>" class="img-thumbnail" />
            <br /><br />
                <?php if(!$this->debugMode) {?>
                <a href="<?php echo JRoute::_("index.php?option=com_crowdfunding&task=story.removeImage&id=".$this->item->id."&".JSession::getFormToken()."=1");?>" class="btn btn-sm btn-danger" role="button">
                   <span class="glyphicon glyphicon-trash"></span>
                   <?php echo JText::_("COM_CROWDFUNDING_REMOVE_IMAGE");?>
                </a>
                <?php }?>
            <?php }?>

            <div class="form-group">
            <?php echo $this->form->getLabel('description'); ?>
            <?php echo $this->form->getInput('description'); ?>
        	</div>
            
            <?php echo $this->form->getInput('id'); ?>
            <input type="hidden" name="task" value="story.save" />
            <?php echo JHtml::_('form.token'); ?>

            <button type="submit" class="btn btn-primary mtb-15-0" <?php echo $this->disabledButton;?>>
                <span class="glyphicon glyphicon-ok"></span>
                <?php echo JText::_("COM_CROWDFUNDING_SAVE_AND_CONTINUE")?>
            </button>
        </div>
        
    </form>
</div>