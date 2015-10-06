<?php
/**
 * @package      Crowdfunding
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined('JPATH_PLATFORM') or die;

class JFormFieldCfPercent extends JFormField
{
    /**
     * The form field type.
     *
     * @var    string
     *
     * @since  11.1
     */
    protected $type = 'cfpercent';

    /**
     * Method to get the field input markup.
     *
     * @return  string  The field input markup.
     *
     * @since   11.1
     */
    protected function getInput()
    {
        // Initialize some field attributes.
        $size      = $this->element['size'] ? ' size="' . (int)$this->element['size'] . '"' : '';
        $maxLength = $this->element['maxlength'] ? ' maxlength="' . (int)$this->element['maxlength'] . '"' : '';
        $readonly  = ((string)$this->element['readonly'] == 'true') ? ' readonly="readonly"' : '';
        $disabled  = ((string)$this->element['disabled'] == 'true') ? ' disabled="disabled"' : '';
        $class     = (!empty($this->element['class'])) ? ' class="' . (string)$this->element['class'] . '"' : "";

        $cssLayout  = $this->element['css_layout'] ? $this->element['css_layout'] : "Bootstrap 2";

        // Initialize JavaScript field attributes.
        $onchange = $this->element['onchange'] ? ' onchange="' . (string)$this->element['onchange'] . '"' : '';

        switch ($cssLayout) {

            case "Bootstrap 3":

                $html = array();
                $html[] = '<div class="input-group">';

                $html []= '<input type="text" name="' . $this->name . '" id="' . $this->id . '"' . ' value="' .
                    htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '"' . $class . $size . $disabled . $readonly . $onchange . $maxLength . '/>';

                // Appended
                $html[] = '<div class="input-group-addon">%</div>';
                $html[] = '</div>';

                break;

            default: // Bootstrap 2

                $html = array();
                $html[] = '<div class="input-append">';

                $html[] = '<input type="text" name="' . $this->name . '" id="' . $this->id . '"' . ' value="' .
                    htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '"' . $class . $size . $disabled . $readonly . $onchange . $maxLength . '/>';

                // Appended
                $html[] = '<span class="add-on">%</span>';
                $html[] = '</div>';

                break;
        }

        return implode("\n", $html);
    }
}
