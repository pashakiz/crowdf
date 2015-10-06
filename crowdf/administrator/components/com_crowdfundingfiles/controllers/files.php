<?php
/**
 * @package      CrowdfundingFiles
 * @subpackage   Component
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2014 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// No direct access
defined('_JEXEC') or die;

/**
 * Crowdfunding files controller class.
 *
 * @package        CrowdfundingFiles
 * @subpackage     Component
 * @since          1.6
 */
class CrowdfundingFilesControllerFiles extends Prism\Controller\Admin
{
    public function getModel($name = 'File', $prefix = 'CrowdfundingFilesModel', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);
        return $model;
    }
}
