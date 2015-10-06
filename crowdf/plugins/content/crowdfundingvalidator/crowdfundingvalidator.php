<?php
/**
 * @package      Crowdfunding
 * @subpackage   Plugins
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.plugin.plugin');

jimport('Prism.init');
jimport('Crowdfunding.init');

/**
 * This plugin validates data.
 * It works only on front-end.
 *
 * @package      Crowdfunding
 * @subpackage   Plugins
 */
class plgContentCrowdfundingValidator extends JPlugin
{
    protected $allowedContexts = array("com_crowdfunding.basic", "com_crowdfunding.funding", "com_crowdfunding.story");

    protected $autoloadLanguage = true;

    /**
     * @var Joomla\Registry\Registry
     */
    public $params;

    /**
     * This method validates project data that comes from users,
     * during the process of creating campaign.
     *
     * @param string $context
     * @param array $data
     * @param Joomla\Registry\Registry $params
     *
     * @return null|array
     */
    public function onContentValidate($context, &$data, &$params)
    {
        if (!in_array($context, $this->allowedContexts)) {
            return null;
        }

        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        if ($app->isAdmin()) {
            return null;
        }

        $doc = JFactory::getDocument();
        /**  @var $doc JDocumentHtml */

        // Check document type
        $docType = $doc->getType();
        if (strcmp("html", $docType) != 0) {
            return null;
        }

        $result = array(
            "success" => true
        );

        switch ($context) {
            case "com_crowdfunding.basic":
                $result = $this->validateStepBasic($data, $params);
                break;

            case "com_crowdfunding.funding":
                $result = $this->validateStepFunding($data, $params);
                break;

            case "com_crowdfunding.story":
                $result = $this->validateStepStory($data, $params);
                break;
        }

        return $result;
    }

    /**
     * This method validates project data that comes from users,
     * during the process of creating campaign.
     * The system executes this method when the data be saved.
     *
     * @param string $context
     * @param object $item
     * @param Joomla\Registry\Registry $params
     *
     * @return null|array
     */
    public function onContentValidateAfterSave($context, &$item, &$params)
    {
        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        if ($app->isAdmin()) {
            return null;
        }

        $doc = JFactory::getDocument();
        /**  @var $doc JDocumentHtml */

        // Check document type
        $docType = $doc->getType();
        if (strcmp("html", $docType) != 0) {
            return null;
        }

        if (!in_array($context, $this->allowedContexts)) {
            return null;
        }

        $result = array(
            "success" => false,
            "message" => ""
        );

        // Validate pitch image and video URL.
        if ($this->params->get("validate_story_image_video", 1) and (!$item->pitch_image and !$item->pitch_video)) {
            $result["message"] = JText::_("PLG_CONTENT_CROWDFUNDINGVALIDATOR_ERROR_INVALID_PITCH_IMAGE_OR_VIDEO");
            return $result;
        }

        // Validation completed successfully.
        $result = array("success" => true);

        return $result;
    }

    protected function validateStepBasic($data, $params)
    {
        $result = array(
            "success" => true
        );

        return $result;
    }

    /**
     * This method validates project data
     * when someone decides to change a project state ( to publish or approve ).
     * It works only on front-end.
     *
     * @param string $context
     * @param object $item
     * @param Joomla\Registry\Registry $params
     * @param int $state
     *
     * @return null|array
     */
    public function onContentValidateChangeState($context, &$item, &$params, $state)
    {
        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        if ($app->isAdmin()) {
            return null;
        }

        $doc = JFactory::getDocument();
        /**  @var $doc JDocumentHtml */

        // Check document type
        $docType = $doc->getType();
        if (strcmp("html", $docType) != 0) {
            return null;
        }

        if (strcmp("com_crowdfunding.projects.changestate", $context) != 0) {
            return null;
        }

        $result = array(
            "success" => false,
            "message" => ""
        );

        // If the project is approved, do not allow unpublishing.
        if ($this->params->get("validate_state_approved", 1) and ($item->published and $item->approved)) {
            $result["message"] = JText::_("PLG_CONTENT_CROWDFUNDINGVALIDATOR_ERROR_APPROVED_UNPUBLISH");
            return $result;
        }

        // It is not necessary to continue with validations if it is a process of unpublishing.
        // It is important to do following validation when someone publish his project.
        if ($state == Prism\Constants::UNPUBLISHED) {
            $result = array("success" => true);
            return $result;
        }

        if (!$item->goal) {
            $result["message"] = JText::_("PLG_CONTENT_CROWDFUNDINGVALIDATOR_ERROR_INVALID_GOAL");
            return $result;
        }

        if (!$item->funding_type) {
            $result["message"] = JText::_("PLG_CONTENT_CROWDFUNDINGVALIDATOR_ERROR_INVALID_FUNDING_TYPE");
            return $result;
        }

        // Validate funding duration.
        $fundingEnd = new Prism\Validator\Date($item->funding_end);
        if (!$fundingEnd->isValid($item->funding_end) and !$item->funding_days) {
            $result["message"] = JText::_("PLG_CONTENT_CROWDFUNDINGVALIDATOR_ERROR_INVALID_FUNDING_DURATION");
            return $result;
        }

        // Validate pitch image and video.
        if ($this->params->get("validate_story_image_video", 1) and (!$item->pitch_image and !$item->pitch_video)) {
            $result["message"] = JText::_("PLG_CONTENT_CROWDFUNDINGVALIDATOR_ERROR_INVALID_PITCH_IMAGE_OR_VIDEO");
            return $result;
        }

        $desc = JString::trim($item->description);
        if (!$desc) {
            $result["message"] = JText::_("PLG_CONTENT_CROWDFUNDINGVALIDATOR_ERROR_INVALID_DESCRIPTION");
            return $result;
        }

        // Validation completed successfully.
        $result = array("success" => true);

        return $result;
    }

