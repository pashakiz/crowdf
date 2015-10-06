<?php
/**
 * @package      Crowdfunding
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined('JPATH_PLATFORM') or die;

class JFormRuleCfCategory extends JFormRule
{
    /**
     * Method to test the email address and optionally check for uniqueness.
     *
     * @param   SimpleXMLElement $element   The SimpleXMLElement object representing the <field /> tag for the form field object.
     * @param   mixed            $value     The form field value to validate.
     * @param   string           $group     The field name group control value. This acts as as an array container for the field.
     *                                      For example if the field has name="foo" and the group value is set to "bar" then the
     *                                      full field name would end up being "bar[foo]".
     * @param   JRegistry        $input     An optional JRegistry object with the entire data set to validate against the entire form.
     * @param   JForm            $form      The form object for which the field is being tested.
     *
     * @return  boolean  True if the value is valid, false otherwise.
     *
     * @since   11.1
     */
    public function test(SimpleXMLElement $element, $value, $group = null, JRegistry $input = null, JForm $form = null)
    {
        // If the field is empty and not required, the field is valid.
        $required = ((string)$element['required'] == 'true' || (string)$element['required'] == 'required');

        if ($required and !$value) {
            return false;
        }

        // Get the database object and a new query object.
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        // Build the query.
        $query
            ->select('COUNT(*)')
            ->from($db->quoteName("#__categories", "a"))
            ->where('a.extension = ' . $db->quote("com_crowdfunding"))
            ->where('a.published = 1');

        // Set and query the database.
        $db->setQuery($query, 0, 1);
        $result = (bool)$db->loadResult();

        return (!$result) ? false : true;
    }
}
