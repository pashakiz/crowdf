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

<div class="control-group">
    <div class="control-label"><?php echo $this->form->getLabel('goal'); ?></div>
    <div class="controls"><?php echo $this->form->getInput('goal'); ?></div>
</div>
<div class="control-group">
    <div class="control-label"><?php echo $this->form->getLabel('funded'); ?></div>
    <div class="controls"><?php echo $this->form->getInput('funded'); ?></div>
</div>

<div class="control-group">
    <div class="control-label"><?php echo $this->form->getLabel('funding_type'); ?></div>
    <div class="controls"><?php echo $this->form->getInput('funding_type'); ?></div>
</div>

<div class="control-group">
    <div class="control-label"><?php echo $this->form->getLabel('funding_start'); ?></div>
    <div class="controls"><?php echo $this->form->getInput('funding_start'); ?></div>
</div>

<div class="control-group">
    <div class="control-label">
        <label for="jform_funding_duration_type" id="jform_funding_duration_type-lbl">
            <?php echo JText::_("COM_CROWDFUNDING_FIELD_FUNDING_DURATION"); ?>
        </label>
    </div>
    <div class="controls">
        <?php
        if (empty($this->fundingDuration) or (strcmp("days", $this->fundingDuration) == 0)) { ?>
            <input type="radio" value="days" name="jform[funding_duration_type]" id="js-funding-duration-days" <?php echo $this->checkedDays; ?>>
            <?php echo $this->form->getLabel('funding_days'); ?>
            <div class="clearfix"></div>
            <?php echo $this->form->getInput('funding_days'); ?>
        <?php
        } ?>

        <br/><br/><br/>

        <?php
        if (empty($this->fundingDuration) or (strcmp("date", $this->fundingDuration) == 0)) { ?>
            <div class="clearfix"></div>
            <input type="radio" value="date" name="jform[funding_duration_type]" id="js-funding-duration-date" <?php echo $this->checkedDate; ?>>
            <?php echo $this->form->getLabel('funding_end'); ?>
            <div class="clearfix"></div>
            <?php echo $this->form->getInput('funding_end'); ?>
        <?php
        } ?>
    </div>
</div>