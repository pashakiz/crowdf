<?php
/**
 * @package      SocialCommunity
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// no direct access
defined('_JEXEC') or die;?>
<div class="row" itemscope itemtype="http://schema.org/Person">
	<div class="col-md-4">
		<?php if(!$this->item->image){?>
		<img src="media/com_socialcommunity/images/no_profile_200x200.png" />
		<?php } else {?>
		<img src="<?php echo $this->imagesFolder."/".$this->item->image;?>" alt="<?php echo $this->item->name;?>" itemprop="image" />
		<?php }?>
		
		<?php if (0 < count($this->socialProfiles)) {?>
		<div class="clearfix">&nbsp;</div>
		<div class="sc-social-profiles">
		  <?php echo JHtml::_("socialcommunity.socialprofiles", $this->socialProfiles, $this->item)?>
		</div>
		<?php }?>

        <?php if (!empty($this->item->website)) {?>
            <div class="clearfix">&nbsp;</div>
            <a href="<?php echo $this->escape($this->item->website); ?>" class="sc-profile-link" target="_blank">
                <?php echo JHtmlString::truncate($this->item->website, 32, true, false); ?>
            </a>
        <?php }?>
		
		<?php if ($this->isOwner){?>
		<div class="clearfix">&nbsp;</div>
		<a href="<?php echo JRoute::_("index.php?option=com_socialcommunity&view=form");?>" class="btn btn-default">
		    <span class="glyphicon glyphicon-edit" ></span>
		    <?php echo JText::_("COM_SOCIALCOMMUNITY_EDIT_PROFILE");?>
	    </a>
		<?php }?>
	</div>
	<div class="col-md-8">
		<h3 itemprop="name"><?php echo $this->item->name;?></h3>
		<?php if (!empty($this->item->bio)) {?>
		<p class="about-bio"><?php echo $this->escape($this->item->bio);?></p>
		<?php }?>
		
		<?php if (!empty($this->displayContactInformation)) {?>
		<h4><?php echo JText::_("COM_SOCIALCOMMUNITY_CONTACT_INFORMATION");?></h4>
        <?php echo JHtml::_("socialcommunity.address", $this->item->address, $this->item->location, $this->item->country); ?>
        <?php echo JHtml::_("socialcommunity.phone", $this->item->phone); ?>
        <?php } ?>
	</div>
</div>
<div class="clearfix">&nbsp;</div>