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

/**
 * Crowdfunding Html Helper
 *
 * @package        ITPrism Components
 * @subpackage     Crowdfunding
 * @since          1.6
 */
abstract class JHtmlCrowdfundingBackend
{
    public static function approved($i, $value, $prefix, $checkbox = 'cb')
    {
        JHtml::_('bootstrap.tooltip');

        if (!$value) { // Disapproved
            $task  = $prefix . "approve";
            $title = "COM_CROWDFUNDING_APPROVE_ITEM";
            $class = "ban-circle";
        } else {
            $task  = $prefix . "disapprove";
            $title = "COM_CROWDFUNDING_DISAPPROVE_ITEM";
            $class = "ok";
        }

        $html[] = '<a class="btn btn-micro hasTooltip" href="javascript:void(0);" onclick="return listItemTask(\'' . $checkbox . $i . '\',\'' . $task . '\')" title="' . addslashes(htmlspecialchars(JText::_($title), ENT_COMPAT, 'UTF-8')) . '">';
        $html[] = '<i class="icon-' . $class . '"></i>';
        $html[] = '</a>';

        return implode("\n", $html);
    }

    /**
     * Returns a published state on a grid
     *
     * @param   integer      $value    The state value.
     * @param   integer      $i        The row index
     * @param   string|array $prefix   An optional task prefix or an array of options
     * @param   boolean      $enabled  An optional setting for access control on the action.
     * @param   string       $checkbox An optional prefix for checkboxes.
     *
     * @return  string  The Html code
     *
     * @see     JHtmlJGrid::state
     * @since   11.1
     */
    public static function published($i, $value, $prefix = '', $enabled = true, $checkbox = 'cb')
    {
        if (is_array($prefix)) {
            $options  = $prefix;
            $enabled  = array_key_exists('enabled', $options) ? $options['enabled'] : $enabled;
            $checkbox = array_key_exists('checkbox', $options) ? $options['checkbox'] : $checkbox;
            $prefix   = array_key_exists('prefix', $options) ? $options['prefix'] : '';
        }

        $states = array(
            1  => array('unpublish', 'JPUBLISHED', 'JLIB_HTML_UNPUBLISH_ITEM', 'JPUBLISHED', true, 'publish', 'publish'),
            0  => array('publish', 'JUNPUBLISHED', 'JLIB_HTML_PUBLISH_ITEM', 'JUNPUBLISHED', true, 'unpublish', 'unpublish'),
            2  => array('unpublish', 'JARCHIVED', 'JLIB_HTML_UNPUBLISH_ITEM', 'JARCHIVED', true, 'archive', 'archive'),
            -2 => array('publish', 'JTRASHED', 'JLIB_HTML_PUBLISH_ITEM', 'JTRASHED', true, 'trash', 'trash')
        );

        return JHtmlJGrid::state($states, $value, $i, $prefix, $enabled, true, $checkbox);
    }

    public static function reward($rewardId, $reward, $projectId, $sent = 0)
    {
        $sent = (!$sent) ? 0 : 1;

        $html = array();

        if (!$rewardId) {

            $rewardLink = "javascript: void(0);";

            $icon  = "../media/com_crowdfunding/images/noreward_16.png";
            $title = 'title="' . JText::_('COM_CROWDFUNDING_REWARD_NOT_SELECTED') . '"';

        } else {

            $rewardLink = JRoute::_("index.php?option=com_crowdfunding&view=rewards&pid=" . (int)$projectId) . "&amp;filter_search=" . rawurlencode("id:" . $rewardId);

            if (!$sent) {
                $icon  = "../media/com_crowdfunding/images/reward_16.png";
                $title = 'title="';
                $title .= htmlspecialchars($reward, ENT_QUOTES, "UTF-8") . "<br />";
                $title .= JText::_("COM_CROWDFUNDING_REWARD_NOT_SENT");
                $title .= '"';
            } else {
                $icon  = "../media/com_crowdfunding/images/reward_sent_16.png";
                $title = 'title="';
                $title .= htmlspecialchars($reward, ENT_QUOTES, "UTF-8") . "<br />";
                $title .= JText::_("COM_CROWDFUNDING_REWARD_SENT");
                $title .= '"';
            }

        }

        $html[] = '<a href="' . $rewardLink . '" class="hasTooltip" ' . $title . '>';
        $html[] = '<img src="' . $icon . '" width="16" height="16" />';
        $html[] = '</a>';

        return implode(" ", $html);
    }

