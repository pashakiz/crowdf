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

// Prepare availability number
$availability = Joomla\Utilities\ArrayHelper::getValue($this->formItem, "number", 0);
if (!$availability) {
    $availability = "";
}

// Prepare delivery date
$deliveryDate = Joomla\Utilities\ArrayHelper::getValue($this->formItem, "delivery", null);
if (!empty($deliveryDate)) {

    $dateValidator = new Prism\Validator\Date($deliveryDate);

    if (!$dateValidator->isValid()) {
        $deliveryDate = null;
    } else { // Formatting date
        $date = new JDate($deliveryDate);
        $deliveryDate = $date->format($this->dateFormat);
    }
}

?>
<div class="row reward-form" id="reward_box_<?php echo $this->formIndex;?>">
    <div class="col-md-2 reward-form-help"><?php echo JText::_("COM_CROWDFUNDING_REWARDS_REWARD"); ?></div>
    <div class="col-md-6">
        <div class="form-group">
    	    <label class="hasTooltip" data-placement="left" for="reward_amount_<?php echo $this->formIndex;?>" title="<?php echo JText::_("COM_CROWDFUNDING_REWARDS_AMOUNT_DESC");?>"><?php echo JText::_("COM_CROWDFUNDING_AMOUNT"); ?><span class="star">&nbsp;*</span></label>
            <div class="input-group">
                <?php if($this->currency->getSymbol()){?>
                <div class="input-group-addon"><?php echo $this->currency->getSymbol();?></div>
                <?php }?>
                <input name="rewards[<?php echo $this->formIndex;?>][amount]" id="reward_amount_<?php echo $this->formIndex;?>" type="text" value="<?php echo Joomla\Utilities\ArrayHelper::getValue($this->formItem,  "amount")?>" class="form-control" />
                <div class="input-group-addon"><?php echo $this->currency->getCode();?></div>
            </div>
        </div>

        <div class="form-group">
        <label class="hasTooltip" data-placement="left" for="reward_title_<?php echo $this->formIndex;?>" title="<?php echo JText::_("COM_CROWDFUNDING_REWARDS_TITLE_DESC");?>"><?php echo JText::_("COM_CROWDFUNDING_TITLE"); ?><span class="star">&nbsp;*</span></label>
        <input name="rewards[<?php echo $this->formIndex;?>][title]" id="reward_title_<?php echo $this->formIndex;?>" type="text" value="<?php echo Joomla\Utilities\ArrayHelper::getValue($this->formItem,  "title")?>" class="form-control" />
        </div>

        <div class="form-group">
        <label class="hasTooltip" data-placement="left" for="reward_description_<?php echo $this->formIndex;?>" title="<?php echo JText::_("COM_CROWDFUNDING_REWARDS_DESCRIPTION_DESC");?>"><?php echo JText::_("COM_CROWDFUNDING_DESCRIPTION"); ?><span class="star">&nbsp;*</span></label>
        <textarea name="rewards[<?php echo $this->formIndex;?>][description]" id="reward_description_<?php echo $this->formIndex;?>" rows="6" class="form-control"><?php echo Joomla\Utilities\ArrayHelper::getValue($this->formItem,  "description")?></textarea>
        </div>

        <div class="form-group">
        <label class="hasTooltip" data-placement="left" for="reward_number_<?php echo $this->formIndex;?>" title="<?php echo JText::_("COM_CROWDFUNDING_REWARDS_AVAIABLE_DESC");?>"><?php echo JText::_("COM_CROWDFUNDING_AVAIABLE"); ?></label>
        <input name="rewards[<?php echo $this->formIndex;?>][number]" id="reward_number_<?php echo $this->formIndex;?>" type="text" value="<?php echo $availability; ?>" />
        </div>

        <div class="form-group">
        <label class="hasTooltip" data-placement="left" for="reward_delivery_<?php echo $this->formIndex;?>" title="<?php echo JText::_("COM_CROWDFUNDING_REWARDS_ESTIMATED_DELIVERY_DESC");?>"><?php echo JText::_("COM_CROWDFUNDING_REWARDS_ESTIMATED_DELIVERY");?></label>
        <?php echo JHtml::_('prism.ui.calendar', $deliveryDate, "rewards[".$this->formIndex."][delivery]", "reward_delivery_".$this->formIndex, $this->dateFormat, array("class" => "form-control"));?>
        </div>

        <input name="rewards[<?php echo $this->formIndex;?>][id]" type="hidden" value="<?php echo Joomla\Utilities\ArrayHelper::getValue($this->formItem,  "id", 0)?>" />


    </div>

    <div class="col-md-4">
        <div class="row">
            <div class="col-md-12 text-right">
                <?php if(!$this->debugMode) {?>
                    <a href="javascript: void(0);" class="btn btn-danger js-btn-remove-reward mt-10" title="<?php echo JText::_("COM_CROWDFUNDING_REMOVE_REWARD")?>" data-reward-id="<?php echo Joomla\Utilities\ArrayHelper::getValue($this->formItem,  "id")?>" data-index-id="<?php echo $this->formIndex;?>" >
                        <span class="glyphicon glyphicon-remove"></span>
                    </a>
                <?php }?>
            </div>
        </div>
        <div class="row">
            <?php if(!empty($this->rewardsImagesEnabled) AND !empty($this->formItem)) {
                echo $this->loadTemplate("image");
            } ?>
        </div>
    </div>



</div>
