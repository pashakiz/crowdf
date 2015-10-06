<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Content.pagenavigation
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>
<div class="row">
    <div class=col-md-12>
        <div class="panel panel-default">
            <div class="panel-body">
                <form action="<?php echo JRoute::_('index.php?option=com_users&task=user.login'); ?>" method="post" class="form-horizontal">
                    <?php foreach ($this->loginForm->getFieldset('credentials') as $field) { ?>
                        <?php if (!$field->hidden) { ?>
                    <div class="form-group">
                        <?php echo $field->label; ?>
                        <?php echo $field->input; ?>
                    </div>
                        <?php } else { ?>
                        <?php echo $field->input; ?>
                        <?php } ?>
                    <?php } ?>
                    <div class="form-group">
                        <div class="col-sm-offset-2 col-sm-10">
                            <button type="submit" class="btn btn-primary"><?php echo JText::_('JLOGIN'); ?></button>
                        </div>
                    </div>
                    <input type="hidden" name="return" value="<?php echo base64_encode($this->returnUrl); ?>" />
                    <?php echo JHtml::_('form.token'); ?>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <ul class="nav nav-pills nav-stacked">
        <li>
            <a href="<?php echo JRoute::_('index.php?option=com_users&view=reset'); ?>">
                <?php echo JText::_('PLG_CROWDFUNDINGPAYMENT_LOGIN_RESET'); ?></a>
        </li>
        <li>
            <a href="<?php echo JRoute::_('index.php?option=com_users&view=remind'); ?>">
                <?php echo JText::_('PLG_CROWDFUNDINGPAYMENT_LOGIN_REMIND'); ?></a>
        </li>
        <?php
        $usersConfig = JComponentHelper::getParams('com_users');
        if ($usersConfig->get('allowUserRegistration')) { ?>
            <li>
                <a href="<?php echo JRoute::_('index.php?option=com_users&view=registration'); ?>">
                    <?php echo JText::_('PLG_CROWDFUNDINGPAYMENT_LOGIN_REGISTER'); ?></a>
            </li>
        <?php } ?>
    </ul>
</div>