    public static function rewardState($rewardId, $transactionId, $sent = 0, $return = "")
    {
        $sent  = (!$sent) ? 0 : 1;
        $state = (!$sent) ? 1 : 0;

        $html = array();

        $rewardLink = "index.php?option=com_crowdfunding&task=reward.changeState&id=" . $rewardId."&txn_id=".$transactionId."&state=".(int)$state."&".JSession::getFormToken().'=1&return='.$return;

        if (!$sent) {
            $icon  = "../media/com_crowdfunding/images/reward_16.png";
            $title = 'title="';
            $title .= JText::_("COM_CROWDFUNDING_REWARD_HAS_NOT_BEEN_SENT");
            $title .= '"';
        } else {
            $icon  = "../media/com_crowdfunding/images/reward_sent_16.png";
            $title = 'title="';
            $title .= JText::_("COM_CROWDFUNDING_REWARD_HAS_BEEN_SENT");
            $title .= '"';
        }

        $html[] = '<a href="' . $rewardLink . '" class="hasTooltip" ' . $title . '>';
        $html[] = '<img src="' . $icon . '" width="16" height="16" />';
        $html[] = '</a>';

        return implode(" ", $html);
    }

    /**
     * @param   int $i
     * @param   int $value The state value
     * @param   bool $canChange
     *
     * @return string
     */
    public static function featured($i, $value = 0, $canChange = true)
    {
        JHtml::_('bootstrap.tooltip');

        // Array of image, task, title, action
        $states = array(
            0 => array('unfeatured', 'projects.featured', 'COM_CROWDFUNDING_UNFEATURED', 'COM_CROWDFUNDING_TOGGLE_TO_FEATURE'),
            1 => array('featured', 'projects.unfeatured', 'COM_CROWDFUNDING_FEATURED', 'COM_CROWDFUNDING_TOGGLE_TO_UNFEATURE'),
        );

        $state = Joomla\Utilities\ArrayHelper::getValue($states, (int)$value, $states[1]);
        $icon  = $state[0];
        if ($canChange) {
            $html = '<a href="#" onclick="return listItemTask(\'cb' . $i . '\',\'' . $state[1] . '\')" class="btn btn-micro hasTooltip' . ($value == 1 ? ' active' : '') . '" title="' . JText::_($state[3]) . '"><i class="icon-' . $icon . '"></i></a>';
        } else {
            $html = '<a class="btn btn-micro hasTooltip disabled' . ($value == 1 ? ' active' : '') . '" title="' . JText::_($state[2]) . '"><i class="icon-' . $icon . '"></i></a>';
        }

        return $html;
    }

    public static function reason($value)
    {
        if (!$value) {
            return "";
        }

        JHtml::_('bootstrap.tooltip');

        $title = JText::sprintf("COM_CROWDFUNDING_STATUS_REASON", htmlspecialchars($value, ENT_COMPAT, 'UTF-8'));

        $html[] = '<a class="btn btn-micro hasTooltip" href="javascript:void(0);" title="' . addslashes($title) . '">';
        $html[] = '<i class="icon-question"></i>';
        $html[] = '</a>';

        return implode("\n", $html);
    }

    /**
     * @param null|Prism\Integration\Profile\ProfileInterface $socialProfile
     * @param int $userId
     *
     * @return string
     */
    public static function profileIcon($socialProfile, $userId)
    {
        $html = array();

        if (!empty($socialProfile)) {
            $link = str_replace("/administrator", "", $socialProfile->getLink());
            $link = $str= ltrim($link, '/');

            $html[] = '<a href="'. JUri::root() .$link .'" class="btn" target="_blank">';
            $html[] = '<i class="icon icon-user"></i>';
            $html[] = '</a>';
        } else {
            $html[] = '<a href="index.php?option=com_crowdfunding&view=users&filter_search=id:' . (int)$userId.'" class="btn">';
            $html[] = '<i class="icon icon-user"></i>';
            $html[] = '</a>';
        }

        return implode("\n", $html);
    }