    protected function validateStepStory($data, $params)
    {
        $result = array(
            "success" => true
        );

        return $result;
    }

    /**
     * Validate user data that comes from step "Funding".
     *
     * @param array $data
     * @param Joomla\Registry\Registry $params
     *
     * @return array
     */
    protected function validateStepFunding(&$data, &$params)
    {
        $result = array(
            "success" => false,
            "message" => ""
        );

        // Validate minimum and maximum amount.
        if ($this->params->get("validate_amount", 1)) {
            $goal      = Joomla\Utilities\ArrayHelper::getValue($data, "goal", 0, "float");
            $minAmount = $params->get("project_amount_minimum", 100);
            $maxAmount = $params->get("project_amount_maximum");

            // Verify minimum amount
            if ($goal < $minAmount) {
                $result["message"] = JText::_('PLG_CONTENT_CROWDFUNDINGVALIDATOR_ERROR_INVALID_GOAL');
                return $result;
            }


            // Verify maximum amount
            if (!empty($maxAmount) and ($goal > $maxAmount)) {
                $result["message"] = JText::_('PLG_CONTENT_CROWDFUNDINGVALIDATOR_ERROR_INVALID_GOAL');
                return $result;
            }
        }

        // Validate funding duration - days or date.
        if ($this->params->get("validate_funding_duration", 1)) {

            $minDays = (int)$params->get("project_days_minimum", 15);
            $maxDays = (int)$params->get("project_days_maximum", 0);

            $fundingType = Joomla\Utilities\ArrayHelper::getValue($data, "funding_duration_type");

            // Validate funding type "days"
            if (strcmp("days", $fundingType) == 0) {

                $days = Joomla\Utilities\ArrayHelper::getValue($data, "funding_days", 0, "integer");
                if ($days < $minDays) {
                    $result["message"] = JText::_('PLG_CONTENT_CROWDFUNDINGVALIDATOR_ERROR_INVALID_DAYS');
                    return $result;
                }

                if (!empty($maxDays) and ($days > $maxDays)) {
                    $result["message"] = JText::_('PLG_CONTENT_CROWDFUNDINGVALIDATOR_ERROR_INVALID_DAYS');
                    return $result;
                }

            } else { // Validate funding type "date"

                $fundingEndDate = Joomla\Utilities\ArrayHelper::getValue($data, "funding_end");

                $dateValidator = new Prism\Validator\Date($fundingEndDate);
                if (!$dateValidator->isValid()) {
                    $result["message"] = JText::_('PLG_CONTENT_CROWDFUNDINGVALIDATOR_ERROR_INVALID_DATE');
                    return $result;
                }
            }
        }

        // Validate funding duration when the projects is published and approved.
        if ($this->params->get("validate_funding_duration_approved", 1)) {

            // Get item and check it for active state ( published and approved ).
            $itemId = Joomla\Utilities\ArrayHelper::getValue($data, "id");
            $userId = JFactory::getUser()->get("id");

            $item   = $this->getItem($itemId, $userId);

            // Validate date if user want to edit date, while the project is published.
            if ($item->published and $item->approved) {

                $minDays = (int)$params->get("project_days_minimum", 15);
                $maxDays = (int)$params->get("project_days_maximum", 0);

                $fundingType = Joomla\Utilities\ArrayHelper::getValue($data, "funding_duration_type");

                // Generate funding end date from days.
                if (strcmp("days", $fundingType) == 0) {

                    // Get funding days.
                    $days = Joomla\Utilities\ArrayHelper::getValue($data, "funding_days", 0, "integer");

                    $fundingStartDate = new Crowdfunding\Date($item->funding_start);
                    $endDate          = $fundingStartDate->calculateEndDate($days);
                    $fundingEndDate   = $endDate->format("Y-m-d");

                } else { // Get funding end date from request
                    $fundingEndDate = Joomla\Utilities\ArrayHelper::getValue($data, "funding_end");
                }

                // Validate the period.
                $dateValidator = new Crowdfunding\Validator\Project\Period($item->funding_start, $fundingEndDate, $minDays, $maxDays);
                if (!$dateValidator->isValid()) {
                    $result["message"] = (!empty($maxDays)) ?
                        JText::sprintf("PLG_CONTENT_CROWDFUNDINGVALIDATOR_ERROR_INVALID_ENDING_DATE_MIN_MAX_DAYS", $minDays, $maxDays) :
                        JText::sprintf("PLG_CONTENT_CROWDFUNDINGVALIDATOR_ERROR_INVALID_ENDING_DATE_MIN_DAYS", $minDays);

                    return $result;
                }

            }
        }

        // Validations completed successfully.
        $result = array(
            "success" => true
        );

        return $result;
    }

    /**
     * Load project data from database.
     *
     * @param int $itemId
     * @param int $userId
     *
     * @return object
     */
    protected function getItem($itemId, $userId)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query
            ->select("a.published, a.approved, a.funding_start")
            ->from($db->quoteName("#__crowdf_projects", "a"))
            ->where("a.id = " .(int)$itemId)
            ->where("a.user_id = " .(int)$userId);

        $db->setQuery($query);

        $result = $db->loadObject();

        $result->published = (0 < $result->published) ? true : false;
        $result->approved = (0 < $result->approved) ? true : false;

        return $result;
    }
}
