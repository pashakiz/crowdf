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

class CrowdfundingModelBacking extends JModelLegacy
{
    protected $item;

    /**
     * Model context string.
     *
     * @var    string
     * @since  11.1
     */
    protected $context = 'com_crowdfunding.backing';

    /**
     * Returns a reference to the a Table object, always creating it.
     *
     * @param   string $type    The table type to instantiate
     * @param   string $prefix A prefix for the table class name. Optional.
     * @param   array  $config Configuration array for model. Optional.
     *
     * @return  JTable  A database object
     * @since   1.6
     */
    public function getTable($type = 'Project', $prefix = 'CrowdfundingTable', $config = array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }

    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @since    1.6
     */
    protected function populateState()
    {
        $app = JFactory::getApplication();
        /** @var  $app JApplicationSite */

        // Project ID
        $itemId = $app->input->getUint('id');
        $this->setState('id', $itemId);

        // Get reward ID
        $projectContext = $this->getProjectContext($itemId);
        $value          = $app->getUserStateFromRequest($projectContext . ".rid", 'rid');
        $this->setState('reward_id', $value);

        // Load the parameters.
        $params = $app->getParams();
        $this->setState('params', $params);
    }

    /**
     * Return the context,
     * used to for storing project data in this model.
     *
     * @param int $projectId
     *
     * @return string
     */
    public function getProjectContext($projectId)
    {
        return $this->context . ".project" . $projectId;
    }

    /**
     * Method to get an object.
     *
     * @param    integer $id   The id of the object to get.
     *
     * @return    mixed    Object on success, false on failure.
     */
    public function getItem($id = null)
    {
        if (empty($id)) {
            $id = $this->getState('id');
        }

        if (is_null($this->item)) {

            $db    = $this->getDbo();
            $query = $db->getQuery(true);

            $query
                ->select(
                    "a.id, a.title, a.short_desc, a.image, " .
                    "a.funded, a.goal, a.pitch_video, a.pitch_image, " .
                    "a.funding_start, a.funding_end, a.funding_days, " .
                    "a.funding_type, a.user_id,  a.type_id, " .
                    "b.name AS user_name, " .
                    $query->concatenate(array("a.id", "a.alias"), ":") . ' AS slug, ' .
                    $query->concatenate(array("c.id", "c.alias"), ":") . ' AS catslug'
                )
                ->from($db->quoteName("#__crowdf_projects", "a"))
                ->innerJoin($db->quoteName('#__users', 'b') .' ON a.user_id = b.id')
                ->innerJoin($db->quoteName('#__categories', 'c') . ' ON a.catid = c.id')
                ->where("a.id = " . (int)$id)
                ->where("a.published = 1")
                ->where("a.approved  = 1");

            $db->setQuery($query, 0, 1);
            $result = $db->loadObject();

            // Attempt to load the row.
            if (!empty($result)) {

                // Calculate ending date by days left.
                if (!empty($result->funding_days)) {
                    $fundingStartDate = new Crowdfunding\Date($result->funding_start);
                    $fundingEndDate = $fundingStartDate->calculateEndDate($result->funding_days);
                    $result->funding_end = $fundingEndDate->format("Y-m-d");
                }

                // Calculate funded percent
                $percent = new Prism\Math();
                $percent->calculatePercentage($result->funded, $result->goal, 0);
                $result->funded_percents = (string)$percent;

                // Calculate days left.
                $today = new Crowdfunding\Date();
                $result->days_left       = $today->calculateDaysLeft($result->funding_days, $result->funding_start, $result->funding_end);

                $this->item              = $result;

            }
        }

        return $this->item;
    }
}
