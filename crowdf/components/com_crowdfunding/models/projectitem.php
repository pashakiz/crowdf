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

class CrowdfundingModelProjectItem extends JModelItem
{
    protected $items = array();

    /**
     * Returns a reference to the a Table object, always creating it.
     *
     * @param   string $type    The table type to instantiate
     * @param   string $prefix A prefix for the table class name. Optional.
     * @param   array  $config Configuration array for model. Optional.
     *
     * @return  CrowdfundingTableProject  A database object
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
        parent::populateState();

        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        // Get the pk of the record from the request.
        $itemId = $app->input->getInt("id");
        $this->setState($this->getName() . '.id', $itemId);

        // Load the parameters.
        $value = $app->getParams($this->option);
        $this->setState('params', $value);

    }

    public function getItem($itemId, $userId)
    {
        $storedId = $this->getStoreId($itemId.$userId);

        if (!isset($this->items[$storedId])) {

            $db = $this->getDbo();
            /** @var $db JDatabaseDriver */

            // Create a new query object.
            $query = $db->getQuery(true);

            // Select the required fields from the table.
            $query->select(
                'a.id, a.title, a.alias, a.short_desc, a.description, a.image, a.image_square, a.image_small, a.location_id, ' .
                'a.goal, a.funded, a.funding_type, a.funding_start, a.funding_end, a.funding_days, ' .
                'a.pitch_video, a.pitch_image, a.hits, a.created, a.featured, a.published, a.approved, a.ordering, a.catid, a.type_id, a.user_id, ' .
                $query->concatenate(array("a.id", "a.alias"), ":") . ' AS slug, ' .
                'b.name AS user_name, ' .
                $query->concatenate(array("c.id", "c.alias"), ":") . " AS catslug"
            );

            $query->from($db->quoteName('#__crowdf_projects', 'a'));
            $query->innerJoin($db->quoteName('#__users', 'b') . ' ON a.user_id = b.id');
            $query->innerJoin($db->quoteName('#__categories', 'c') . ' ON a.catid = c.id');

            $query->where("a.id = ". (int)$itemId);
            $query->where("a.user_id = ". (int)$userId);

            $db->setQuery($query);

            $item = $db->loadObject();

            if (!empty($item)) {

                // Calculate funding end date
                if (!empty($item->funding_days)) {
                    $fundingStartDate  = new Crowdfunding\Date($item->funding_start);
                    $fundingEndDate    = $fundingStartDate->calculateEndDate($item->funding_days);
                    $item->funding_end = $fundingEndDate->format("Y-m-d");
                }

                // Calculate funded percentage.
                $percent = new Prism\Math();
                $percent->calculatePercentage($item->funded, $item->goal, 0);
                $item->funded_percents = (string)$percent;

                // Calculate days left
                $today = new Crowdfunding\Date();
                $item->days_left       = $today->calculateDaysLeft($item->funding_days, $item->funding_start, $item->funding_end);

            } else {
                $item = new stdClass();
            }

            $this->items[$storedId] = $item;
        }

        return $this->items[$storedId];
    }

    /**
     * Publish or not an item. If state is going to be published,
     * we have to calculate end date.
     *
     * @param integer $itemId
     * @param integer $userId
     * @param integer $state
     *
     * @throws Exception
     */
    public function saveState($itemId, $userId, $state)
    {
        $keys = array(
            "id"      => $itemId,
            "user_id" => $userId
        );

        /** @var $row CrowdfundingTableProject */
        $row = $this->getTable();
        $row->load($keys);

        // Prepare data only if the user publish the project.
        if ($state == Prism\Constants::PUBLISHED) {
            $this->prepareTable($row);
        }

        $row->set("published", (int)$state);
        $row->store();

        // Trigger the event

        $context = $this->option . '.project';
        $pks     = array($row->get("id"));

        // Include the content plugins for the change of state event.
        JPluginHelper::importPlugin('content');

        // Trigger the onContentChangeState event.
        $dispatcher = JEventDispatcher::getInstance();
        $results    = $dispatcher->trigger("onContentChangeState", array($context, $pks, $state));

        if (in_array(false, $results, true)) {
            throw new Exception(JText::_("COM_CROWDFUNDING_ERROR_CHANGE_STATE"));
        }

    }

    /**
     * This method calculate start date and validate funding period.
     *
     * @param CrowdfundingTableProject $table
     *
     * @throws Exception
     */
    protected function prepareTable(&$table)
    {
        // Calculate start and end date if the user publish a project for first time.
        $fundingStartDate = new Prism\Validator\Date($table->funding_start);
        if (!$fundingStartDate->isValid($table->funding_start)) {

            $fundingStart         = new JDate();
            $table->funding_start = $fundingStart->toSql();

            // If funding type is "days", calculate end date.
            if ($table->get("funding_days")) {
                $fundingStartDate = new Crowdfunding\Date($table->get("funding_start"));
                $endDate = $fundingStartDate->calculateEndDate($table->get("funding_days"));
                $table->set("funding_end", $endDate->format("Y-m-d"));
            }

        }

        // Get parameters
        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        $params = $app->getParams();
        /** @var  $params Joomla\Registry\Registry */

        $minDays = $params->get("project_days_minimum", 15);
        $maxDays = $params->get("project_days_maximum");

        // If there is an ending date, validate the period.
        $fundingEndDate = new Prism\Validator\Date($table->get("funding_end"));
        if ($fundingEndDate->isValid()) {

            $validatorPeriod = new Crowdfunding\Validator\Project\Period($table->get("funding_start"), $table->get("funding_end"), $minDays, $maxDays);
            if (!$validatorPeriod->isValid()) {

                if (!empty($maxDays)) {
                    throw new RuntimeException(JText::sprintf("COM_CROWDFUNDING_ERROR_INVALID_ENDING_DATE_MIN_MAX_DAYS", $minDays, $maxDays));
                } else {
                    throw new RuntimeException(JText::sprintf("COM_CROWDFUNDING_ERROR_INVALID_ENDING_DATE_MIN_DAYS", $minDays));
                }
            }

        }

    }

    /**
     * This method counts the rewards of the project.
     *
     * @param  integer $itemId Project id
     *
     * @return number
     */
    protected function countRewards($itemId)
    {
        $db    = $this->getDbo();
        $query = $db->getQuery(true);

        $query
            ->select("COUNT(*)")
            ->from($db->quoteName("#__crowdf_rewards", "a"))
            ->where("a.project_id = " . (int)$itemId);

        $db->setQuery($query);
        $result = $db->loadResult();

        return (int)$result;
    }
}
