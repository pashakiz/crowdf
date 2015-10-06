<?php
/**
 * @package      Crowdfunding
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.html.html');
jimport('joomla.form.formfield');
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');

/**
 * Form field class that loads countries as options,
 * using code with 4 letters for ID.
 *
 * @package      Crowdfunding
 * @subpackage   Components
 * @since        1.6
 */
class JFormFieldCfCountries extends JFormFieldList
{
    /**
     * The form field type.
     *
     * @var     string
     * @since   1.6
     */
    protected $type = 'cfcountries';

    /**
     * Method to get the field options.
     *
     * @return  array   The field option objects.
     * @since   1.6
     */
    protected function getOptions()
    {
        $indexes = array("id", "code");
        $index   = (string)$this->element["index"];

        if (!in_array($index, $indexes)) {
            $index = "id";
        }

        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query
            ->select('a.'.$index.' AS value, a.name AS text')
            ->from($db->quoteName('#__crowdf_countries', 'a'))
            ->order("a.name ASC");

        // Get the options.
        $db->setQuery($query);
        $options = $db->loadObjectList();

        // Merge any additional options in the XML definition.
        $options = array_merge(parent::getOptions(), $options);

        return $options;
    }
}
