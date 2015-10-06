<?php
/**
 * @package      CrowdfundingData
 * @subpackage   Component
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2014 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
// no direct access
defined('_JEXEC') or die;
?>
<?php if (!empty($this->sidebar)): ?>
<div id="j-sidebar-container" class="span2">
    <?php echo $this->sidebar; ?>
</div>
<div id="j-main-container" class="span10">
<?php else : ?>
<div id="j-main-container">
<?php endif; ?>
    <div class="span8">
    </div>

    <div class="span4">
        <a href="#" target="_blank"><img src="../media/com_crowdfundingdata/images/logo.png" alt="<?php echo JText::_("COM_CROWDFUNDINGDATA"); ?>"/></a>
        <a href="http://itprism.com" target="_blank" title="<?php echo JText::_("COM_CROWDFUNDINGDATA_PRODUCT"); ?>">
            <img src="../media/com_crowdfundingdata/images/product_of_itprism.png" alt="<?php echo JText::_("COM_CROWDFUNDINGDATA_PRODUCT"); ?>"/>
        </a>
        <p><?php echo JText::_("COM_CROWDFUNDINGDATA_YOUR_VOTE"); ?></p>
        <p><?php echo JText::_("COM_CROWDFUNDINGDATA_SUBSCRIPTION"); ?></p>
        <table class="table table-striped">
            <tbody>
            <tr>
                <td><?php echo JText::_("COM_CROWDFUNDINGDATA_INSTALLED_VERSION"); ?></td>
                <td><?php echo $this->version->getShortVersion(); ?></td>
            </tr>
            <tr>
                <td><?php echo JText::_("COM_CROWDFUNDINGDATA_RELEASE_DATE"); ?></td>
                <td><?php echo $this->version->releaseDate ?></td>
            </tr>
            <tr>
                <td><?php echo JText::_("COM_CROWDFUNDINGDATA_PRISM_LIBRARY_VERSION"); ?></td>
                <td><?php echo $this->itprismVersion; ?></td>
            </tr>
            <tr>
                <td><?php echo JText::_("COM_CROWDFUNDINGDATA_COPYRIGHT"); ?></td>
                <td><?php echo $this->version->copyright; ?></td>
            </tr>
            <tr>
                <td><?php echo JText::_("COM_CROWDFUNDINGDATA_LICENSE"); ?></td>
                <td><?php echo $this->version->license; ?></td>
            </tr>
            </tbody>
        </table>
    </div>
</div>
