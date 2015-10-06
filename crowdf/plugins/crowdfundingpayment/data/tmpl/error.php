<?php
/**
 * @package      CrowdfundingData
 * @subpackage   Plugins
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined('_JEXEC') or die;
?>
<p class="alert">
    <i class="icon-warning-sign"></i>
    <?php echo JText::_("PLG_CROWDFUNDINGPAYMENT_ERROR_INVALID_DATA"); ?>
</p>
<a href="<?php echo JRoute::_(CrowdfundingHelperRoute::getBackingRoute($item->slug, $item->catslug)); ?>" class="btn btn-primary" >
    <i class="icon-chevron-left"></i>
    <?php echo JText::_("PLG_CROWDFUNDINGPAYMENT_BUTTON_STEP_ONE"); ?>
</a>