<?php
/**
 * @package      CrowdfundingData
 * @subpackage   Component
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2014 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// No direct access
defined('_JEXEC') or die;

/**
 * Crowdfunding records controller class.
 *
 * @package        CrowdfundingData
 * @subpackage     Component
 * @since          1.6
 */
class CrowdfundingDataControllerRecords extends Prism\Controller\Admin
{
    public function getModel($name = 'Record', $prefix = 'CrowdfundingDataModel', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);

        return $model;
    }

    public function view()
    {
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        $cid    = $this->input->post->get('cid', array(), 'array');
        $itemId = array_pop($cid);

        $this->setRedirect(JRoute::_("index.php?option=com_crowdfundingdata&view=record&id=".(int)$itemId, false));
    }
}
