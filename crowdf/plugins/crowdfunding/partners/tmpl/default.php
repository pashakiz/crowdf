<?php
/**
 * @package      CrowdfundingPartners
 * @subpackage   Plugins
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined('_JEXEC') or die;

// Load the script that initializes the select element with banks.
$doc->addScript("plugins/crowdfunding/partners/js/script.js?v=" . rawurlencode($this->version));
?>

<div class="row">
    <div class="col-md-12 well">

        <h3><?php echo JText::_("PLG_CROWDFUNDING_PARTNERS_PARTNERS");?></h3>

        <form action="<?php echo JRoute::_("index.php?option=com_crowdfundingpartners&task=partners.addPartner&format=raw"); ?>" method="post" class="form-search" id="js-cfpartners-form" autocomplete="off">

            <div class="form-group">
                <label for="cf-partners-username" class="sr-only"><?php echo JText::_("PLG_CROWDFUNDING_PARTNERS_USERNAME_EMAIL");?></label>
                <input type="text" name="username" id="cf-partners-username" class="form-control" placeholder="<?php echo JText::_("PLG_CROWDFUNDING_PARTNERS_ENTER_USERNAME");?>" >
            </div>

            <button class="btn btn-primary" type="submit">
                <span class="glyphicon glyphicon-plus-sign"></span>
                <?php echo JText::_("PLG_CROWDFUNDING_PARTNERS_ADD_PARTNER");?>
            </button>
            <img src="/media/com_crowdfunding/images/ajax-loader.gif" width="16" height="16" id="js-cfpartners-ajax-loader" class="hide" />

            <input type="hidden" name="project_id" value="<?php echo (int)$item->id; ?>" />
            <input type="hidden" name="image_size" value="<?php echo $this->params->get("image_size", "small"); ?>" />
        </form>

        <table class="table table-bordered mtb-25-0">
            <thead>
            <tr>
                <th class="col-md-1">&nbsp;</th>
                <th class="col-md-9"><?php echo JText::_("PLG_CROWDFUNDING_PARTNERS_NAME");?></th>
                <th class="col-md-2">&nbsp;</th>
            </tr>
            </thead>
            <tbody id="js-cfpartners-list">
            <?php foreach ($partners as $partner) { ?>
                <tr id="js-cfpartners-partner<?php echo $partner["id"]; ?>">
                    <td>
                        <img src="<?php echo $partner["avatar"]; ?>" width="<?php echo $this->params->get("width", 50); ?>" height="<?php echo $this->params->get("height", 50); ?>" />
                    </td>
                    <td>
                        <?php echo htmlentities($partner["name"], ENT_QUOTES, "UTF-8"); ?>
                    </td>
                    <td>
                        <a class="btn btn-danger js-cfpartners-btn-remove" href="<?php echo JRoute::_("index.php?option=com_crowdfundingpartners&task=partners.remove&format=raw");?>" data-partner-id="<?php echo (int)$partner["id"]; ?>">
                            <span class="glyphicon glyphicon-trash"></span>
                            <span class="hidden-xs"><?php echo JText::_("PLG_CROWDFUNDING_PARTNERS_REMOVE");?></span>
                        </a>
                    </td>
                </tr>
            <?php } ?>

            <tr style="display: none;" id="js-cfpartners-element">
                <td>
                    <img src="" width="<?php echo $this->params->get("width", 50); ?>" height="<?php echo $this->params->get("height", 50); ?>" />
                </td>
                <td>{NAME}</td>
                <td>
                    <a class="btn btn-danger" href="<?php echo JRoute::_("index.php?option=com_crowdfundingpartners&task=partners.remove&format=raw");?>">
                        <span class="glyphicon glyphicon-trash"></span>
                        <span class="hidden-xs"><?php echo JText::_("PLG_CROWDFUNDING_PARTNERS_REMOVE");?></span>
                    </a>
                </td>
            </tr>

            </tbody>
        </table>

    </div>

</div>

