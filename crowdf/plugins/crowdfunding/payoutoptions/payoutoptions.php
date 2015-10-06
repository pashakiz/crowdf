<?php
/**
 * @package         CrowdfundingPayoutOptions
 * @subpackage      Plugins
 * @author          Todor Iliev
 * @copyright       Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license         http://www.gnu.org/licenses/gpl-3.0.en.html GNU/GPL
 */

// no direct access
defined('_JEXEC') or die;

jimport("Crowdfunding.init");
jimport("CrowdfundingFinance.init");

/**
 * Crowdfunding Payout Options Plugin
 *
 * @package        CrowdfundingPayoutOptions
 * @subpackage     Plugins
 */
class plgCrowdfundingPayoutOptions extends JPlugin
{
    protected $autoloadLanguage = true;

    /**
     * @var JApplicationSite
     */
    protected $app;

    /**
     * @var Joomla\Registry\Registry
     */
    public $params;

    protected $version = "1.3";

    /**
     * This method prepares a code that will be included to step "Extras" on project wizard.
     *
     * @param string    $context This string gives information about that where it has been executed the trigger.
     * @param object    $item    A project data.
     * @param Joomla\Registry\Registry $params  The parameters of the component
     *
     * @return null|string
     */
    public function onExtrasDisplay($context, &$item, &$params)
    {
        if (strcmp("com_crowdfunding.project.extras", $context) != 0) {
            return null;
        }

        if ($this->app->isAdmin()) {
            return null;
        }

        $doc = JFactory::getDocument();
        /**  @var $doc JDocumentHtml */

        // Check document type
        $docType = $doc->getType();
        if (strcmp("html", $docType) != 0) {
            return null;
        }
        
        if (empty($item->user_id)) {
            return null;
        }

        // A flag that shows the options are active.
        if (!$this->params->get("display_paypal", 0) and !$this->params->get("display_banktransfer", 0)) {
            return "";
        }

        $activeTab = "";
        if ($this->params->get("display_paypal", 0)) {
            $activeTab = "paypal";
        } elseif ($this->params->get("display_banktransfer", 0)) {
            $activeTab = "banktransfer";
        }

        $payout = new CrowdfundingFinance\Payout(JFactory::getDbo());
        $payout->load($item->id);

        // Load jQuery
        JHtml::_("jquery.framework");
        JHtml::_("prism.ui.pnotify");
        JHtml::_('prism.ui.joomlaHelper');

        // Get the path for the layout file
        $path = JPath::clean(JPluginHelper::getLayoutPath('crowdfunding', 'payoutoptions'));

        // Render the login form.
        ob_start();
        include $path;
        $html = ob_get_clean();

        return $html;
    }
}
