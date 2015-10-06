<?php
/**
 * @package      Crowdfunding
 * @subpackage   Modules
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
 
// no direct access
defined('_JEXEC') or die; 
?>
<div class="cfinfo<?php echo $moduleclassSfx; ?>">
    <div class="cfinfo-raised">
    	<?php echo $amount->setValue($project->getFunded())->formatCurrency(); ?>
    </div>
    <div class="cfinfo-raised-of">
        <?php echo JText::sprintf("MOD_CROWDFUNDINGINFO_RAISED_OF", $fundedAmount);?>
	</div>
    <?php echo JHtml::_("crowdfunding.progressbar", $project->getFundedPercent(), $project->getDaysLeft(), $project->getFundingType()); ?>
	<div class="cfinfo-days-raised">
    	<div class="cfinfo-days-wrapper">
    		<div class="cfinfo-days">
        		<img src="media/com_crowdfunding/images/clock.png" width="25" height="25" />
        		<?php echo $project->getDaysLeft();?>
    		</div>
    		<div class="text-center fzmfwbu"><?php echo JText::_("MOD_CROWDFUNDINGINFO_DAYS_LEFT");?></div>
		</div>
		<div class="cfinfo-percent-wrapper">
			<div class="cfinfo-percent">
    			<img src="media/com_crowdfunding/images/piggy-bank.png" width="27" height="20" />
        		<?php echo $project->getFundedPercent();?>%
    		</div>
    		<div class="text-center fzmfwbu"><?php echo JText::_("MOD_CROWDFUNDINGINFO_FUNDED");?></div>
		</div>
	</div>
	<div class="clearfix"></div>
    <div class="cfinfo-funding-type">
        <?php echo JText::_("MOD_CROWDFUNDINGINFO_FUNDING_TYPE_". JString::strtoupper($project->getFundingType())); ?>
    </div>
    
	<?php if($project->isCompleted()) {?>
	<div class="well">
		<div class="cf-fund-result-state pull-center"><?php echo JHtml::_("crowdfunding.resultState", $project->getFundedPercent(), $project->getFundingType());?></div>
		<div class="cf-frss pull-center"><?php echo JHtml::_("crowdfunding.resultStateText", $project->getFundedPercent(), $project->getFundingType());?></div>
	</div>
	<?php } else {?>
	<div class="cfinfo-funding-action">
		<a class="btn btn-default btn-large btn-block" href="<?php echo JRoute::_(CrowdfundingHelperRoute::getBackingRoute($project->getSlug(), $project->getCatSlug()));?>">
            <?php echo JText::_("MOD_CROWDFUNDINGINFO_INVEST_NOW"); ?>
        </a>
	</div>
	<?php }?>
    
    <div class="cfinfo-funding-type-info">
    	<?php
    	$endDate = JHtml::_('crowdfunding.date', $project->getFundingEnd(), JText::_('DATE_FORMAT_LC3'));
    	
    	if("FIXED" == $project->getFundingType()) {
    	    echo JText::sprintf("MOD_CROWDFUNDINGINFO_FUNDING_TYPE_INFO_FIXED", $fundedAmount, $endDate);
    	} else {
    	    echo JText::sprintf("MOD_CROWDFUNDINGINFO_FUNDING_TYPE_INFO_FLEXIBLE", $endDate);
    	}
    	?>
    </div>
</div>