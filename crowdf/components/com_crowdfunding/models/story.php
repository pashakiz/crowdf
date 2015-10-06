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

JLoader::register("CrowdfundingModelProject", CROWDFUNDING_PATH_COMPONENT_SITE . "/models/project.php");

class CrowdfundingModelStory extends CrowdfundingModelProject
{
    /**
     * Method to get the profile form.
     *
     * The base form is loaded from XML and then an event is fired
     * for users plugins to extend the form with extra fields.
     *
     * @param    array   $data     An optional array of data for the form to interogate.
     * @param    boolean $loadData True if the form is to load its own data (default case), false if not.
     *
     * @return    JForm    A JForm object on success, false on failure
     * @since    1.6
     */
    public function getForm($data = array(), $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm($this->option . '.story', 'story', array('control' => 'jform', 'load_data' => $loadData));
        if (empty($form)) {
            return false;
        }

        return $form;
    }

    /**
     * Method to get the data that should be injected in the form.
     *
     * @return    mixed    The data for the form.
     * @since    1.6
     */
    protected function loadFormData()
    {
        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        $data = $app->getUserState($this->option . '.edit.story.data', array());
        if (!$data) {

            $itemId = (int)$this->getState($this->getName() . '.id');
            $userId = JFactory::getUser()->get("id");

            $data = $this->getItem($itemId, $userId);

        }

        return $data;
    }

    /**
     * Method to save the form data.
     *
     * @param    array    $data    The form data.
     *
     * @return    mixed        The record id on success, null on failure.
     * @since    1.6
     */
    public function save($data)
    {
        $id          = Joomla\Utilities\ArrayHelper::getValue($data, "id");
        $description = Joomla\Utilities\ArrayHelper::getValue($data, "description");

        $keys = array(
            "id" => $id,
            "user_id" => JFactory::getUser()->get("id"),
        );

        // Load a record from the database.
        /** @var $row CrowdfundingTableProject */
        $row = $this->getTable();
        $row->load($keys);

        $row->set("description", $description);

        $this->prepareTable($row, $data);

        $row->store();

        // Trigger the event onContentAfterSave.
        $this->triggerEventAfterSave($row, "story");

        return $row->get("id");
    }

    protected function prepareTable(&$table, $data)
    {
        // Prepare the video
        $pitchVideo = Joomla\Utilities\ArrayHelper::getValue($data, "pitch_video");
        $table->set("pitch_video", $pitchVideo);

        // Prepare the image.
        if (!empty($data["pitch_image"])) {

            // Delete old image if I upload a new one.
            if (!empty($table->pitch_image)) {

                $params       = JComponentHelper::getParams($this->option);
                /** @var  $params Joomla\Registry\Registry */

                $imagesFolder = $params->get("images_directory", "images/crowdfunding");

                // Remove an image from the filesystem
                $pitchImage = JPAth::clean($imagesFolder . DIRECTORY_SEPARATOR . $table->pitch_image);

                if (is_file($pitchImage)) {
                    JFile::delete($pitchImage);
                }
            }

            $table->set("pitch_image", $data["pitch_image"]);
        }
    }


