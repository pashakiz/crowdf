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

/**
 * Crowdfunding project controller.
 *
 * @package     Crowdfunding
 * @subpackage  Components
 */
class CrowdfundingControllerProject extends JControllerLegacy
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
    public function getModel($name = 'Project', $prefix = 'CrowdfundingModel', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);

        return $model;
    }

    /**
     * Method to save the submitted ordering values for records via AJAX.
     *
     * @throws Exception
     * @return  void
     */
    public function loadLocation()
    {
        // Get the input
        $query = $this->input->get->get('query', "", 'string');

        $response = new Prism\Response\Json();

        try {

            $locations = new Crowdfunding\Locations(JFactory::getDbo());
            $locations->loadByString($query);

            $locationData = $locations->toOptions();

        } catch (Exception $e) {
            JLog::add($e->getMessage());
            throw new Exception(JText::_('COM_CROWDFUNDING_ERROR_SYSTEM'));
        }

        $response
            ->setData($locationData)
            ->success();

        echo $response;
        JFactory::getApplication()->close();
    }

    /**
     * Method to save the submitted ordering values for records via AJAX.
     *
     * @throws Exception
     * @return  void
     */
    public function loadProject()
    {
        // Get the input
        $query = $this->input->get->get('query', "", 'string');

        $response = new Prism\Response\Json();

        try {

            $options = array(
                "published" => Prism\Constants::PUBLISHED,
                "approved"  => Prism\Constants::APPROVED,
            );

            $projects = new Crowdfunding\Projects(JFactory::getDbo());
            $projects->loadByString($query, $options);

            $projectData = $projects->toOptions();

        } catch (Exception $e) {
            JLog::add($e->getMessage());
            throw new Exception(JText::_('COM_CROWDFUNDING_ERROR_SYSTEM'));
        }

        $response
            ->setData($projectData)
            ->success();

        echo $response;
        JFactory::getApplication()->close();
    }

    public function uploadImage()
    {
        // Check for request forgeries.
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        $response = new Prism\Response\Json();

        $userId = JFactory::getUser()->get("id");
        if (!$userId) {
            $response
                ->setTitle(JText::_('COM_CROWDFUNDING_FAIL'))
                ->setText(JText::_('COM_CROWDFUNDING_ERROR_NOT_LOG_IN'))
                ->failure();

            echo $response;
            $app->close();
        }

        // Get the model
        $model = $this->getModel();
        /** @var $model CrowdfundingModelProject */

        $projectId = $this->input->post->get("id");

        // Validate project owner.
        if (!empty($projectId)) {
            $validator = new Crowdfunding\Validator\Project\Owner(JFactory::getDbo(), $projectId, $userId);
            if (!$validator->isValid()) {

                $response
                    ->setTitle(JText::_('COM_CROWDFUNDING_FAIL'))
                    ->setText(JText::_('COM_CROWDFUNDING_ERROR_INVALID_PROJECT'))
                    ->failure();

                echo $response;
                $app->close();
            }
        }

        $file = $this->input->files->get("project_image");
        if (!$file) {
            $response
                ->setTitle(JText::_('COM_CROWDFUNDING_FAIL'))
                ->setText(JText::_('COM_CROWDFUNDING_ERROR_FILE_CANT_BE_UPLOADED'))
                ->failure();

            echo $response;
            $app->close();
        }

        $temporaryUrl = "";

        try {

            // Get the folder where the images will be stored
            $temporaryFolder = CrowdfundingHelper::getTemporaryImagesFolder();

            $image      = $model->uploadImage($file, $temporaryFolder);
            $imageName  = basename($image);

            // Prepare URL to temporary image.
            $temporaryUrl = JUri::base(). CrowdfundingHelper::getTemporaryImagesFolderUri() . "/". $imageName;

            // Remove an old image if it exists.
            $oldImage = $app->getUserState(Crowdfunding\Constants::TEMPORARY_IMAGE_CONTEXT);
            if (!empty($oldImage)) {
                $oldImage = JPath::clean($temporaryFolder . "/" . basename($oldImage));
                if (JFile::exists($oldImage)) {
                    JFile::delete($oldImage);
                }
            }

            // Set the name of the image in the session.
            $app->setUserState(Crowdfunding\Constants::TEMPORARY_IMAGE_CONTEXT, $imageName);

        } catch (InvalidArgumentException $e) {

            $response
                ->setTitle(JText::_('COM_CROWDFUNDING_FAIL'))
                ->setText($e->getMessage())
                ->failure();

            echo $response;
            $app->close();

        } catch (RuntimeException $e) {

            $response
                ->setTitle(JText::_('COM_CROWDFUNDING_FAIL'))
                ->setText($e->getMessage())
                ->failure();

            echo $response;
            $app->close();

        } catch (Exception $e) {

            $response
                ->setTitle(JText::_('COM_CROWDFUNDING_FAIL'))
                ->setText(JText::_('COM_CROWDFUNDING_ERROR_SYSTEM'))
                ->failure();

            echo $response;
            $app->close();
        }

        $response
            ->setTitle(JText::_('COM_CROWDFUNDING_SUCCESS'))
            ->setText(JText::_('COM_CROWDFUNDING_IMAGE_SAVED'))
            ->setData($temporaryUrl)
            ->success();

        echo $response;
        $app->close();
    }

    public function cropImage()
    {
        // Check for request forgeries.
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        $response = new Prism\Response\Json();

        $userId = JFactory::getUser()->get("id");
        if (!$userId) {
            $response
                ->setTitle(JText::_('COM_CROWDFUNDING_FAIL'))
                ->setText(JText::_('COM_CROWDFUNDING_ERROR_NOT_LOG_IN'))
                ->failure();

            echo $response;
            $app->close();
        }

        // Get the model
        $model = $this->getModel();
        /** @var $model CrowdfundingModelProject */

        $projectId = $this->input->post->get("id");

        // If there is a project, validate the owner.
        if (!empty($projectId)) {

            // Validate project owner.
            $validator = new Crowdfunding\Validator\Project\Owner(JFactory::getDbo(), $projectId, $userId);
            if (!$validator->isValid()) {

                $response
                    ->setTitle(JText::_('COM_CROWDFUNDING_FAIL'))
                    ->setText(JText::_('COM_CROWDFUNDING_ERROR_INVALID_PROJECT'))
                    ->failure();

                echo $response;
                $app->close();
            }

        }

        // Get the filename from the session.
        $fileName = basename($app->getUserState(Crowdfunding\Constants::TEMPORARY_IMAGE_CONTEXT));
        $temporaryFile = JPath::clean(CrowdfundingHelper::getTemporaryImagesFolder() ."/". $fileName);

        if (!$fileName or !JFile::exists($temporaryFile)) {
            $response
                ->setTitle(JText::_('COM_CROWDFUNDING_FAIL'))
                ->setText(JText::_('COM_CROWDFUNDING_ERROR_FILE_DOES_NOT_EXIST'))
                ->failure();

            echo $response;
            $app->close();
        }

        $imageUrl = "";

        try {

            // Get the folder where the images will be stored
            $destination = CrowdfundingHelper::getTemporaryImagesFolder();

            $params = JComponentHelper::getParams("com_crowdfunding");

            $options = array(
                "width"    => $this->input->getFloat("width"),
                "height"   => $this->input->getFloat("height"),
                "x"        => $this->input->getFloat("x"),
                "y"        => $this->input->getFloat("y"),
                "destination"  => $destination,
                "resize_width" => $params->get("image_width", 200),
                "resize_height" => $params->get("image_height", 200)
            );

            // Resize the picture.
            $images     = $model->cropImage($temporaryFile, $options);
            $imageName  = basename(Joomla\Utilities\ArrayHelper::getValue($images, "image"));

            // Remove the temporary images if they exist.
            $temporaryImages = $app->getUserState(Crowdfunding\Constants::CROPPED_IMAGES_CONTEXT);
            if (!empty($temporaryImages)) {
                $model->removeTemporaryImages($temporaryImages, $destination);
            }

            // If there is a project, store the images to database.
            // If there is NO project, store the images in the session.
            if (!empty($projectId)) {
                $model->updateImages($projectId, $images, $destination);
                $app->setUserState(Crowdfunding\Constants::CROPPED_IMAGES_CONTEXT, null);

                // Get the folder of the images where the pictures will be stored.
                $imageUrl = JUri::base() . CrowdfundingHelper::getImagesFolderUri() ."/". $imageName;
            } else {
                $app->setUserState(Crowdfunding\Constants::CROPPED_IMAGES_CONTEXT, $images);

                // Get the temporary folder where the images will be stored.
                $imageUrl = JUri::base() . CrowdfundingHelper::getTemporaryImagesFolderUri() ."/". $imageName;
            }

        } catch (RuntimeException $e) {

            $response
                ->setTitle(JText::_('COM_CROWDFUNDING_FAIL'))
                ->setText($e->getMessage())
                ->failure();

            echo $response;
            $app->close();

        } catch (Exception $e) {

            $response
                ->setTitle(JText::_('COM_CROWDFUNDING_FAIL'))
                ->setText(JText::_('COM_CROWDFUNDING_ERROR_SYSTEM'))
                ->failure();

            echo $response;
            $app->close();
        }

        $response
            ->setTitle(JText::_('COM_CROWDFUNDING_SUCCESS'))
            ->setText(JText::_('COM_CROWDFUNDING_IMAGE_SAVED'))
            ->setData($imageUrl)
            ->success();

        echo $response;
        $app->close();
    }

    public function cancelImageCrop()
    {
        // Check for request forgeries.
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        $response = new Prism\Response\Json();

        $userId = JFactory::getUser()->get("id");
        if (!$userId) {
            $response
                ->setTitle(JText::_('COM_CROWDFUNDING_FAIL'))
                ->setText(JText::_('COM_CROWDFUNDING_ERROR_NOT_LOG_IN'))
                ->failure();

            echo $response;
            $app->close();
        }

        try {

            // Get the folder where the images will be stored
            $temporaryFolder = CrowdfundingHelper::getTemporaryImagesFolder();

            // Remove old image if it exists.
            $oldImage = $app->getUserState(Crowdfunding\Constants::TEMPORARY_IMAGE_CONTEXT);
            if (!empty($oldImage)) {
                $oldImage = JPath::clean($temporaryFolder . "/" . basename($oldImage));
                if (JFile::exists($oldImage)) {
                    JFile::delete($oldImage);
                }
            }

            // Set the name of the image in the session.
            $app->setUserState(Crowdfunding\Constants::TEMPORARY_IMAGE_CONTEXT, null);

        } catch (Exception $e) {

            $response
                ->setTitle(JText::_('COM_CROWDFUNDING_FAIL'))
                ->setText(JText::_('COM_CROWDFUNDING_ERROR_SYSTEM'))
                ->failure();

            echo $response;
            $app->close();
        }

        $response
            ->setTitle(JText::_('COM_CROWDFUNDING_SUCCESS'))
            ->setText(JText::_('COM_CROWDFUNDING_IMAGE_RESET_SUCCESSFULLY'))
            ->success();

        echo $response;
        $app->close();
    }

    /**
     * Method to follow a project.
     *
     * @throws Exception
     * @return  void
     */
    public function follow()
    {
        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        $response = new Prism\Response\Json();

        $userId  = JFactory::getUser()->get("id");

        if (!$userId) {
            $response
                ->setTitle(JText::_('COM_CROWDFUNDING_FAIL'))
                ->setText(JText::_('COM_CROWDFUNDING_ERROR_INVALID_USER'))
                ->failure();

            echo $response;
            $app->close();
        }

        // Get project ID.
        $projectId  = $this->input->post->getInt('pid', 0);

        if (!$projectId) {
            $response
                ->setTitle(JText::_('COM_CROWDFUNDING_FAIL'))
                ->setText(JText::_('COM_CROWDFUNDING_ERROR_INVALID_PROJECT'))
                ->failure();

            echo $response;
            $app->close();
        }

        $state = $this->input->post->getInt('state', 0);
        $state = (!$state) ? Prism\Constants::UNFOLLOWED : Prism\Constants::FOLLOWED;

        try {

            $user = new Crowdfunding\User\User(JFactory::getDbo());
            $user->setId($userId);

            if (!$state) {
                $user->unfollow($projectId);
            } else {
                $user->follow($projectId);
            }

        } catch (Exception $e) {
            JLog::add($e->getMessage());
            throw new Exception(JText::_('COM_CROWDFUNDING_ERROR_SYSTEM'));
        }

        $responseData = array(
            "state" => $state
        );

        $response
            ->setTitle(JText::_('COM_CROWDFUNDING_SUCCESS'))
            ->setData($responseData)
            ->success();

        echo $response;
        $app->close();
    }
}
