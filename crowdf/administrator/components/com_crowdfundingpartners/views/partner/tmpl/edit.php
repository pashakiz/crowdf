<?php
/**
 * @package      Crowdfunding
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2014 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// no direct access
defined('_JEXEC') or die;
?>
<div class="row-fluid">
    <div class="span6 form-horizontal">
        <form action="<?php echo JRoute::_('index.php?option=com_crowdfundingpartners'); ?>" method="post" name="adminForm" id="adminForm" class="form-validate">

            <?php echo $this->form->getControlGroup('name'); ?>
            <?php echo $this->form->getControlGroup('partner_id'); ?>
            <?php echo $this->form->getControlGroup('project_id'); ?>
            <?php echo $this->form->getControlGroup('id'); ?>

            <input type="hidden" name="task" value=""/>
            <?php echo JHtml::_('form.token'); ?>
        </form>
    </div>
</div>