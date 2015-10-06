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

class CrowdfundingModelPayments extends JModelLegacy
{
    /**
     * @param int $projectId
     * @param Joomla\Registry\Registry $params
     * @param object $paymentSession
     *
     * @return stdClass
     * @throws UnexpectedValueException
     */
    public function prepareItem($projectId, $params, $paymentSession)
    {
        $project = new Crowdfunding\Project(JFactory::getDbo());
        $project->load($projectId);

        if (!$project->getId()) {
            throw new UnexpectedValueException(JText::_("COM_CROWDFUNDING_ERROR_INVALID_PROJECT"));
        }

        if ($project->isCompleted()) {
            throw new UnexpectedValueException(JText::_("COM_CROWDFUNDING_ERROR_COMPLETED_PROJECT"));
        }

        // Get currency
        $currency   = Crowdfunding\Currency::getInstance(JFactory::getDbo(), $params->get("project_currency"));

        // Create amount object.
        $amount   = new Crowdfunding\Amount($params);
        $amount->setCurrency($currency);

        $item = new stdClass();

        $item->id             = $project->getId();
        $item->title          = $project->getTitle();
        $item->slug           = $project->getSlug();
        $item->catslug        = $project->getCatSlug();
        $item->rewardId       = $paymentSession->rewardId;
        $item->starting_date  = $project->getFundingStart();
        $item->ending_date    = $project->getFundingEnd();

        $item->amount         = $paymentSession->amount;
        $item->currencyCode   = $currency->getCode();

        $item->amountFormated = $amount->setValue($item->amount)->format();
        $item->amountCurrency = $amount->setValue($item->amount)->formatCurrency();

        return $item;
    }
}