    /**
     * Upload an image
     *
     * @param  array $image
     * @param  string $destination
     *
     * @throws Exception
     * @return array
     */
    public function uploadImage($image, $destination)
    {
        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        $uploadedFile = Joomla\Utilities\ArrayHelper::getValue($image, 'tmp_name');
        $uploadedName = Joomla\Utilities\ArrayHelper::getValue($image, 'name');
        $errorCode    = Joomla\Utilities\ArrayHelper::getValue($image, 'error');

        // Load parameters.
        $params     = JComponentHelper::getParams($this->option);
        /** @var  $params Joomla\Registry\Registry */

        $tmpFolder = $app->get("tmp_path");

        // Joomla! media extension parameters
        $mediaParams = JComponentHelper::getParams("com_media");
        /** @var  $mediaParams Joomla\Registry\Registry */

        $file = new Prism\File\File();

        // Prepare size validator.
        $KB            = 1024 * 1024;
        $fileSize      = (int)$app->input->server->get('CONTENT_LENGTH');
        $uploadMaxSize = $mediaParams->get("upload_maxsize") * $KB;

        $sizeValidator = new Prism\File\Validator\Size($fileSize, $uploadMaxSize);

        // Prepare server validator.
        $serverValidator = new Prism\File\Validator\Server($errorCode, array(UPLOAD_ERR_NO_FILE));

        // Prepare image validator.
        $imageValidator = new Prism\File\Validator\Image($uploadedFile, $uploadedName);

        // Get allowed mime types from media manager options
        $mimeTypes = explode(",", $mediaParams->get("upload_mime"));
        $imageValidator->setMimeTypes($mimeTypes);

        // Get allowed image extensions from media manager options
        $imageExtensions = explode(",", $mediaParams->get("image_extensions"));
        $imageValidator->setImageExtensions($imageExtensions);

        $file
            ->addValidator($sizeValidator)
            ->addValidator($imageValidator)
            ->addValidator($serverValidator);

        // Validate the file
        if (!$file->isValid()) {
            throw new RuntimeException($file->getError());
        }

        // Generate temporary file name
        $ext = Joomla\String\String::strtolower(JFile::makeSafe(JFile::getExt($image['name'])));

        $generatedName = new Prism\String();
        $generatedName->generateRandomString(32);

        $tmpDestFile = JPath::clean($tmpFolder . DIRECTORY_SEPARATOR . $generatedName . "." . $ext);

        // Prepare uploader object.
        $uploader = new Prism\File\Uploader\Local($uploadedFile);
        $uploader->setDestination($tmpDestFile);

        // Upload temporary file
        $file->setUploader($uploader);

        $file->upload();

        // Get file
        $tmpDestFile = $file->getFile();

        if (!is_file($tmpDestFile)) {
            throw new Exception('COM_CROWDFUNDING_ERROR_FILE_CANT_BE_UPLOADED');
        }

        // Resize image
        $image = new JImage();
        $image->loadFile($tmpDestFile);
        if (!$image->isLoaded()) {
            throw new Exception(JText::sprintf('COM_CROWDFUNDING_ERROR_FILE_NOT_FOUND', $tmpDestFile));
        }

        $imageName = $generatedName . "_pimage.png";
        $imageFile = $destination . DIRECTORY_SEPARATOR . $imageName;

        // Get the scale method.
        $scaleMethod = $params->get("image_resizing_scale", JImage::SCALE_INSIDE);

        // Create main image
        $width  = $params->get("pitch_image_width", 600);
        $height = $params->get("pitch_image_height", 400);
        $image->resize($width, $height, false, $scaleMethod);
        $image->toFile($imageFile, IMAGETYPE_PNG);

        // Remove the temporary file.
        if (is_file($tmpDestFile)) {
            JFile::delete($tmpDestFile);
        }

        return $imageName;
    }

    /**
     * Delete pitch image.
     *
     * @param integer $id Item id
     * @param integer $userId User id
     */
    public function removeImage($id, $userId)
    {
        $keys = array(
            "id" => $id,
            "user_id" => $userId
        );

        // Load category data
        $row = $this->getTable();
        $row->load($keys);

        // Delete old image if I upload the new one
        if ($row->get("pitch_image")) {
            jimport('joomla.filesystem.file');

            $params       = JComponentHelper::getParams($this->option);
            /** @var  $params Joomla\Registry\Registry */

            $imagesFolder = $params->get("images_directory", "images/crowdfunding");

            // Remove an image from the filesystem
            $pitchImage = JPath::clean($imagesFolder . DIRECTORY_SEPARATOR . $row->get("pitch_image"));

            if (is_file($pitchImage)) {
                JFile::delete($pitchImage);
            }
        }

        $row->set("pitch_image", "");
        $row->store();
    }

}
