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
    <div class="col-md-6">
        <form action="<?php echo JRoute::_('index.php?option=com_crowdfunding'); ?>" method="post" name="projectForm" id="js-cf-project-form" novalidate="novalidate" autocomplete="off" enctype="multipart/form-data" >

            <div class="form-group">
                <?php echo $this->form->getLabel('title'); ?>
                <?php echo $this->form->getInput('title'); ?>
            </div>

            <div class="form-group">
            <?php echo $this->form->getLabel('short_desc'); ?>
            <?php echo $this->form->getInput('short_desc'); ?>
            </div>

            <div class="form-group">
            <?php echo $this->form->getLabel('catid'); ?>
            <?php echo $this->form->getInput('catid'); ?>
            </div>

            <div class="form-group">
            <?php echo $this->form->getLabel('location_preview'); ?>
            <?php echo $this->form->getInput('location_preview'); ?>
            </div>

            <?php if(!empty($this->numberOfTypes)) {?>
                <div class="form-group">
                <?php echo $this->form->getLabel('type_id'); ?>
                <?php echo $this->form->getInput('type_id'); ?>
                </div>
            <?php  } else { ?>
                <input type="hidden" name="jform[type_id]" value="0" />
            <?php }?>
            
            <?php 
			if($this->params->get("project_terms", 0) AND $this->isNew) {
			    $termsUrl = $this->params->get("project_terms_url", "");
			?>
			<div class="checkbox">
                <label>
                    <input type="checkbox" name="jform[terms]" value="1" required="required"> <?php echo (!$termsUrl) ? JText::_("COM_CROWDFUNDING_TERMS_AGREEMENT") : JText::sprintf("COM_CROWDFUNDING_TERMS_AGREEMENT_URL", $termsUrl);?>
                </label>
            </div>
            <?php }?>
            
            <?php echo $this->form->getInput('id'); ?>
            <?php echo $this->form->getInput('location_id'); ?>
            
            <input type="hidden" name="task" value="project.save" />
            <?php echo JHtml::_('form.token'); ?>
            
            <button type="submit" class="btn btn-primary mtb-15-0" <?php echo $this->disabledButton;?>>
            	<span class="glyphicon glyphicon-ok"></span>
                <?php echo JText::_("COM_CROWDFUNDING_SAVE_AND_CONTINUE")?>
            </button>
        </form>
    </div>

    <div class="col-md-6">
        <?php if(!$this->debugMode) {?>
        <div class="mb-15">
            <span class="btn btn-default fileinput-button">
                <span class="glyphicon glyphicon-upload"></span>
                <span><?php echo JText::_("COM_CROWDFUNDING_UPLOAD_IMAGE");?></span>
                <!-- The file input field used as target for the file upload widget -->
                <input id="js-thumb-fileupload" type="file" name="project_image" data-url="<?php echo JRoute::_("index.php?option=com_crowdfunding&task=project.uploadImage&format=raw");?>" />
            </span>

            <a href="<?php echo JRoute::_("index.php?option=com_crowdfunding&task=project.removeImage&id=".$this->item->id."&".JSession::getFormToken()."=1");?>" id="js-btn-remove-image" class="btn btn-danger" style="display: <?php echo $this->displayRemoveButton; ?>">
                <span class="glyphicon glyphicon-trash"></span>
                <?php echo JText::_("COM_CROWDFUNDING_REMOVE_IMAGE");?>
            </a>

            <img src="media/com_crowdfunding/images/ajax-loader.gif" width="16" height="16" id="js-thumb-fileupload-loader" style="display: none;" />

            <div id="js-image-tools" class="mt-10" style="display: none;">
                <a href="javascript: void(0);" class="btn btn-primary" id="js-crop-btn">
                    <span class="glyphicon glyphicon-ok-sign"></span>
                    <?php echo JText::_("COM_CROWDFUNDING_CROP_IMAGE");?>
                </a>

                <a href="javascript: void(0);" class="btn btn-default" id="js-crop-btn-cancel">
                    <span class="glyphicon glyphicon-ban-circle"></span>
                    <?php echo JText::_("COM_CROWDFUNDING_CANCEL");?>
                </a>
            </div>

        </div>
        <form action="<?php echo JRoute::_("index.php?option=com_crowdfunding");?>" method="post" id="js-image-tools-form">
            <input type="hidden" name="<?php echo JSession::getFormToken(); ?>" value="1" />
        </form>
        <?php }?>

        <div id="js-fixed-dragger-cropper">
            <img src="<?php echo $this->imagePath; ?>" class="img-polaroid" id="js-thumb-img" />
        </div>

    </div>

</div>