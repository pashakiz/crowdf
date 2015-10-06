<?php
/**
 * @package      CrowdfundingData
 * @subpackage   Plugins
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined('_JEXEC') or die;

// Load the script that initializes the select element with banks.
$doc->addScript("plugins/crowdfundingpayment/data/js/script.js?v=" . rawurlencode($this->version));

?>
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <?php echo JText::_("PLG_CROWDFUNDINGPAYMENT_DATA_INFORMATION_ABOUT_YOU");?>
            </div>
            <div class="panel-body">
                <form action="index.php?option=com_crowdfundingdata" method="post" id="js-cfdata-form">
                    <div class="form-group">
                        <?php echo $this->form->getLabel("name"); ?>
                        <?php echo $this->form->getInput("name"); ?>
                    </div>
                    <div class="form-group">
                        <?php echo $this->form->getLabel("email"); ?>
                        <?php echo $this->form->getInput("email"); ?>
                    </div>
                    <div class="form-group">
                        <?php echo $this->form->getLabel("address"); ?>
                        <?php echo $this->form->getInput("address"); ?>
                    </div>
                    <div class="form-group">
                        <?php echo $this->form->getLabel("country_id"); ?>
                        <?php echo $this->form->getInput("country_id"); ?>
                    </div>

                    <div class="bg-info p-5 hide" id="js-cfdata-btn-alert">
                        <span class="glyphicon glyphicon-info-sign"></span>
                    </div>

                    <button type="submit" class="btn btn-primary" id="js-cfdata-btn-submit">
                        <?php echo JText::_('PLG_CROWDFUNDINGPAYMENT_DATA_SUBMIT'); ?>
                    </button>
                    <img src="media/com_crowdfunding/images/ajax-loader.gif" width="16" height="16" id="js-cfdata-ajax-loading" style="display: none;" />

                    <a href="#" class="btn btn-success" id="js-continue-btn" role="button" style="display: none;">
                        <span class="glyphicon glyphicon-chevron-right"></span>
                        <?php echo JText::_("PLG_CROWDFUNDINGPAYMENT_DATA_CONTINUE_NEXT_STEP"); ?>
                    </a>

                    <?php echo $this->form->getInput("project_id"); ?>
                    <input type="hidden" name="task" value="record.save" />
                    <input type="hidden" name="format" value="raw" />

                    <?php if ($componentParams->get("backing_terms", 0) and !empty($this->terms)) { ?>
                        <input type="hidden" name="terms" value="1" />
                    <?php } ?>

                    <?php echo JHtml::_('form.token'); ?>
                </form>
            </div>
        </div>
    </div>
</div>