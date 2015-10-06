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
<?php if (!empty($this->item)) { ?>}
<div class="row-fluid">
    <ul class="thumbnails">
      <li class="span12">
        <div class="thumbnail">
          <img src="<?php echo $this->item->link_image;?>" alt="<?php echo $this->escape($this->item->title);?>" width="<?php echo $this->params->get("image_width"); ?>" height="<?php echo $this->params->get("image_height"); ?>">
          <div class="caption">
            <h3><a href="<?php echo JRoute::_( CrowdfundingHelperRoute::getDetailsRoute($this->item->slug, $this->item->catslug) ); ?>" target="_blank"><?php echo $this->item->title;?></a></h3>
            <span class="cf-founder">
                by <?php echo JHtml::_("crowdfunding.socialProfileLink", $socialProfileLink, $this->item->user_name, array("target" => "_blank")); ?>
            </span>
            <p><?php echo $this->item->short_desc;?></p>
            <div class="progress progress-success">
           		<div class="bar" style="width: <?php echo ($this->item->funded_percents > 100) ? 100 : $this->item->funded_percents;?>%"></div>
            </div>
            <div class="row-fluid">
            	<div class="span4">
                    <div><strong><?php echo $this->item->funded_percents;?>%</strong></div>
                    <?php echo strtoupper( JText::_("COM_CROWDFUNDING_FUNDED") );?>
            	</div>
            	<div class="span4">
                    <div><strong><?php echo $this->amount->setValue($this->item->funded)->formatCurrency();?></strong></div>
                    <?php echo strtoupper( JText::_("COM_CROWDFUNDING_RAISED") );?>
            	</div>
            	<div class="span4">
                    <div><strong><?php echo $this->item->days_left;?></strong></div>
                    <?php echo strtoupper( JText::_("COM_CROWDFUNDING_DAYS_LEFT") );?>
            	</div>
            </div>
          </div>
        </div>
      </li>
    </ul>
</div>
<?php } ?>
    