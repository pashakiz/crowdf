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
 * @package        Crowdfunding
 * @subpackage     Components
 * @since          1.6
 */
abstract class JHtmlCrowdfunding
{
    /**
     * @var   array   array containing information for loaded files
     */
    protected static $loaded = array();

    /**
     * Display an icon for approved or not approved project.
     *
     * @param integer $value
     * @param bool $hint
     * @param string $iconOk
     * @param string $iconRemove
     *
     * @return string
     */
    public static function approved($value, $hint = true, $iconOk = "glyphicon glyphicon-ok-sign", $iconRemove = "glyphicon glyphicon-remove-sign")
    {
        if (!$hint) {
            $html = '<span class="{ICON}" aria-hidden="true"></span>';
        } else {
            JHtml::_("bootstrap.tooltip");
            $title = (!$value) ? ' title="'.JText::_("COM_CROWDFUNDING_NOTAPPROVED").'"' : ' title="'.JText::_("COM_CROWDFUNDING_APPROVED").'""';
            $html = '<span class="{ICON} hasTooltip cursor-pointer" aria-hidden="true"'. $title .'></span>';
        }

        switch ($value) {

            case 1: // Published
                $html = str_replace("{ICON}", $iconOk, $html);
                break;

            default: // Unpublished
                $html = str_replace("{ICON}", $iconRemove, $html);
                break;
        }

        return $html;

    }

