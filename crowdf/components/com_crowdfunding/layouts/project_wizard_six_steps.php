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

$id = (!empty($displayData["item_id"])) ? "&id=".(int)$displayData["item_id"] : "";

$active = array("basic" => false, "funding" => false, "story" => false, "rewards" => false, "extras" => false, "manager" => false);
switch($displayData["layout"]) {
    case "default":
        $active["basic"] = true;
        break;
    case "funding":
        $active["funding"] = true;
        break;
    case "story":
        $active["story"] = true;
        break;
    case "rewards":
        $active["rewards"] = true;
        break;
    case "extras":
        $active["extras"] = true;
        break;
    case "manager":
        $active["manager"] = true;
        break;
}
?>
<div class="navbar navbar-default" role="navigation">
    <div class="container-fluid">
        <div class="navbar-header">
            <a class="navbar-brand" href="<?php echo JRoute::_("index.php?option=com_crowdfunding&view=project".$id);?>">
                <?php echo JText::_("COM_CROWDFUNDING_WIZARD");?>
            </a>
        </div>
        <div class="collapse navbar-collapse">
            <ul class="nav navbar-nav">
                <li <?php echo ($active["basic"]) ? 'class="active"' : '';?>>
                    <a href="<?php echo JRoute::_("index.php?option=com_crowdfunding&view=project".$id);?>"><?php echo JText::_("COM_CROWDFUNDING_STEP_BASIC");?></a>
                </li>

                <li <?php echo ($active["funding"]) ? 'class="active"' : '';?>>
                    <?php if(!empty($displayData["item_id"])){?>
                        <a href="<?php echo JRoute::_("index.php?option=com_crowdfunding&view=project&layout=funding".$id);?>"><?php echo JText::_("COM_CROWDFUNDING_STEP_FUNDING");?></a>
                    <?php }else {?>
                        <a href="javascript: void(0);" class="disabled"><?php echo JText::_("COM_CROWDFUNDING_STEP_FUNDING");?></a>
                    <?php }?>
                </li>

                <li <?php echo ($active["story"]) ? 'class="active"' : '';?>>
                    <?php if(!empty($displayData["item_id"])){?>
                        <a href="<?php echo JRoute::_("index.php?option=com_crowdfunding&view=project&layout=story".$id);?>"><?php echo JText::_("COM_CROWDFUNDING_STEP_STORY");?></a>
                    <?php }else {?>
                        <a href="javascript: void(0);" class="disabled"><?php echo JText::_("COM_CROWDFUNDING_STEP_STORY");?></a>
                    <?php }?>
                </li>

                <?php if ($displayData["rewards_enabled"]) { ?>
                <li <?php echo ($active["rewards"]) ? 'class="active"' : '';?>>
                    <?php if(!empty($displayData["item_id"])){?>
                        <a href="<?php echo JRoute::_("index.php?option=com_crowdfunding&view=project&layout=rewards".$id);?>"><?php echo JText::_("COM_CROWDFUNDING_STEP_REWARDS");?></a>
                    <?php }else {?>
                        <a href="javascript: void(0);" class="disabled"><?php echo JText::_("COM_CROWDFUNDING_STEP_REWARDS");?></a>
                    <?php }?>
                </li>
                <?php }?>

                <li <?php echo ($active["extras"]) ? 'class="active"' : '';?>>
                    <?php if(!empty($displayData["item_id"])){?>
                        <a href="<?php echo JRoute::_("index.php?option=com_crowdfunding&view=project&layout=extras".$id);?>"><?php echo JText::_("COM_CROWDFUNDING_STEP_EXTRAS");?></a>
                    <?php }else {?>
                        <a href="javascript: void(0);" class="disabled"><?php echo JText::_("COM_CROWDFUNDING_STEP_EXTRAS");?></a>
                    <?php }?>
                </li>

                <li <?php echo ($active["manager"]) ? 'class="active"' : '';?>>
                    <?php if(!empty($displayData["item_id"])){?>
                        <a href="<?php echo JRoute::_("index.php?option=com_crowdfunding&view=project&layout=manager".$id);?>"><?php echo JText::_("COM_CROWDFUNDING_STEP_MANAGER");?></a>
                    <?php }else {?>
                        <a href="javascript: void(0);" class="disabled"><?php echo JText::_("COM_CROWDFUNDING_STEP_MANAGER");?></a>
                    <?php }?>
                </li>
            </ul>
        </div>
    </div>
</div>