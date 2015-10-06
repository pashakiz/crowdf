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
 * Crowdfunding Files controller class.
 *
 * @package        CrowdfundingFiles
 * @subpackage     Component
 * @since          1.6
 */
class CrowdfundingFilesControllerFiles extends JControllerLegacy
{
    /**
     * Method to get a model object, loading it if required.
     *
     * @param    string $name   The model name. Optional.
     * @param    string $prefix The class prefix. Optional.
     * @param    array  $config Configuration array for model. Optional.
     *
     * @return    object    The model.
     * @since    1.5
     */
    public function getModel($name = 'Files', $prefix = 'CrowdfundingFilesModel', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);

        return $model;
    }

    public function upload()
    {
        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        $response = new Prism\Response\Json();

        $userId = JFactory::getUser()->get("id");
        if (!$userId) {

            $response
                ->setTitle(JText::_('COM_CROWDFUNDINGFILES_FAIL'))
                ->setText(JText::_('COM_CROWDFUNDINGFILES_ERROR_NOT_LOG_IN'))
                ->failure();

            echo $response;
            JFactory::getApplication()->close();
        }

        $projectId = $this->input->post->get("project_id");

        // Get component parameters
        $params = $app->getParams("com_crowdfundingfiles");
        /** @var  $params Joomla\Registry\Registry */

        // Get the model
        $model = $this->getModel();
        /** @var $model CrowdfundingFilesModelFiles */

        // Validate project owner.
        $validator = new Crowdfunding\Validator\Project\Owner(JFactory::getDbo(), $projectId, $userId);
        if (!$projectId or !$validator->isValid()) {

            $response
                ->setTitle(JText::_('COM_CROWDFUNDINGFILES_FAIL'))
                ->setText(JText::sprintf('COM_CROWDFUNDINGFILES_ERROR_INVALID_PROJECT_FILE_TOO_LARGE', $params->get("max_size")))
                ->failure();

            echo $response;
            JFactory::getApplication()->close();
        }

        $files = $this->input->files->get("files");
        if (!$files) {
            $response
                ->setTitle(JText::_('COM_CROWDFUNDINGFILES_FAIL'))
                ->setText(JText::_('COM_CROWDFUNDINGFILES_ERROR_FILE_CANT_BE_UPLOADED'))
                ->failure();

            echo $response;
            JFactory::getApplication()->close();
        }

        // Get the folder where the images will be stored
        $mediaUri = CrowdfundingFilesHelper::getMediaFolderUri($userId);

        // Get the folder where the images will be stored
        $destination = CrowdfundingFilesHelper::getMediaFolder($userId);
        if (!JFolder::exists($destination)) {
            $response
                ->setTitle(JText::_('COM_CROWDFUNDINGFILES_FAIL'))
                ->setText(JText::_('COM_CROWDFUNDINGFILES_ERROR_FILE_CANT_BE_UPLOADED'))
                ->failure();

            echo $response;
            JFactory::getApplication()->close();
        }

        $options = array(
            "legal_extensions"  => $params->get("legal_extensions"),
            "legal_types"       => $params->get("legal_types"),
            "max_size"          => (int)$params->get("max_size", 2),
            "destination"       => $destination
        );

        try {
            
            $files = $model->uploadFiles($files, $options);
            $files = $model->storeFiles($files, $projectId, $userId, $mediaUri);

        } catch (RuntimeException $e) {

            $response
                ->setTitle(JText::_('COM_CROWDFUNDINGFILES_FAIL'))
                ->setText($e->getMessage())
                ->failure();

            echo $response;
            JFactory::getApplication()->close();

        } catch (Exception $e) {

            JLog::add($e->getMessage());

            $response
                ->setTitle(JText::_('COM_CROWDFUNDINGFILES_FAIL'))
                ->setText(JText::_('COM_CROWDFUNDINGFILES_ERROR_SYSTEM'))
                ->failure();

            echo $response;
            JFactory::getApplication()->close();
        }

        $response
            ->setTitle(JText::_('COM_CROWDFUNDINGFILES_SUCCESS'))
            ->setText(JText::_('COM_CROWDFUNDINGFILES_FILES_UPLOADED'))
            ->setData($files)
            ->success();

        echo $response;
        JFactory::getApplication()->close();
    }


    /**
     * Delete a file.
     */
    public function remove()
    {
        // Create response object
        $response = new Prism\Response\Json();

        $userId = JFactory::getUser()->get("id");
        if (!$userId) {
            $response
                ->setTitle(JText::_('COM_CROWDFUNDINGFILES_FAIL'))
                ->setText(JText::_('COM_CROWDFUNDINGFILES_ERROR_NOT_LOG_IN'))
                ->failure();

            echo $response;
            JFactory::getApplication()->close();
        }

        // Get file ID.
        $fileId = $this->input->post->get("id");

        // Get the folder where the images are stored.
        $mediaFolder = CrowdfundingFilesHelper::getMediaFolder($userId);

        try {

            // Get the model
            $model = $this->getModel();
            /** @var $model CrowdfundingFilesModelFiles */

            $model->removeFile($fileId, $mediaFolder, $userId);

        } catch (RuntimeException $e) {

            $response
                ->setTitle(JText::_('COM_CROWDFUNDINGFILES_FAIL'))
                ->setText(JText::_('COM_CROWDFUNDINGFILES_ERROR_INVALID_PROJECT'))
                ->failure();

            echo $response;
            JFactory::getApplication()->close();

        } catch (Exception $e) {
            JLog::add($e->getMessage());
            throw new Exception($e->getMessage());
        }

        $response
            ->setTitle(JText::_('COM_CROWDFUNDINGFILES_SUCCESS'))
            ->setText(JText::_('COM_CROWDFUNDINGFILES_FILE_DELETED'))
            ->setData(array("file_id" => $fileId))
            ->success();

        echo $response;
        JFactory::getApplication()->close();
    }
}
