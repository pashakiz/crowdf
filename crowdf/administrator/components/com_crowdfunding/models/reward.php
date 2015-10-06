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

class CrowdfundingModelReward extends JModelAdmin
{
    /**
     * Returns a reference to the a Table object, always creating it.
     *
     * @param   string $type    The table type to instantiate
     * @param   string $prefix A prefix for the table class name. Optional.
     * @param   array  $config Configuration array for model. Optional.
     *
     * @return  JTable  A database object
     * @since   1.6
     */
    public function getTable($type = 'Reward', $prefix = 'CrowdfundingTable', $config = array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }

    /**
     * Method to get the record form.
     *
     * @param   array   $data     An optional array of data for the form to interogate.
     * @param   boolean $loadData True if the form is to load its own data (default case), false if not.
     *
     * @return  JForm   A JForm object on success, false on failure
     * @since   1.6
     */
    public function getForm($data = array(), $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm($this->option . '.reward', 'reward', array('control' => 'jform', 'load_data' => $loadData));
        if (empty($form)) {
            return false;
        }

        return $form;
    }

    /**
     * Method to get the data that should be injected in the form.
     *
     * @return  mixed   The data for the form.
     * @since   1.6
     */
    protected function loadFormData()
    {
        // Check the session for previously entered form data.
        $data = JFactory::getApplication()->getUserState($this->option . '.edit.reward.data', array());
        if (empty($data)) {
            $data = $this->getItem();

            // Set project ID to form data, if it is a new record.
            if (empty($data->id)) {
                $app = JFactory::getApplication();
                /** @var  $app JApplicationAdministrator */

                $data->project_id = $app->getUserState("com_crowdfunding.rewards.pid");
            }

        }

        return $data;
    }

    /**
     * Save data into the DB
     *
     * @param array $data   The data about item
     *
     * @return   int  Item ID
     */
    public function save($data)
    {
        $id          = JArrayHelper::getValue($data, "id");
        $title       = JArrayHelper::getValue($data, "title");
        $description = JArrayHelper::getValue($data, "description");
        $amount      = JArrayHelper::getValue($data, "amount");
        $number      = JArrayHelper::getValue($data, "number");
        $distributed = JArrayHelper::getValue($data, "distributed");
        $delivery    = JArrayHelper::getValue($data, "delivery");
        $shipping    = JArrayHelper::getValue($data, "shipping");
        $published   = JArrayHelper::getValue($data, "published");
        $projectId   = JArrayHelper::getValue($data, "project_id");

        // Load a record from the database
        $row = $this->getTable();
        $row->load($id);

        $row->set("title", $title);
        $row->set("description", $description);
        $row->set("amount", $amount);
        $row->set("number", $number);
        $row->set("distributed", $distributed);
        $row->set("delivery", $delivery);
        $row->set("shipping", $shipping);
        $row->set("published", $published);
        $row->set("project_id", $projectId);


        $row->store();

        return $row->get("id");
    }

    /**
     * Upload an image.
     *
     * @param  array $image
     * @param  string $destFolder
     *
     * @throws RuntimeException
     *
     * @return array
     */
    public function uploadImage($image, $destFolder)
    {
        // Load parameters.
        $params = JComponentHelper::getParams($this->option);
        /** @var  $params Joomla\Registry\Registry */

        // Joomla! media extension parameters
        $mediaParams = JComponentHelper::getParams("com_media");
        /** @var  $mediaParams Joomla\Registry\Registry */

        $names           = array("image" => "", "thumb" => "", "square" => "");

        $KB = 1024 * 1024;

        $uploadMaxSize   = $mediaParams->get("upload_maxsize") * $KB;
        $mimeTypes       = explode(",", $mediaParams->get("upload_mime"));
        $imageExtensions = explode(",", $mediaParams->get("image_extensions"));

        $uploadedFile = JArrayHelper::getValue($image, 'tmp_name');
        $uploadedName = Joomla\String\String::trim(JArrayHelper::getValue($image, 'name'));
        $errorCode    = JArrayHelper::getValue($image, 'error');

        $file = new Prism\File\Image();

        if (!empty($uploadedName)) {
            // Prepare size validator.
            $fileSize = (int)JArrayHelper::getValue($image, 'size');

            // Prepare file size validator.
            $sizeValidator = new Prism\File\Validator\Size($fileSize, $uploadMaxSize);

            // Prepare server validator.
            $serverValidator = new Prism\File\Validator\Server($errorCode, array(UPLOAD_ERR_NO_FILE));

            // Prepare image validator.
            $imageValidator = new Prism\File\Validator\Image($uploadedFile, $uploadedName);

            // Get allowed mime types from media manager options
            $imageValidator->setMimeTypes($mimeTypes);

            // Get allowed image extensions from media manager options
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
            $generatedName->generateRandomString(12, "reward_");

            $destFile = JPath::clean($destFolder . DIRECTORY_SEPARATOR . $generatedName . "." . $ext);

            // Prepare uploader object.
            $uploader = new Prism\File\Uploader\Local($uploadedFile);
            $uploader->setDestination($destFile);

            // Upload temporary file
            $file->setUploader($uploader);

            $file->upload();

            // Get file
            $imageSource = $file->getFile();
            if (!is_file($imageSource)) {
                throw new RuntimeException(JText::_("COM_CROWDFUNDING_ERROR_FILE_CANT_BE_UPLOADED"));
            }

            // Generate thumbnails.

            // Create thumbnail.
            $generatedName->generateRandomString(12, "reward_thumb_");
            $options     = array(
                "width"       => $params->get("rewards_image_thumb_width", 200),
                "height"      => $params->get("rewards_image_thumb_height", 200),
                "destination" => JPath::clean($destFolder . DIRECTORY_SEPARATOR . $generatedName . "." . $ext)
            );
            $thumbSource = $file->createThumbnail($options);

            // Create square image.
            $generatedName->generateRandomString(12, "reward_square_");
            $options      = array(
                "width"       => $params->get("rewards_image_square_width", 50),
                "height"      => $params->get("rewards_image_square_height", 50),
                "destination" => JPath::clean($destFolder . DIRECTORY_SEPARATOR . $generatedName . "." . $ext)
            );
            $squareSource = $file->createThumbnail($options);

            $names['image']  = basename($imageSource);
            $names["thumb"]  = basename($thumbSource);
            $names["square"] = basename($squareSource);

        }

        return $names;
    }

