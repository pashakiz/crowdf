<?php
/**
 * @package      Crowdfunding
 * @subpackage   Plugins
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined('_JEXEC') or die;

// Load initialization script.
$doc->addScript("plugins/crowdfundingpayment/banktransfer/js/script.js?v=".rawurlencode($this->version));
?>
<div class="well">
    <h4>
        <img width="30" height="26" src="plugins/crowdfundingpayment/banktransfer/images/bank_icon.png" />
        <?php echo JText::_($this->textPrefix . "_TITLE"); ?>
    </h4>

<?php
// Check for valid beneficiary information. If missing information, display error message.
$beneficiaryInfo = JString::trim(strip_tags($this->params->get("beneficiary")));
if (!$beneficiaryInfo) {?>
    <div class="alert"><?php echo JText::_($this->textPrefix . "_ERROR_PLUGIN_NOT_CONFIGURED"); ?></div>
    <?php
    return;
}?>

    <div><?php echo nl2br($this->params->get("beneficiary")); ?></div>

<?php
if ($this->params->get("display_additional_info", 1)) {
    $additionalInfo = JString::trim($this->params->get("additional_info"));

    if (!empty($additionalInfo)) {?>
    <p class="bg-info p-5"><span class="glyphicon glyphicon-info-sign"></span> <?php echo htmlspecialchars($additionalInfo, ENT_QUOTES, "UTF-8"); ?></p>
    <?php } else { ?>
    <p class="bg-info p-5"><span class="glyphicon glyphicon-info-sign"></span> <?php echo JText::_($this->textPrefix . "_INFO"); ?></p>
    <?php } ?>

<?php } ?>

    <div class="bg-info p-5 mb-10" id="js-bt-alert" style="display: none;"></div>

    <button class="btn btn-primary" id="js-register-bt" type="button" data-project-id="<?php echo $item->id; ?>" data-amount="<?php echo $item->amount; ?>">
        <?php echo JText::_($this->textPrefix . "_MAKE_BANK_TRANSFER"); ?>
    </button>
    <img src="media/com_crowdfunding/images/ajax-loader.gif" width="16" height="16" id="js-banktransfer-ajax-loading" style="display: none;" />
    <a href="#" class="btn btn-success" id="js-continue-bt" style="display: none;" role="button">
        <span class="glyphicon glyphicon-chevron-right"></span>
        <?php echo JText::_($this->textPrefix . "_CONTINUE_NEXT_STEP"); ?>
    </a>

</div>