    /**
     * Display an input field for amount.
     *
     * @param float  $value
     * @param Crowdfunding\Amount $amount
     * @param array  $options
     *
     * @return string
     */
    public static function inputAmount($value, Crowdfunding\Amount $amount, $options)
    {
        $currency     = $amount->getCurrency();

        $symbol       = $currency->getSymbol();
        $currencyCode = $currency->getCode();

        $html = '<div class="input-group">';

        if (!empty($symbol)) {
            $html .= '<div class="input-group-addon">' . $symbol . '</div>';
        }

        $name = Joomla\Utilities\ArrayHelper::getValue($options, "name");

        $id = "";
        if (Joomla\Utilities\ArrayHelper::getValue($options, "id")) {
            $id = 'id="' . Joomla\Utilities\ArrayHelper::getValue($options, "id") . '"';
        }

        $class = 'class="form-control ';
        if (Joomla\Utilities\ArrayHelper::getValue($options, "class")) {
            $class .= Joomla\Utilities\ArrayHelper::getValue($options, "class");
        }
        $class .= '"';

        if (!$value or !is_numeric($value)) {
            $value = 0;
        }

        $html .= '<input type="text" name="' . $name . '" value="' . $amount->setValue($value)->format(). '" ' . $id . ' ' . $class . ' />';

        if (!empty($currencyCode)) {
            $html .= '<div class="input-group-addon">' . $currencyCode . '</div>';
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * Display a progress bar
     *
     * @param int    $percent A percent of fund raising
     * @param int    $daysLeft
     * @param string $fundingType
     *
     * @return string
     */
    public static function progressBar($percent, $daysLeft, $fundingType)
    {
        $html  = array();
        $class = 'progress-bar-success';

        if ($daysLeft > 0) {
            $html[1] = '<div class="progress-bar ' .$class.'" style="width: ' . $percent . '%"></div>';
        } else {

            // Check for the type of funding
            if ($fundingType == "FLEXIBLE") {

                if ($percent > 0) {
                    $html[1] = '<div class="progress-bar ' .$class.'" style="width: 100%">' . Joomla\String\String::strtoupper(JText::_("COM_CROWDFUNDING_SUCCESSFUL")) . '</div>';
                } else {
                    $class   = 'progress-bar-danger';
                    $html[1] = '<div class="progress-bar ' .$class.'" style="width: 100%">' . Joomla\String\String::strtoupper(JText::_("COM_CROWDFUNDING_COMPLETED")) . '</div>';
                }

            } else { // Fixed

                if ($percent >= 100) {
                    $html[1] = '<div class="progress-bar ' .$class.'" style="width: 100%">' . Joomla\String\String::strtoupper(JText::_("COM_CROWDFUNDING_SUCCESSFUL")) . '</div>';
                } else {
                    $class   = 'progress-bar-danger';
                    $html[1] = '<div class="progress-bar ' .$class.'" style="width: 100%">' . Joomla\String\String::strtoupper(JText::_("COM_CROWDFUNDING_COMPLETED")) . '</div>';
                }

            }

        }

        $html[0] = '<div class="progress">';
        $html[2] = '</div>';

        ksort($html);

        return implode("\n", $html);
    }

    /**
     * Display a state of result
     *
     * @param int    $percent A percent of fund raising
     * @param string $fundingType
     *
     * @return string
     */
    public static function resultState($percent, $fundingType)
    {
        // Check for the type of funding
        if ($fundingType == "FLEXIBLE") {

            if ($percent > 0) {
                $otuput = JText::_("COM_CROWDFUNDING_SUCCESSFUL");
            } else {
                $otuput = JText::_("COM_CROWDFUNDING_COMPLETED");
            }

        } else { // Fixed

            if ($percent >= 100) {
                $otuput = JText::_("COM_CROWDFUNDING_SUCCESSFUL");
            } else {
                $otuput = JText::_("COM_CROWDFUNDING_COMPLETED");
            }

        }

        return $otuput;
    }

    /**
     *
     * Display a text that describes the state of result
     *
     * @param int    $percent A percent of fund raising
     * @param string $fundingType
     *
     * @return string
     */
    public static function resultStateText($percent, $fundingType)
    {

        // Check for the type of funding
        if ($fundingType == "FLEXIBLE") {

            if ($percent > 0) {
                $otuput = JText::_("COM_CROWDFUNDING_FUNDRAISE_FINISHED_SUCCESSFULLY");
            } else {
                $otuput = JText::_("COM_CROWDFUNDING_FUNDRAISE_HAS_EXPIRED");
            }

        } else { // Fixed

            if ($percent >= 100) {
                $otuput = JText::_("COM_CROWDFUNDING_FUNDRAISE_FINISHED_SUCCESSFULLY");
            } else {
                $otuput = JText::_("COM_CROWDFUNDING_FUNDRAISE_HAS_EXPIRED");
            }

        }

        return $otuput;
    }

    /**
     * Display an icon for state of project
     *
     * @param integer $value
     * @param string  $url An url to the task
     * @param bool  $tip
     * @param string  $iconOk
     * @param string  $iconRemove
     *
     * @return string
     */
    public static function state($value, $url, $tip = false, $iconOk = "glyphicon glyphicon-ok-sign", $iconRemove = "glyphicon glyphicon-remove-sign")
    {
        $title = "";
        if (!empty($tip)) {
            JHtml::_("bootstrap.tooltip");

            $tipMessage = ($value != 1) ? JText::_("COM_CROWDFUNDING_LAUNCH_CAMPAIGN") : JText::_("COM_CROWDFUNDING_STOP_CAMPAIGN");

            $class = ' class="btn btn-default btn-sm hasTooltip"';
            $title = ' title="' . htmlspecialchars($tipMessage, ENT_QUOTES, "UTF-8") . '"';

        } else {
            $class = ' class="btn btn-default btn-sm"';
        }

        $html = '<a href="' . $url . '"' . $class . $title . ' ><span class="{ICON}"></span></a>';

        switch ($value) {

            case 1: // Published
                $html = str_replace("{ICON}", $iconOk, $html);
                break;

            default: // Unpublished
                $html = str_replace("{ICON}", $iconRemove, $html);
                break;
        }

        return $html;
    }

    /**
     * If value is higher than 100, sets it to 100.
     * This method validates percent of funding.
     *
     * @param integer $value
     *
     * @return int
     */
    public static function funded($value)
    {
        if ($value > 100) {
            $value = 100;
        };

        return $value;
    }

    /**
     * Calculate funded percents
     *
     * @param float $goal
     * @param float $funded
     *
     * @return int
     */
    public static function percents($goal, $funded)
    {

        $percents = 0;
        if ($goal > 0) {
            $percents = round(($funded / $goal) * 100, 2);
        }

        return $percents;
    }

    /**
     * This method generates a code that display a video
     *
     * @param string $value
     * @param boolean $responsive
     *
     * @return string
     */
    public static function video($value, $responsive = false)
    {
        $videoEmbed = new Prism\Video\Embed($value);
        $videoEmbed->parse();

        $html = array();

        if (!$responsive) {
            $html[] = $videoEmbed->getHtmlCode();
        } else {
            $html[] = '<div class="video-container">';
            $html[] = $videoEmbed->getHtmlCode();
            $html[] = '</div>';
        }

        return implode("\n", $html);
    }


    /**
     * Method to sort a column in a grid.
     *
     * @param   string $title         The link title
     * @param   string $order         The order field for the column
     * @param   string $direction     The current direction
     * @param   int $selected      The selected ordering
     * @param   string $task          An optional task override
     * @param   string $new_direction An optional direction for the new column
     * @param   string $tip           An optional text shown as tooltip title instead of $title
     *
     * @return  string
     *
     * @since   11.1
     *
     * @return string
     */
    public static function sort($title, $order, $direction = 'asc', $selected = 0, $task = null, $new_direction = 'asc', $tip = '')
    {
        $direction = strtolower($direction);
        $icon      = array('triangle-top', 'triangle-bottom');
        $index     = (int)($direction == 'desc');

        if ($order != $selected) {
            $direction = $new_direction;
        } else {
            $direction = ($direction == 'desc') ? 'asc' : 'desc';
        }

        $html = '<a href="#" onclick="Joomla.tableOrdering(\'' . $order . '\',\'' . $direction . '\',\'' . $task . '\');return false;" class="hasTooltip" title="' .
            JHtml::tooltipText(($tip ? $tip : $title), 'JGLOBAL_CLICK_TO_SORT_THIS_COLUMN') . '">';

        if (isset($title['0']) && $title['0'] == '<') {
            $html .= $title;
        } else {
            $html .= JText::_($title);
        }

        if ($order == $selected) {
            $html .= ' <span class="glyphicon glyphicon-' . $icon[$index] . '"></span>';
        }

        $html .= '</a>';

        return $html;
    }

    public static function reward($rewardId, $reward, $txnId, $sent = 0, $canEdit = false, $redirect = "")
    {
        $state = (!$sent) ? 1 : 0;

        $html = array();

        if (!$rewardId) {
            $icon  = "media/com_crowdfunding/images/noreward_16.png";
            $title = 'title="' . JText::_('COM_CROWDFUNDING_REWARD_NOT_SELECTED') . '"';
        } else {

            if (!$sent) {

                $icon = "media/com_crowdfunding/images/reward_16.png";

                // Prepare tooltip text
                if ($canEdit) {
                    $tooltipText = JText::sprintf('COM_CROWDFUNDING_SENT_REWARD_TOOLTIP', htmlspecialchars($reward, ENT_QUOTES, "UTF-8"), "<br />");
                } else {
                    $tooltipText = htmlspecialchars($reward, ENT_QUOTES, "UTF-8") . "<br />" . JText::_('COM_CROWDFUNDING_REWARD_NOT_SENT');
                }
                $title = 'title="' . $tooltipText . '"';

            } else {

                $icon = "media/com_crowdfunding/images/reward_sent_16.png";

                // Prepare tooltip text
                if ($canEdit) {
                    $tooltipText = JText::sprintf('COM_CROWDFUNDING_NOT_SENT_REWARD_TOOLTIP', htmlspecialchars($reward, ENT_QUOTES, "UTF-8"), "<br />");
                } else {
                    $tooltipText = htmlspecialchars($reward, ENT_QUOTES, "UTF-8") . "<br />" . JText::_('COM_CROWDFUNDING_REWARD_HAS_BEEN_SENT');
                }

                $title = 'title="' . $tooltipText . '"';

            }

        }

        // Prepare link
        if (!$rewardId or !$canEdit) {
            $link = "javascript: void(0);";
        } else {
            if (!empty($redirect)) {
                $redirect = "&redirect=".base64_encode($redirect);
            }

            $link = JRoute::_("index.php?option=com_crowdfunding&task=rewards.changeState&txn_id=" . (int)$txnId . "&state=" . (int)$state . "&" . JSession::getFormToken() . "=1".$redirect);
        }

        $html[] = '<a href="' . $link . '" class="hasTooltip" ' . $title . '>';
        $html[] = '<img src="' . $icon . '" width="16" height="16" />';
        $html[] = '</a>';

        return implode(" ", $html);
    }


    public static function projectTitle($title, $categoryState, $slug, $catSlug)
    {
        $html = array();

        if (!$categoryState) {
            $html[] = htmlspecialchars($title, ENT_QUOTES, "utf-8");
            $html[] = '<button type="button" class="hasTooltip" title="' . htmlspecialchars(JText::_("COM_CROWDFUNDING_SELECT_OTHER_CATEGORY_TOOLTIP"), ENT_QUOTES, "utf-8") . '">';
            $html[] = '<span class="glyphicon glyphicon-info-sign"></span>';
            $html[] = '</button>';
        } else {

            $html[] = '<a href="' . JRoute::_(CrowdfundingHelperRoute::getDetailsRoute($slug, $catSlug)) . '">';
            $html[] = htmlspecialchars($title, ENT_QUOTES, "utf-8");
            $html[] = '</a>';
        }

        return implode("\n", $html);
    }

    public static function date($date, $format = "d F Y")
    {
        $dateValidator = new Prism\Validator\Date($date);
        if ($dateValidator->isValid()) {
            $date = JHtml::_("date", $date, $format);
        } else {
            $date = "---";
        }

        return $date;
    }

    /**
     * @param string       $endDate
     * @param int       $days
     * @param string $format
     *
     * @return string
     */
    public static function duration($endDate, $days, $format = "d F Y")
    {
        $output = "";

        $endDateValidator = new Prism\Validator\Date($endDate);

        if (!empty($days)) {
            $output .= JText::sprintf("COM_CROWDFUNDING_DURATION_DAYS", (int)$days);

            // Display end date
            if ($endDateValidator->isValid()) {
                $output .= '<div class="info-mini">';
                $output .= JText::sprintf("COM_CROWDFUNDING_DURATION_END_DATE", JHTML::_('date', $endDate, $format));
                $output .= '</div>';
            }

        } elseif ($endDateValidator->isValid()) {
            $output .= JText::sprintf("COM_CROWDFUNDING_DURATION_END_DATE", JHTML::_('date', $endDate, $format));
        } else {
            $output .= "---";
        }

        return $output;
    }

    public static function postedby($name, $date, $link = null)
    {
        if (!empty($link)) {
            $profile = '<a href="' . $link . '">' . htmlspecialchars($name, ENT_QUOTES, "utf-8") . '</a>';
        } else {
            $profile = $name;
        }

        $date = JHTML::_('date', $date, JText::_('DATE_FORMAT_LC3'));
        $html = JText::sprintf("COM_CROWDFUNDING_POSTED_BY", $profile, $date);

        return $html;
    }

    public static function name($name)
    {
        if (!empty($name)) {
            $output = htmlspecialchars($name, ENT_QUOTES, "UTF-8");
        } else {
            $output = JText::_("COM_CROWDFUNDING_ANONYMOUS");
        }

        return $output;
    }

    /**
     * Display a percent string.
     *
     * <code>
     * $percentString = CrowdfundingHelper::percent(100);
     * echo $percentString;
     * </code>
     *
     * @param string $value
     *
     * @return string
     */
    public static function percent($value)
    {
        if (!$value) {
            $value = "0.0";
        }

        return $value . "%";
    }

    public static function socialProfileLink($link, $name, $options = array())
    {
        if (!empty($link)) {

            $targed = "";
            if (!empty($options["target"])) {
                $targed = 'target="' . Joomla\Utilities\ArrayHelper::getValue($options, "target") . '"';
            }

            $output = '<a href="' . $link . '" ' . $targed . '>' . htmlspecialchars($name, ENT_QUOTES, "UTF-8") . '</a>';

        } else {
            $output = htmlspecialchars($name, ENT_QUOTES, "utf-8");
        }

        return $output;
    }

    public static function rewardImage($image, $rewardId, $width = 250, $height = 250)
    {
        $html[] = '<img src="' . $image . '" width="' . (int)$width . '" height="' . (int)$height . '" ';
        if (!empty($rewardId)) {
            $html[] = ' id="js-reward-image-' . (int)$rewardId . '" ';
        }
        $html[] = '/>';

        return implode("\n", $html);
    }

    public static function rewardsNumber($number)
    {
        return (!$number) ? JText::_("COM_CROWDFUNDING_UNLIMITED") : (int)$number;
    }

    public static function rewardsAvailable($number, $distributed)
    {
        if (!empty($number)) {
            $result = abs($number - $distributed);
        } else {
            $result = JText::_("COM_CROWDFUNDING_UNLIMITED");
        }

        return $result;
    }

    /**
     * Prepare some specific CSS styles of the projects.
     *
     * @param object $item
     * @param Joomla\Registry\Registry $params
     *
     * @return string
     */
    public static function styles($item, $params)
    {
        $classes = array();

        // Prepare class Featured
        if (!empty($item->featured)) {
            $classes[] = $params->get("style_featured");
        }

        // Check dates
        $today = new JDate();
        $fundingEnd = new Prism\Date($item->funding_end);
        $fundingStart = new Prism\Date($item->funding_start);

        // Prepare completed campaign classes.
        if ($today > $fundingEnd) {
            if ($item->goal <= $item->funded) {
                $classes[] = $params->get("style_completed_successfully");
            } else {
                $classes[] = $params->get("style_completed_unsuccessfully");
            }
        }

        // Prepare class for a new campaign.
        if (($today < $fundingEnd) and $fundingStart->isCurrentWeekDay()) {
            $classes[] = $params->get("style_new");
        }

        // Prepare class for a ending soon campaign.
        if (($today < $fundingEnd) and $fundingEnd->isCurrentWeekDay()) {
            $classes[] = $params->get("style_ending_soon");
        }

        $classes = array_filter($classes);

        return implode(" ", $classes);
    }

    /**
     * Load jQuery Fancybox library.
     */
    public static function jqueryFancybox()
    {
        // Only load once
        if (!empty(self::$loaded[__METHOD__])) {
            return;
        }

        $document = JFactory::getDocument();

        $document->addStylesheet(JUri::root() . 'media/com_crowdfunding/css/jquery.fancybox.css');
        $document->addScript(JUri::root() . 'media/com_crowdfunding/js/jquery.fancybox.min.js');

        self::$loaded[__METHOD__] = true;
    }

    /**
     * Display a location of an user.
     *
     * @param string $name
     * @param string $countryCode
     *
     * @return string
     */
    public static function profileLocation($name, $countryCode)
    {
        $html = array();
        if (!empty($name)) {
            $html[] = '<div class="cf-location">';
            $html[] = htmlentities($name, ENT_QUOTES, "UTF-8");

            if (!empty($countryCode)) {
                $html[] = ", " . htmlentities($countryCode, ENT_QUOTES, "UTF-8");
            }

            $html[] = '</div>';
        }

        return implode("", $html);
    }

    public static function profileName($name, $link = "", $verified = false)
    {
        $html = array();

        if (!empty($link)) {
            $html[] = '<a href="' . $link . '">';
            $html[] = htmlspecialchars($name, ENT_QUOTES, "UTF-8");
            $html[] = '</a>';
        } else {
            $html[] = htmlspecialchars($name, ENT_QUOTES, "UTF-8");
        }

        if ($verified) {
            $html[] = '<span class="glyphicon glyphicon-ok-sign hasTooltip cursor-pointer" title="'.JText::_("COM_CROWDFUNDING_TOOLTIP_PROFILE_VERIFIED").'"></span>';
        }

        return implode("\n", $html);
    }

    public static function profileAvatar($avatar, $link = "", $options = array())
    {
        $class = (isset($options["class"])) ? 'class="'.$options["class"].'"' : "";

        $html = array();

        if (!empty($link)) {
            $html[] = '<a href="' . $link . '" '.$class.'>';
            $html[] = '<img class="media-object" src="'.$avatar.'" />';
            $html[] = '</a>';
        } else {
            $html[] = '<a href="javascript: void(0);" '.$class.' />';
            $html[] = '<img class="media-object" src="'.$avatar.'" />';
            $html[] = '</a>';
        }

        return implode("\n", $html);
    }
}