    /**
     * @param null|Prism\Integration\Profile\ProfileInterface $socialProfile
     * @param string $name
     * @param int $userId
     *
     * @return string
     */
    public static function profileLink($socialProfile, $name, $userId)
    {
        $html = array();

        if (!empty($socialProfile)) {
            $link = str_replace("/administrator", "", $socialProfile->getLink());
            $link = $str= ltrim($link, '/');

            $html[] = '<a href="'. JUri::root() .$link .'" target="_blank">';
            $html[] = htmlentities($name, ENT_QUOTES, "UTF-8");
            $html[] = '</a>';
        } else {
            $html[] = '<a href="index.php?option=com_crowdfunding&view=users&filter_search=id:' . (int)$userId.'">';
            $html[] = htmlentities($name, ENT_QUOTES, "UTF-8");
            $html[] = '</a>';
        }

        return implode("\n", $html);
    }

    /**
     * Generates tracking information about a transaction data.
     *
     * @param mixed $trackId
     *
     * @return string
     */
    public static function trackId($trackId)
    {
        if (!$trackId) {
            $output = JText::sprintf("COM_CROWDFUNDING_DATE_AND_TIME", "---");
        } else {

            if (!is_numeric($trackId)) {
                $output = JText::sprintf("COM_CROWDFUNDING_TRACK_ID", htmlentities($trackId, ENT_QUOTES, "UTF-8"));
            } else {

                $validator = new Prism\Validator\Date($trackId);

                if (!$validator->isValid()) {
                    $output = JText::sprintf("COM_CROWDFUNDING_DATE_AND_TIME", "---");
                } else {
                    $date = new JDate($trackId);
                    $output = JText::sprintf("COM_CROWDFUNDING_DATE_AND_TIME", $date->format(DATE_RFC822));
                }
            }

        }

        return $output;
    }

    /**
     * Generates information about transaction amount.
     *
     * @param object $item
     * @param Crowdfunding\Amount $amount
     * @param Crowdfunding\Currencies $currencies
     *
     * @return string
     */
    public static function transactionAmount($item, $amount, $currencies)
    {
        $currency = $currencies->getCurrencyByCode($item->txn_currency);
        $amount->setCurrency($currency);

        $item->txn_amount = floatval($item->txn_amount);
        $item->fee = floatval($item->fee);

        $output = (!empty($currency)) ? $amount->setValue($item->txn_amount)->formatCurrency() : $item->txn_amount;

        if (!empty($item->fee)) {

            $fee = (!empty($currency)) ? $amount->setValue($item->fee)->formatCurrency() : $item->fee;

            // Prepare project owner amount.
            $projectOwnerAmount = round($item->txn_amount - $item->fee, 2);
            $projectOwnerAmount = (!empty($currency)) ? $amount->setValue($projectOwnerAmount)->formatCurrency() : $projectOwnerAmount;

            JHtml::_('bootstrap.tooltip');

            $title = JText::sprintf("COM_CROWDFUNDING_TRANSACTION_AMOUNT_FEE", $projectOwnerAmount, $fee);

            $output .= '<a class="btn btn-micro hasTooltip" href="javascript:void(0);" title="' . addslashes($title) . '">';
            $output .= '<i class="icon-question"></i>';
            $output .= '</a>';
        }

        return $output;
    }

    public static function name($name, $userId = 0)
    {
        $output = array();

        if (!empty($name)) {
            if (!empty($userId)) {
                $output[] = '<a href="' . JRoute::_("index.php?option=com_crowdfunding&view=users&filter_search=id:" . (int)$userId) . '">';
                $output[] = htmlspecialchars($name, ENT_QUOTES, "UTF-8");
                $output[] = '</a>';
            } else {
                $output[] = htmlspecialchars($name, ENT_QUOTES, "UTF-8");
            }
        } else {
            $output[] = JText::_("COM_CROWDFUNDING_ANONYMOUS");
        }

        return implode("\n", $output);
    }

    /**
     * Route URI to front-end.
     *
     * @param object  $item
     * @param string  $website
     * @param JRouter $routerSite
     *
     * @return string
     */
    public static function siteRoute($item, $website, $routerSite)
    {
        $routedUri = $routerSite->build(CrowdfundingHelperRoute::getDetailsRoute($item->slug, $item->catslug));
        if ($routedUri instanceof JUri) {
            $routedUri = $routedUri->toString();
        }

        if (0 === strpos($routedUri, "/administrator")) {
            $routedUri = str_replace("/administrator", "", $routedUri);
        }

        return $website.$routedUri;
    }
}
