<?php
/**
 * @package      Crowdfunding
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// no direct access
defined('_JEXEC') or die;?>
<div class="cfprojects<?php echo $this->pageclass_sfx;?>">
    <?php if ($this->params->get('show_page_heading', 1)) { ?>
    <h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
    <?php } ?>
    
    <form action="<?php echo JRoute::_('index.php?option=com_crowdfunding&view=projects'); ?>" method="post" name="adminForm" id="adminForm">
    
        <table class="table table-striped table-bordered cf-projects-list">
            <thead>
            	<tr>
            		<th><?php echo JHtml::_('crowdfunding.sort',  'COM_CROWDFUNDING_TITLE', 'a.title', $this->listDirn, $this->listOrder); ?></th>
            		<th class="nowrap hidden-phone"><?php echo JHtml::_('crowdfunding.sort',  'COM_CROWDFUNDING_GOAL', 'a.goal', $this->listDirn, $this->listOrder); ?></th>
            		<th class="nowrap"><?php echo JHtml::_('crowdfunding.sort',  'COM_CROWDFUNDING_FUNDED', 'a.funded', $this->listDirn, $this->listOrder); ?></th>
            		<th class="nowrap hidden-phone"><?php echo JHtml::_('crowdfunding.sort', 'COM_CROWDFUNDING_STARTING_DATE', 'a.funding_start', $this->listDirn, $this->listOrder); ?></th>
            		<th class="nowrap hidden-phone"><?php echo JHtml::_('crowdfunding.sort', 'COM_CROWDFUNDING_DURATION', 'a.funding_end', $this->listDirn, $this->listOrder); ?></th>
            		<th><?php echo JText::_("COM_CROWDFUNDING_LAUNCHED"); ?></th>
            		<th class="nowrap hidden-phone"><?php echo JText::_("COM_CROWDFUNDING_APPROVED"); ?></th>
            		<th class="nowrap hidden-phone">&nbsp;</th>
            	</tr>
            </thead>
            <tfoot></tfoot>
            
            <tbody>
            	<?php foreach($this->items as $item) {
            	    
            		$goal           = $this->amount->setValue($item->goal)->formatCurrency();
            		$funded         = $this->amount->setValue($item->funded)->formatCurrency();
            		$fundedPercent  = JHtml::_("crowdfunding.percents", $item->goal, $item->funded);
            	    
            	    // Reverse state.
            	    $state = (!$item->published) ? 1 : 0;
            	?>
            	<tr>
            		<td>
            		    <?php echo JHtml::_("crowdfunding.projectTitle", $item->title, $item->catstate, $item->slug, $item->catslug);?>
            		</td>
            		<td class="text-center hidden-phone"><?php echo $goal; ?></td>
            		<td class="text-center">
                        <span class="hasTooltip cursor-help" title="<?php echo JText::sprintf("COM_CROWDFUNDING_PERCENTS_FUNDED", $fundedPercent);?>"><?php echo $funded; ?></span>
                    </td>
            		<td class="text-center hidden-phone">
                        <?php echo JHtml::_("crowdfunding.date", $item->funding_start, JText::_('DATE_FORMAT_LC3')); ?>
                    </td>
            		<td class="text-center hidden-phone">
                        <?php echo JHtml::_("crowdfunding.duration", $item->funding_end, $item->funding_days, JText::_('DATE_FORMAT_LC3')); ?>
                    </td>
            		<td class="text-center">
            		    <?php echo JHtml::_("crowdfunding.state", $item->published, JRoute::_("index.php?option=com_crowdfunding&task=projects.savestate&id=".$item->id."&state=".$state."&".JSession::getFormToken()."=1"), true)?>
            		</td>
            		<td class="text-center hidden-phone">
            		    <?php echo JHtml::_("crowdfunding.approved", $item->approved); ?>
            		</td>
            		<td class="hidden-phone">
            			<a href="<?php echo JRoute::_(CrowdfundingHelperRoute::getFormRoute($item->id)) ;?>" class="btn btn-primary btn-sm">
            			    <span class="glyphicon glyphicon-edit"></span>
            			    <?php echo JText::_("COM_CROWDFUNDING_EDIT");?>
        			    </a>
                        <a href="<?php echo JRoute::_(CrowdfundingHelperRoute::getFormRoute($item->id, "manager")) ;?>" class="btn btn-default btn-sm">
                            <span class="glyphicon glyphicon-wrench"></span>
                            <?php echo JText::_("COM_CROWDFUNDING_MANAGER");?>
                        </a>
            		</td>
            	</tr>
            	<?php }?>
            </tbody>
        
        </table>
        
        <input type="hidden" name="task" value="" />
        <input type="hidden" name="filter_order" value="<?php echo $this->listOrder; ?>" />
        <input type="hidden" name="filter_order_Dir" value="<?php echo $this->listDirn; ?>" />
        <?php echo JHtml::_('form.token'); ?>
    </form>
</div>