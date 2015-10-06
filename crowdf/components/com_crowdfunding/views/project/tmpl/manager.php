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

if (strcmp("five_steps", $this->wizardType) == 0) {
    $layout      = new JLayoutFile('project_wizard');
} else {
    $layout      = new JLayoutFile('project_wizard_six_steps');
}
echo $layout->render($this->layoutData);
?>
<div class="row">
    <div class="col-md-4">
        <?php
            $this->raised = $this->amount->setValue($this->item->funded)->formatCurrency();

            // Prepare the value that I am going to display
            $fundedPercents = JHtml::_("crowdfunding.funded", $this->item->funded_percents);
            ?>
            <div class="thumbnail cf-project">
                <a href="<?php echo JRoute::_(CrowdfundingHelperRoute::getDetailsRoute($this->item->slug, $this->item->catslug)); ?>">
                    <?php if (!$this->item->image) { ?>
                        <img src="<?php echo "media/com_crowdfunding/images/no_image.png"; ?>"
                             alt="<?php echo $this->item->title; ?>" width="<?php echo $this->imageWidth; ?>"
                             height="<?php echo $this->imageHeight; ?>">
                    <?php } else { ?>
                        <img src="<?php echo $this->imageFolder . "/" . $this->item->image; ?>"
                             alt="<?php echo $this->item->title; ?>" width="<?php echo $this->imageWidth; ?>"
                             height="<?php echo $this->imageHeight; ?>">
                    <?php } ?>
                </a>

                <div class="caption">
                    <h3>
                        <a href="<?php echo JRoute::_(CrowdfundingHelperRoute::getDetailsRoute($this->item->slug, $this->item->catslug)); ?>">
                            <?php echo JHtmlString::truncate($this->item->title, $this->titleLength, true, false); ?>
                        </a>
                    </h3>
                    <p><?php echo JHtmlString::truncate($this->item->short_desc, $this->descriptionLength, true, false); ?></p>
                </div>

                <div class="cf-caption-info absolute-bottom">
                    <?php echo JHtml::_("crowdfunding.progressbar", $fundedPercents, $this->item->days_left, $this->item->funding_type); ?>
                    <div class="row-fluid">
                        <div class="col-md-4">
                            <div class="bolder"><?php echo $this->item->funded_percents; ?>%</div>
                            <div class="text-uppercase"><?php echo JText::_("COM_CROWDFUNDING_FUNDED"); ?></div>
                        </div>
                        <div class="col-md-4">
                            <div class="bolder"><?php echo $this->raised; ?></div>
                            <div class="text-uppercase"><?php echo JText::_("COM_CROWDFUNDING_RAISED"); ?></div>
                        </div>
                        <div class="col-md-4">
                            <div class="bolder"><?php echo $this->item->days_left; ?></div>
                            <div class="text-uppercase"><?php echo JText::_("COM_CROWDFUNDING_DAYS_LEFT"); ?></div>
                        </div>
                    </div>
                </div>

            </div>
    </div>

    <div class="col-md-4">
        <?php echo $this->loadTemplate("basic"); ?>
    </div>

    <div class="col-md-4">
        <?php echo $this->loadTemplate("settings"); ?>
    </div>
</div>

<?php if (CrowdfundingHelper::isRewardsEnabled($this->item->id)) {?>
<div class="row">
    <div class="col-md-12">
        <?php echo $this->loadTemplate("rewards"); ?>
    </div>
</div>
<?php } ?>