    /**
     * Save reward images to the reward.
     *
     * @param array $images
     * @param string $imagesFolder
     * @param int $rewardId
     *
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function storeImage($images, $imagesFolder, $rewardId)
    {
        if (!$images or !is_array($images)) {
            throw new InvalidArgumentException(JText::_("COM_CROWDFUNDING_ERROR_INVALID_IMAGES"));
        }

        // Get reward row.
        /** @var $table CrowdfundingTableReward */
        $table = $this->getTable();
        $table->load($rewardId);

        if (!$table->get("id")) {
            throw new RuntimeException(JText::_("COM_CROWDFUNDING_ERROR_INVALID_REWARD"));
        }

        // Delete old reward image ( image, thumb and square ) from the filesystem.
        $this->deleteImages($table, $imagesFolder);

        // Store the new one.
        $image  = JArrayHelper::getValue($images, "image");
        $thumb  = JArrayHelper::getValue($images, "thumb");
        $square = JArrayHelper::getValue($images, "square");

        $table->set("image", $image);
        $table->set("image_thumb", $thumb);
        $table->set("image_square", $square);

        $table->store();
    }

    public function removeImage($rewardId, $imagesFolder)
    {
        // Get reward row.
        /** @var $table CrowdfundingTableReward */
        $table = $this->getTable();
        $table->load($rewardId);

        if (!$table->get("id")) {
            throw new RuntimeException(JText::_("COM_CROWDFUNDING_ERROR_INVALID_REWARD"));
        }

        // Delete the images from filesystem.
        $this->deleteImages($table, $imagesFolder);

        $table->set("image", null);
        $table->set("image_thumb", null);
        $table->set("image_square", null);

        $table->store(true);
    }

    /**
     * Remove images from the filesystem.
     *
     * @param CrowdfundingTableReward $table
     * @param string $imagesFolder
     */
    protected function deleteImages(&$table, $imagesFolder)
    {
        // Remove image.
        if ($table->get("image")) {
            $fileSource = $imagesFolder . DIRECTORY_SEPARATOR . $table->get("image");
            if (JFile::exists($fileSource)) {
                JFile::delete($fileSource);
            }
        }

        // Remove thumbnail.
        if ($table->get("image_thumb")) {
            $fileSource = $imagesFolder . DIRECTORY_SEPARATOR . $table->get("image_thumb");
            if (JFile::exists($fileSource)) {
                JFile::delete($fileSource);
            }
        }

        // Remove square image.
        if ($table->get("image_square")) {
            $fileSource = $imagesFolder . DIRECTORY_SEPARATOR . $table->get("image_square");
            if (JFile::exists($fileSource)) {
                JFile::delete($fileSource);
            }
        }
    }

    public function updateRewardState($transactionId, $state)
    {
        $state  = (!$state) ? 0 : 1;

        $db     = $this->getDbo();

        $query  = $db->getQuery(true);

        $query
            ->update($db->quoteName("#__crowdf_transactions"))
            ->set($db->quoteName("reward_state") . " = " . (int)$state)
            ->where($db->quoteName("id") . " = " . (int)$transactionId);

        $db->setQuery($query);
        $db->execute();
    }
}
