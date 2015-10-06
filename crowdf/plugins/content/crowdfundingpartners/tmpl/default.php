<?php
/**
 * @package      CrowdfundingPartners
 * @subpackage   Plugins
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined('_JEXEC') or die;
?>
<div class="panel panel-default">
    <div class="panel-heading">
        <h4><?php echo JText::_("PLG_CONTENT_CROWDFUNDINGPARTNERS_TEAM");?></h4>
    </div>

    <table class="table table-bordered mtb-25-0">
        <tbody>
        <?php
        foreach ($partners as $partner) { ?>
            <tr>
                <td>
                    <?php echo JHtml::_("crowdfundingpartners.partner", $partner, array("width" => $this->params->get("width", 50), "height" => $this->params->get("height", 50))); ?>
                </td>
            </tr>
        <?php
        } ?>
        </tbody>
    </table>
</div>