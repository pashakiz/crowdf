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

            <!--  Row 1 -->
            <div class="row-fluid dashboard-stats">
                <div class="span6">
                    <h3 class="latest-started">
                        <?php echo JText::_("COM_CROWDFUNDING_LATEST_STARTED"); ?>
                    </h3>
                    <table class="table">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th><?php echo JText::_("COM_CROWDFUNDING_PROJECT"); ?></th>
                            <th class="center nowrap"
                                style="max-width: 50px;"><?php echo JText::_("COM_CROWDFUNDING_STARTED_ON"); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php for ($i = 0, $max = count($this->latestStarted); $i < $max; $i++) { ?>
                            <tr>
                                <td><?php echo $i + 1; ?></td>
                                <td>
                                    <a href="<?php echo JRoute::_("index.php?option=com_crowdfunding&view=projects&filter_search=id:" . (int)$this->latestStarted[$i]["id"]); ?>">
                                        <?php echo JHtmlString::truncate(strip_tags($this->latestStarted[$i]["title"]), 53); ?>
                                    </a>
                                </td>
                                <td class="center" style="min-width: 100px;">
                                    <?php echo JHtml::_('date', $this->latestStarted[$i]["funding_start"], JText::_('DATE_FORMAT_LC3')); ?>
                                </td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                </div>
                <div class="span6">
                    <h3 class="popular">
                        <?php echo JText::_("COM_CROWDFUNDING_POPULAR"); ?>
                    </h3>
                    <table class="table">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th><?php echo JText::_("COM_CROWDFUNDING_PROJECT"); ?></th>
                            <th class="center nowrap"
                                style="max-width: 50px;"><?php echo JText::_("COM_CROWDFUNDING_HITS"); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php for ($i = 0, $max = count($this->popular); $i < $max; $i++) { ?>
                            <tr>
                                <td><?php echo $i + 1; ?></td>
                                <td>
                                    <a href="<?php echo JRoute::_("index.php?option=com_crowdfunding&view=projects&filter_search=id:" . (int)$this->popular[$i]["id"]); ?>">
                                        <?php echo JHtmlString::truncate(strip_tags($this->popular[$i]["title"]), 53); ?>
                                    </a>
                                </td>
                                <td class="center">
                                    <?php echo (int)$this->popular[$i]["hits"]; ?>
                                </td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- /Row 1 -->
            <!--  Row 2 -->
            <div class="row-fluid dashboard-stats">
                <div class="span6">
                    <h3 class="latest-created">
                        <?php echo JText::_("COM_CROWDFUNDING_LATEST_CREATED"); ?>
                    </h3>
                    <table class="table">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th><?php echo JText::_("COM_CROWDFUNDING_PROJECT"); ?></th>
                            <th class="center nowrap"
                                style="max-width: 50px;"><?php echo JText::_("COM_CROWDFUNDING_CREATED_ON"); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php for ($i = 0, $max = count($this->latestCreated); $i < $max; $i++) { ?>
                            <tr>
                                <td><?php echo $i + 1; ?></td>
                                <td>
                                    <a href="<?php echo JRoute::_("index.php?option=com_crowdfunding&view=projects&filter_search=id:" . (int)$this->latestCreated[$i]["id"]); ?>">
                                        <?php echo JHtmlString::truncate(strip_tags($this->latestCreated[$i]["title"]), 53); ?>
                                    </a>
                                </td>
                                <td class="center" style="min-width: 100px;">
                                    <?php echo JHtml::_('date', $this->latestCreated[$i]["created"], JText::_('DATE_FORMAT_LC3')); ?>
                                </td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                </div>
                <div class="span6">
                    <h3 class="mostfunded">
                        <?php echo JText::_("COM_CROWDFUNDING_MOST_FUNDED"); ?>
                    </h3>
                    <table class="table">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th><?php echo JText::_("COM_CROWDFUNDING_PROJECT"); ?></th>
                            <th class="center nowrap"
                                style="max-width: 50px;"><?php echo JText::_("COM_CROWDFUNDING_FUNDS"); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php for ($i = 0, $max = count($this->mostFunded); $i < $max; $i++) { ?>
                            <tr>
                                <td><?php echo $i + 1; ?></td>
                                <td>
                                    <a href="<?php echo JRoute::_("index.php?option=com_crowdfunding&view=projects&filter_search=id:" . (int)$this->mostFunded[$i]["id"]); ?>">
                                        <?php echo JHtmlString::truncate(strip_tags($this->mostFunded[$i]["title"]), 53); ?>
                                    </a>
                                </td>
                                <td class="center">
                                    <?php echo $this->amount->setValue($this->mostFunded[$i]["funded"])->formatCurrency(); ?>
                                </td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- /Row 2 -->
        </div>

        <div class="span4">
            <a href="http://itprism.com/free-joomla-extensions/ecommerce-gamification/crowdfunding-collective-raising-capital" target="_blank"><img src="../media/com_crowdfunding/images/logo.png" alt="<?php echo JText::_("COM_CROWDFUNDING"); ?>"/></a>
            <a href="http://itprism.com" target="_blank" title="<?php echo JText::_("COM_CROWDFUNDING_PRODUCT"); ?>"><img src="../media/com_crowdfunding/images/product_of_itprism.png" alt="<?php echo JText::_("COM_CROWDFUNDING_PRODUCT"); ?>"/></a>

            <p><?php echo JText::_("COM_CROWDFUNDING_YOUR_VOTE"); ?></p>
            <p><?php echo JText::_("COM_CROWDFUNDING_SUBSCRIPTION"); ?></p>
            <table class="table table-striped">
                <tbody>
                <tr>
                    <td><?php echo JText::_("COM_CROWDFUNDING_INSTALLED_VERSION"); ?></td>
                    <td><?php echo $this->version->getShortVersion(); ?></td>
                </tr>
                <tr>
                    <td><?php echo JText::_("COM_CROWDFUNDING_RELEASE_DATE"); ?></td>
                    <td><?php echo $this->version->releaseDate ?></td>
                </tr>
                <tr>
                    <td><?php echo JText::_("COM_CROWDFUNDING_PRISM_LIBRARY_VERSION"); ?></td>
                    <td><?php echo $this->prismVersion; ?></td>
                </tr>
                <tr>
                    <td><?php echo JText::_("COM_CROWDFUNDING_COPYRIGHT"); ?></td>
                    <td><?php echo $this->version->copyright; ?></td>
                </tr>
                <tr>
                    <td><?php echo JText::_("COM_CROWDFUNDING_LICENSE"); ?></td>
                    <td><?php echo $this->version->license; ?></td>
                </tr>
                </tbody>
            </table>
            <?php if (!empty($this->prismVersionLowerMessage)) {?>
                <p class="alert alert-warning cf-upgrade-info"><i class="icon-warning"></i> <?php echo $this->prismVersionLowerMessage; ?></p>
            <?php } ?>
            <p class="alert alert-info cf-upgrade-info"><i class="icon-info"></i> <?php echo JText::_("COM_CROWDFUNDING_HOW_TO_UPGRADE"); ?></p>
        </div>
    </div>