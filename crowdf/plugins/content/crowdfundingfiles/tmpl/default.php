<?php
/**
 * @package      CrowdfundingFiles
 * @subpackage   Plugins
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined('_JEXEC') or die;
?>
<div class="panel panel-default">
    <div class="panel-heading">
        <h4><?php echo JText::_("PLG_CONTENT_CROWDFUNDINGFILES_FILES");?></h4>
    </div>

    <table class="table table-bordered mtb-25-0">
        <tbody>
        <?php
        foreach ($files as $file) { ?>
            <tr>
                <td>
                    <a href="<?php echo $mediaFolderUri ."/". $file["filename"]; ?>" download>
                        <?php echo htmlentities($file["title"], ENT_QUOTES, "UTF-8"); ?>
                    </a>
                </td>
            </tr>
        <?php
        } ?>
        </tbody>
    </table>
</div>