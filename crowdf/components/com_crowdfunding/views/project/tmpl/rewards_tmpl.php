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
<div class="row reward-form" id="reward_tmpl" style="display: none;">
    <div class="col-md-2 reward-form-help"><?php echo JText::_("COM_CROWDFUNDING_REWARDS_REWARD"); ?></div>
    <div class="col-md-6">

        <div class="form-group">
    	    <label for="reward_amount_d" id="reward_amount_label_d"><?php echo JText::_("COM_CROWDFUNDING_AMOUNT"); ?><span class="star">&nbsp;*</span></label>
            <div class="input-group">
                <?php if($this->currency->getSymbol()){?>
                <div class="input-group-addon"><?php echo $this->currency->getSymbol();?></div>
                <?php }?>
                <input name="rewards[][amount]" id="reward_amount_d" type="text" value=""  class="form-control"/>
                <div class="input-group-addon"><?php echo $this->currency->getCode();?></div>
            </div>
        </div>

        <div class="form-group">
        <label for="reward_title_d" id="reward_title_title_d"><?php echo JText::_("COM_CROWDFUNDING_TITLE"); ?><span class="star">&nbsp;*</span></label>
        <input name="rewards[][title]" id="reward_title_d" type="text" value="" class="form-control" />
        </div>

        <div class="form-group">
        <label for="reward_description_d" id="reward_description_title_d"><?php echo JText::_("COM_CROWDFUNDING_DESCRIPTION"); ?><span class="star">&nbsp;*</span></label>
        <textarea name="rewards[][description]" id="reward_description_d" rows="6" class="form-control"></textarea>
        </div>

        <div class="form-group">
        <label for="reward_number_d" id="reward_number_title_d"><?php echo JText::_("COM_CROWDFUNDING_AVAIABLE");?></label>
        <input name="rewards[][number]" id="reward_number_d" type="text" value="" />
        </div>

        <div class="form-group">
            <label for="reward_delivery_d" id="reward_delivery_title_d"><?php echo JText::_("COM_CROWDFUNDING_REWARDS_ESTIMATED_DELIVERY");?></label>
            <?php echo JHtml::_('prism.ui.calendar', "", "rewards[][delivery]", "reward_delivery_d", $this->dateFormat, array("class" => "form-control"));?>
        </div>

        <input name="rewards[][id]" type="hidden" value="" id="reward_id_d" />
    </div>

    <div class="col-md-4">
        <div class="row">
            <div class="col-md-12 text-right">
                <?php if(!$this->debugMode) {?>
                    <a href="#" class="btn btn-danger js-btn-remove-reward mt-10" title="<?php echo JText::_("COM_CROWDFUNDING_REMOVE_REWARD")?>" id="reward_remove_d" data-reward-id="0" data-index-id="0" >
                        <span class="glyphicon glyphicon-remove"></span>
                    </a>
                <?php }?>
            </div>
        </div>
    </div>
</div>
