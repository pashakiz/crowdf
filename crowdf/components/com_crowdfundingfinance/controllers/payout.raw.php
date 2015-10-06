<?php
/**
 * @package      CrowdfundingFinance
 * @subpackage   Component
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2014 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

/**
 * Payouts controller class.
 *
 * @package        CrowdfundingFinance
 * @subpackage     Component
 * @since          1.6
 */
class CrowdfundingFinanceControllerPayout extends JControllerForm
{
    /**
     * Save an item
     */
    public function save($key = null, $urlVar = null)
    {
        $response = new Prism\Response\Json();

        $userId    = JFactory::getUser()->get("id");
        if (!$userId) {
            // Send response to the browser
            $response
                ->setTitle(JText::_("COM_CROWDFUNDINGFINANCE_FAIL"))
                ->setText(JText::_("COM_CROWDFUNDINGFINANCE_ERROR_SYSTEM"))
                ->failure();

            echo $response;
            JFactory::getApplication()->close();
        }
        
        // Get project ID.
        $projectId = $this->input->post->get('project_id');

        // Validate project owner
        $validator = new Crowdfunding\Validator\Project\Owner(JFactory::getDbo(), $projectId, $userId);
        if (!$validator->isValid()) {
            // Send response to the browser
            $response
                ->setTitle(JText::_("COM_CROWDFUNDINGFINANCE_FAIL"))
                ->setText(JText::_("COM_CROWDFUNDINGFINANCE_INVALID_PROJECT"))
                ->failure();

            echo $response;
            JFactory::getApplication()->close();
        }

        $data = array(
            "id"                => $projectId,
            "paypal_email"      => $this->input->post->get('paypal_email', null, "string"),
            "paypal_first_name" => $this->input->post->get('paypal_first_name'),
            "paypal_last_name"  => $this->input->post->get('paypal_last_name'),
            "iban"              => $this->input->post->get('iban'),
            "bank_account"      => $this->input->post->get('bank_account', null, "string")
        );

        $model = $this->getModel();
        /** @var $model CrowdfundingFinanceModelPayout */

        try {

            $model->save($data);

        } catch (Exception $e) {

            $response
                ->setTitle(JText::_("COM_CROWDFUNDINGFINANCE_FAIL"))
                ->setText(JText::_('COM_CROWDFUNDINGFINANCE_ERROR_SYSTEM'))
                ->failure();

            echo $response;
            JFactory::getApplication()->close();

        }

        $response
            ->setTitle(JText::_("COM_CROWDFUNDINGFINANCE_SUCCESS"))
            ->setText(JText::_("COM_CROWDFUNDINGFINANCE_PAYOUT_DATA_SAVED_SUCCESSFULLY"))
            ->success();

        echo $response;
        JFactory::getApplication()->close();
    }
}
