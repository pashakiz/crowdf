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

/**
 * These class contains methods using for upgrading the extension
 */
class CrowdfundingDataInstallHelper
{
    public static function startTable()
    {
        echo '
        <div style="width: 600px;">
        <table class="table table-bordered table-striped">';
    }

    public static function endTable()
    {
        echo "</table></div>";
    }

    public static function addRowHeading($heading)
    {
        echo '
	    <tr class="info">
            <td colspan="3">' . $heading . '</td>
        </tr>';
    }

    /**
     * Display an HTML code for a row
     *
     * @param string $title
     * @param array  $result
     * @param string $info
     *
     * # Example
     *    array(
     *    type => success, important, warning,
     *    text => yes, no, off, on, warning,...
     *    )
     */
    public static function addRow($title, $result, $info)
    {
        $outputType = Joomla\Utilities\ArrayHelper::getValue($result, "type", "");
        $outputText = Joomla\Utilities\ArrayHelper::getValue($result, "text", "");

        $output = "";
        if (!empty($outputType) and !empty($outputText)) {
            $output = '<span class="label label-' . $outputType . '">' . $outputText . '</span>';
        }

        echo '
	    <tr>
            <td>' . $title . '</td>
            <td>' . $output . '</td>
            <td>' . $info . '</td>
        </tr>';
    }
}
