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
    <form action="<?php echo JRoute::_('index.php?option=com_crowdfunding'); ?>" method="post" name="projectForm" id="crowdf-rewards-form" class="form-validate" enctype="multipart/form-data" autocomplete="off">
        <div id="rewards_wrapper">
        <?php
        if (!$this->items) { // Display first form
            $this->formItem  = array();
            $this->formIndex = 1;
            echo $this->loadTemplate("form");
        } else {
            $this->formIndex = 1;
            foreach ($this->items as $item) {
                $item["amount"] = $this->amount->setValue($item["amount"])->format();
                $this->formItem  = $item;
                echo $this->loadTemplate("form");
                $this->formIndex++;
            }
        }?>
        </div>
        
        <input type="hidden" name="task" value="rewards.save" />
        <input type="hidden" name="id" value="<?php echo $this->projectId;?>" />
        <?php echo JHtml::_('form.token'); ?>

        <input type="hidden" name="items_number" id="items_number" value="<?php echo (0 == count($this->items)) ? 1 : count($this->items);?>" />
        <?php if(!$this->debugMode and $this->rewardsEnabledViaType) {?>
        <button class="btn btn-default btn-lg btn-block" type="button" id="cf_add_new_reward"><?php echo JText::_("COM_CROWDFUNDING_REWARDS_ADD_REWARD");?></button>
        <?php }?>
        
        <?php if (!$this->rewardsEnabledViaType) {
            // Get next layout
            $nextLayout = (strcmp($this->params->get("project_wizard_type", "five_steps"), "five_steps") == 0) ? "manager" : "extras";
            ?>
            <p class="alert alert-info mt-10">
                <span class="glyphicon glyphicon-info"></span>
                <?php echo JText::_("COM_CROWDFUNDING_NOTE_REWARDS_CREATING_NOT_ALLOWED");?>
            </p>

            <div class="cf-rewards-submit-btn">
                <a class="btn btn-primary" href="<?php echo JRoute::_(CrowdfundingHelperRoute::getFormRoute($this->item->id, $nextLayout)); ?>">
                    <span class="glyphicon glyphicon-ok"></span>
                    <?php echo JText::_("COM_CROWDFUNDING_CONTINUE");?>
                </a>
            </div>

        <?php } else { ?>
        
            <div class="cf-rewards-submit-btn">
                <button class="btn btn-primary" <?php echo $this->disabledButton;?> name="btn_submit" value="save" type="submit">
                    <span class="glyphicon glyphicon-ok"></span>
                    <?php echo JText::_("COM_CROWDFUNDING_SAVE_REWARDS");?>
                </button>

                <button class="btn btn-default" <?php echo $this->disabledButton;?> name="btn_submit" value="save_continue" type="submit">
                    <span class="glyphicon glyphicon-ok"></span>
                    <?php echo JText::_("COM_CROWDFUNDING_SAVE_AND_CONTINUE");?>
                </button>
            </div>
        <?php } ?>

    </form>
</div>
<?php echo $this->loadTemplate("tmpl");?>