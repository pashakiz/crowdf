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

<?php
if (!empty($this->item->event->onExtrasDisplay)) {
    echo $this->item->event->onExtrasDisplay;
}
?>

<div class="row">
    <div class="col-md-12">
        <a class="btn btn-primary" <?php echo $this->disabledButton;?> href="<?php echo JRoute::_("index.php?option=com_crowdfunding&view=project&layout=manager&id=".(int)$this->item->id); ?>">
            <span class="glyphicon glyphicon-ok"></span>
            <?php echo JText::_("COM_CROWDFUNDING_CONTINUE_NEXT_STEP");?>
        </a>
    </div>
</div>
