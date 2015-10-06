<?php
/**
 * @package      Crowdfunding
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// No direct access
defined('_JEXEC') or die;

/**
 * Crowdfunding users controller
 *
 * @package      Crowdfunding
 * @subpackage   Components
 */
class CrowdfundingControllerUsers extends Prism\Controller\Admin
{
    public function getModel($name = 'User', $prefix = 'CrowdfundingModel', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);

        return $model;
    }

    public function view()
    {
        $cid = $this->input->get("cid", array(), "array");

        $id  = array_shift($cid);

        $this->setRedirect(JRoute::_("index.php?option=com_crowdfunding&view=user&id=".(int)$id, false));
    }
}
