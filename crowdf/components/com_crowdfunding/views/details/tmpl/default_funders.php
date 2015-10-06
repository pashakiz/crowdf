<?php
/**
 * @package      Crowdfunding
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// no direct access
defined('_JEXEC') or die;?>
<?php
if (!empty($this->items)) {
    foreach ($this->items as $item) {

        $socialProfile = (!$this->socialProfiles) ? null : $this->socialProfiles->getLink($item->id);

        $socialAvatar  = (!$this->socialProfiles) ? $this->defaultAvatar : $this->socialProfiles->getAvatar($item->id, $this->avatarsSize);
        if (!$socialAvatar) {
            $socialAvatar = $this->defaultAvatar;
        }

        $socialLocation  = (!$this->socialProfiles) ? null : $this->socialProfiles->getLocation($item->id);
        $socialCountryCode  = (!$this->socialProfiles) ? null: $this->socialProfiles->getCountryCode($item->id);
        ?>

            <div class="cf-funder-row">

                <div class="media">
                    <div class="media-left">
                        <a class="cf-funder-picture" href="<?php echo (!$socialProfile) ? "javascript: void(0);" : $socialProfile; ?>">
                            <img class="media-object" src="<?php echo $socialAvatar; ?>" />
                        </a>
                    </div>

                    <div class="media-body">
                        <div class="pull-left cf-funder-info">
                            <h5 class="media-heading">
                                <?php if (!empty($socialProfile)) { ?>
                                    <a href="<?php echo $socialProfile; ?>">
                                        <?php echo $this->escape($item->name); ?>
                                    </a>
                                <?php } else { ?>
                                    <?php echo (!$item->name) ? JText::_("COM_CROWDFUNDING_ANONYMOUS") : $this->escape($item->name); ?>
                                <?php } ?>
                            </h5>
                            <?php echo JHtml::_("crowdfunding.profileLocation", $socialLocation, $socialCountryCode); ?>
                        </div>

                        <?php if(!empty($this->displayAmounts)) { ?>
                        <div class="pull-right cf-funder-amount">
                            <?php echo $this->amount->setValue($item->txn_amount)->formatCurrency(); ?>
                        </div>
                        <?php } ?>
                    </div>
                </div>

            </div>

    <?php } ?>

<?php } ?>