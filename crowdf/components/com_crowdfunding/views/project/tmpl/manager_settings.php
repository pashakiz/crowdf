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
<?php if (!$this->item->published) { ?>
    <a class="btn btn-default btn-lg" href="<?php echo JRoute::_(CrowdfundingHelperRoute::getDetailsRoute($this->item->slug, $this->item->catslug)); ?>" target="_blank">
        <span class="glyphicon glyphicon-eye-open"></span>
        <?php echo JText::_("COM_CROWDFUNDING_PREVIEW");?>
    </a>
    <a class="btn btn-primary btn-lg" id="js-btn-project-publish" href="<?php echo JRoute::_("index.php?option=com_crowdfunding&task=projects.savestate&id=".(int)$this->item->id."&state=1&".JSession::getFormToken()."=1&return=".base64_encode($this->returnUrl)); ?>">
        <span class="glyphicon glyphicon-ok"></span>
        <?php echo JText::_("COM_CROWDFUNDING_LAUNCH");?>
    </a>
    <p class="bg-info mt-10 p-5 text-justify">
        <span class="glyphicon glyphicon-info-sign"></span>
        <?php echo JText::_("COM_CROWDFUNDING_NOTE_LAUNCH_PROJECT"); ?>
    </p>
<?php } else { ?>

    <a class="btn btn-danger btn-lg" id="js-btn-project-unpublish" href="<?php echo JRoute::_("index.php?option=com_crowdfunding&task=projects.savestate&id=".(int)$this->item->id."&state=0&".JSession::getFormToken()."=1&return=".base64_encode($this->returnUrl)); ?>">
        <span class="glyphicon glyphicon-stop"></span>
        <?php echo JText::_("COM_CROWDFUNDING_STOP");?>
    </a>

<?php } ?>
