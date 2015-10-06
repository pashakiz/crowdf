<?php
/**
 * @package      CrowdfundingFiles
 * @subpackage   Plugins
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined('_JEXEC') or die;

// Load the script that initializes the select element with banks.
$doc->addScript("plugins/crowdfunding/files/js/script.js?v=" . rawurlencode($this->version));
?>
<div class="row">
    <div class="col-md-12 well">

        <h3><?php echo JText::_("PLG_CROWDFUNDING_FILES_FILES");?></h3>

        <span class="btn btn-primary fileinput-button">
            <span class="glyphicon glyphicon-upload"></span>
            <span><?php echo JText::_("PLG_CROWDFUNDING_FILES_UPLOAD");?></span>
            <!-- The file input field used as target for the file upload widget -->
            <input id="js-cffiles-fileupload" type="file" name="files[]" data-url="<?php echo JRoute::_("index.php?option=com_crowdfundingfiles&task=files.upload&format=raw");?>" multiple />
        </span>
        <img src="/media/com_crowdfunding/images/ajax-loader.gif" width="16" height="16" id="js-cffiles-ajax-loader" class="hide" />

        <input type="hidden" name="project_id" value="<?php echo (int)$item->id; ?>" id="js-cffiles-project-id" />

        <?php if ($this->params->get("display_note", 1)) { ?>
        <div class="bg-info p-5 mt-5">
            <h4>
                <span class="glyphicon glyphicon-info-sign"></span>
                <?php echo JText::_("PLG_CROWDFUNDING_FILES_INFORMATION"); ?>
            </h4>
            <p><?php echo JText::sprintf("PLG_CROWDFUNDING_FILES_FILE_TYPES_NOTE", $this->params->get("files_type_info")); ?></p>
            <p><?php echo JText::sprintf("PLG_CROWDFUNDING_FILES_FILE_SIZE_NOTE", $componentParams->get("max_size")); ?></p>
        </div>
        <?php } ?>

        <table class="table table-bordered mtb-25-0">
            <thead>
            <tr>
                <th class="col-md-7"><?php echo JText::_("PLG_CROWDFUNDING_FILES_TITLE");?></th>
                <th class="col-md-2"><?php echo JText::_("PLG_CROWDFUNDING_FILES_FILENAME");?></th>
                <th class="col-md-3">&nbsp;</th>
            </tr>
            </thead>
            <tbody id="js-cffiles-list">
            <?php foreach ($files as $file) { ?>
                <tr id="js-cffiles-file<?php echo $file["id"]; ?>">
                    <td><?php echo htmlentities($file["title"], ENT_QUOTES, "UTF-8"); ?></td>
                    <td><?php echo htmlentities($file["filename"], ENT_QUOTES, "UTF-8"); ?></td>
                    <td>
                        <a class="btn btn-default hidden-xs" href="<?php echo $mediaUri."/".$file["filename"];?>" download>
                            <span class="glyphicon glyphicon-download"></span>
                            <span><?php echo JText::_("PLG_CROWDFUNDING_FILES_DOWNLOAD");?></span>
                        </a>

                        <a class="btn btn-danger js-cffile-btn-remove"
                           data-file-id="<?php echo (int)$file["id"]; ?>"
                           href="<?php echo JRoute::_("index.php?option=com_crowdfundingfiles&task=files.remove&format=raw");?>">
                            <span class="glyphicon glyphicon-trash"></span>
                            <span class="hidden-xs"><?php echo JText::_("PLG_CROWDFUNDING_FILES_DELETE");?></span>
                        </a>
                    </td>
                </tr>
            <?php } ?>

            <tr style="display: none;" id="js-cffiles-element">
                <td>{TITLE}</td>
                <td>{FILENAME}</td>
                <td>
                    <a class="btn btn-default hidden-xs" href="#" download>
                        <span class="glyphicon glyphicon-download"></span>
                        <span class="hidden-xs"><?php echo JText::_("PLG_CROWDFUNDING_FILES_DOWNLOAD");?></span>
                    </a>
                    <a class="btn btn-danger" href="<?php echo JRoute::_("index.php?option=com_crowdfundingfiles&task=files.remove&format=raw");?>">
                        <span class="glyphicon glyphicon-trash"></span>
                        <span class="hidden-xs"><?php echo JText::_("PLG_CROWDFUNDING_FILES_DELETE");?></span>
                    </a>
                </td>
            </tr>

            </tbody>
        </table>

    </div>

</div>

