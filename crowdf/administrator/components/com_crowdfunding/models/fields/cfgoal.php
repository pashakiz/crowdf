<?php
/**
 * @package      Crowdfunding
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined('JPATH_PLATFORM') or die;

class JFormFieldCfGoal extends JFormField
{
    /**
     * The form field type.
     *
     * @var    string
     *
     * @since  11.1
     */
    protected $type = 'cfgoal';

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
        $required  = $this->required ? ' required aria-required="true"' : '';

        $cssLayout  = $this->element['css_layout'] ? $this->element['css_layout'] : "Bootstrap 2";

        // Prepare currency object.
        $params     = JComponentHelper::getParams("com_crowdfunding");
        /** @var  $params Joomla\Registry\Registry */

        $currency = Crowdfunding\Currency::getInstance(JFactory::getDbo(), $params->get("project_currency"));

        // Prepare amount object.
        $amount = new Crowdfunding\Amount($params, $this->value);
        $amount->setCurrency($currency);

        switch ($cssLayout) {

            case "Bootstrap 3":

                $html = array();
                $html[] = '<div class="input-group">';

                if ($currency->getSymbol()) { // Prepended
                    $html[] = '<div class="input-group-addon">'.$currency->getSymbol().'</div>';
                }

                $html[] = '<input type="text" name="' . $this->name . '" id="' . $this->id . '"' . ' value="' . $amount->format() . '"' . $class . $size . $disabled . $readonly . $maxLength . $required . '/>';

                // Prepend
                $html[] = '<div class="input-group-addon">'.$currency->getCode().'</div>';

                $html[] = '</div>';

                break;

            default: // Bootstrap 2

                $html = array();
                if ($currency->getSymbol()) { // Prepended
                    $html[] = '<div class="input-prepend input-append"><span class="add-on">' . $currency->getSymbol() . '</span>';
                } else { // Append
                    $html[] = '<div class="input-append">';
                }

                $html[] = '<input type="text" name="' . $this->name . '" id="' . $this->id . '"' . ' value="' . $amount->format() . '"' . $class . $size . $disabled . $readonly . $maxLength . $required . '/>';

                // Appended
                $html[] = '<span class="add-on">' . $currency->getCode() . '</span></div>';

                break;
        }

        return implode("\n", $html);
    }
}
