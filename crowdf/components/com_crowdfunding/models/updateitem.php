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

class CrowdfundingModelUpdateItem extends JModelItem
{
    protected $item;

    /**
     * Returns a reference to the a Table object, always creating it.
     *
     * @param   string $type    The table type to instantiate
     * @param   string $prefix A prefix for the table class name. Optional.
     * @param   array $config  Configuration array for model. Optional.
     *
     * @return  JTable  A database object
     * @since   1.6
     */
    public function getTable($type = 'Update', $prefix = 'CrowdfundingTable', $config = array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }

    /**
     * Method to auto-populate the model state.
     * Note. Calling getState in this method will result in recursion.
     *
     * @since    1.6
     */
    protected function populateState()
    {
        $app = JFactory::getApplication();
        /** @var  $app JApplicationSite */

        // Load the object state.
        $value = $app->input->getInt('id');
        $this->setState($this->getName() . '.id', $value);

        $value = $app->input->getInt('project_id');
        $this->setState('project_id', $value);

        // Load the parameters.
        $params = $app->getParams();
        $this->setState('params', $params);
    }

    /**
     * Method to get an object.
     *
     * @param    integer  $id  The id of the object to get.
     *
     * @return    null|object    Object on success, false on failure.
     */
    public function getItem($id = null)
    {
        if (empty($id)) {
            $id = $this->getState($this->getName() . '.id');
        }

        $storedId = $this->getStoreId($id);

        if (!isset($this->item[$storedId])) {
            $this->item[$storedId] = null;

            // Get a level row instance.
            $table = $this->getTable();
            $table->load($id);

            // Attempt to load the row.
            if ($table->get("id")) {
                $properties = $table->getProperties();
                $this->item[$storedId] = Joomla\Utilities\ArrayHelper::toObject($properties);
            }
        }

        return $this->item[$storedId];
    }

    public function remove($itemId, $userId = null)
    {
        $db    = $this->getDbo();
        $query = $db->getQuery(true);

        $query
            ->delete($db->quoteName("#__crowdf_updates"))
            ->where($db->quoteName("id") . "= " . (int)$itemId);

        if (!empty($userId)) {
            $query->where("user_id=" . (int)$userId);
        }

        $db->setQuery($query);
        $db->execute();
    }
}
