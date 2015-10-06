<?php
/**
 * @package         CrowdfundingFiles
 * @subpackage      Plugins
 * @author          Todor Iliev
 * @copyright       Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license         http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// no direct access
defined('_JEXEC') or die;

jimport("Crowdfunding.init");
jimport("CrowdfundingFiles.init");

/**
 * Crowdfunding Files Plugin
 *
 * @package        CrowdfundingFiles
 * @subpackage     Plugins
 */
class plgContentCrowdfundingFiles extends JPlugin
{
    protected $autoloadLanguage = true;

    /**
     * @var JApplicationSite
     */
    protected $app;

    /**
     * @var Joomla\Registry\Registry
     */
    public $params;

    public function onContentAfterDisplay($context, &$item, &$params)
    {
        if (strcmp("com_crowdfunding.details", $context) != 0) {
            return null;
        }

        if ($this->app->isAdmin()) {
            return null;
        }

        $doc = JFactory::getDocument();
        /**  @var $doc JDocumentHtml */

        // Check document type
        $docType = $doc->getType();
        if (strcmp("html", $docType) != 0) {
            return null;
        }

        $html = "";

        $files = new CrowdfundingFiles\Files(JFactory::getDbo());
        $files->load(array("project_id" => $item->id));

        if (count($files) > 0) {

            $mediaFolderUri = CrowdfundingFilesHelper::getMediaFolderUri($item->user_id);

            // Get the path for the layout file
            $path = JPath::clean(JPluginHelper::getLayoutPath('content', 'crowdfundingfiles'));

            // Render the login form.
            ob_start();
            include $path;
            $html = ob_get_clean();
        }

        return $html;
    }
}
