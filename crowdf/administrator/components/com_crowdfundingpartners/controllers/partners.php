<?php
/**
 * @package      CrowdfundingPartners
 * @subpackage   Component
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2014 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// No direct access
defined('_JEXEC') or die;

/**
 * Crowdfunding Partners controller class.
 *
 * @package        CrowdfundingPartners
 * @subpackage     Component
 * @since          1.6
 */
class CrowdfundingPartnersControllerPartners extends Prism\Controller\Admin
{
    public function getModel($name = 'Partner', $prefix = 'CrowdfundingPartnersModel', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);
        return $model;
    }
}
