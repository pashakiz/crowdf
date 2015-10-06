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

class CrowdfundingModelRewards extends JModelLegacy
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
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @since    1.6
     */
    protected function populateState()
    {
        parent::populateState();

        $app = JFactory::getApplication("Site");
        /** @var $app JApplicationSite */

        // Get the pk of the record from the request.
        $value = $app->input->getInt("id");
        $this->setState($this->getName() . '.project_id', $value);

        // Load the parameters.
        $value = $app->getParams($this->option);
        $this->setState('params', $value);
    }

    public function getItems($projectId)
    {
        $db    = $this->getDbo();
        $query = $db->getQuery(true);

        $query
            ->select("a.id, a.amount, a.title, a.description, a.number, a.distributed, a.delivery, a.image_thumb")
            ->from($db->quoteName("#__crowdf_rewards", "a"))
            ->where("a.project_id = " . (int)$projectId)
            ->where("a.published = 1");

        $db->setQuery($query);

        return $db->loadAssocList();

    }

    public function validate($data)
    {
        if (empty($data) or !is_array($data)) {
            throw new InvalidArgumentException(JText::_("COM_CROWDFUNDING_ERROR_INVALID_REWARDS"));
        }

        $filter = JFilterInput::getInstance();

        $params = JComponentHelper::getParams("com_crowdfunding");
        /** @var  $params Joomla\Registry\Registry */

        // Create a currency object.
        $currency = Crowdfunding\Currency::getInstance(JFactory::getDbo(), $params->get("project_currency"));

        // Create the object "amount".
        $amount = new Crowdfunding\Amount($params);
        $amount->setCurrency($currency);

        foreach ($data as $key => $item) {

            $item["amount"] = $amount->setValue($item["amount"])->parse();

            // Filter data
            if (!is_numeric($item["amount"])) {
                $item["amount"] = 0;
            }

            $item["title"] = $filter->clean($item["title"], "string");
            $item["title"] = Joomla\String\String::trim($item["title"]);
            $item["title"] = Joomla\String\String::substr($item["title"], 0, 128);

            $item["description"] = $filter->clean($item["description"], "string");
            $item["description"] = Joomla\String\String::trim($item["description"]);
            $item["description"] = Joomla\String\String::substr($item["description"], 0, 500);

            $item["number"] = (int)$item["number"];

            $item["delivery"] = Joomla\String\String::trim($item["delivery"]);
            $item["delivery"] = $filter->clean($item["delivery"], "string");

            if (!empty($item["delivery"])) {
                $date     = new JDate($item["delivery"]);
                $unixTime = $date->toUnix();
                if ($unixTime < 0) {
                    $item["delivery"] = "";
                }
            }

            if (!$item["title"]) {
                throw new RuntimeException(JText::_("COM_CROWDFUNDING_ERROR_INVALID_TITLE"));
            }

            if (!$item["description"]) {
                throw new RuntimeException(JText::_("COM_CROWDFUNDING_ERROR_INVALID_DESCRIPTION"));
            }

            if (!$item["amount"]) {
                throw new RuntimeException(JText::_("COM_CROWDFUNDING_ERROR_INVALID_AMOUNT"));
            }

            $data[$key] = $item;
        }

        return $data;
    }

    /**
     * Method to save the form data.
     *
     * @param array $data
     * @param int $projectId
     *
     * @return    mixed        The record id on success, null on failure.
     *
     * @throws Exception
     *
     * @since    1.6
     */
    public function save($data, $projectId)
    {
        $ids = array();

        foreach ($data as $item) {

            // Load a record from the database
            $row    = $this->getTable();
            $itemId = Joomla\Utilities\ArrayHelper::getValue($item, "id", 0, "integer");

            if (!empty($itemId)) {
                $keys = array("id" => $itemId, "project_id" => $projectId);
                $row->load($keys);
                if (!$row->get("id")) {
                    throw new Exception(JText::_("COM_CROWDFUNDING_ERROR_INVALID_REWARD"));
                }
            }

            $amount      = Joomla\Utilities\ArrayHelper::getValue($item, "amount");
            $title       = Joomla\Utilities\ArrayHelper::getValue($item, "title");
            $description = Joomla\Utilities\ArrayHelper::getValue($item, "description");
            $number      = Joomla\Utilities\ArrayHelper::getValue($item, "number");
            $delivery    = Joomla\Utilities\ArrayHelper::getValue($item, "delivery");

            $row->set("amount", $amount);
            $row->set("title", $title);
            $row->set("description", $description);
            $row->set("number", $number);
            $row->set("delivery", $delivery);
            $row->set("project_id", $projectId);

            $row->store();

            $ids[] = $row->get("id");

        }

        return $ids;
    }

    public function remove($rewardId, $imagesFolder)
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

        $table->delete();
    }

    /**
     * Upload images.
     *
     * @param  array $files
     * @param  string $destFolder
     * @param  array $rewardsIds
     *
     * @return array
     */
    public function uploadImages($files, $destFolder, $rewardsIds)
    {
        // Load parameters.
        $params = JComponentHelper::getParams($this->option);
        /** @var  $params Joomla\Registry\Registry */

        // Joomla! media extension parameters
        $mediaParams = JComponentHelper::getParams("com_media");
        /** @var  $mediaParams Joomla\Registry\Registry */

        $KB = 1024 * 1024;

        $uploadMaxSize   = $mediaParams->get("upload_maxsize") * $KB;
        $mimeTypes       = explode(",", $mediaParams->get("upload_mime"));
        $imageExtensions = explode(",", $mediaParams->get("image_extensions"));

        $images = array();

        foreach ($files as $rewardId => $image) {

            // If the image is set to not valid reward, continue to next one.
            // It is impossible to store image to missed reward.
            if (!in_array($rewardId, $rewardsIds)) {
                continue;
            }

            $uploadedFile = Joomla\Utilities\ArrayHelper::getValue($image, 'tmp_name');
            $uploadedName = Joomla\String\String::trim(Joomla\Utilities\ArrayHelper::getValue($image, 'name'));
            $errorCode    = Joomla\Utilities\ArrayHelper::getValue($image, 'error');

            $file = new Prism\File\Image();

            if (!empty($uploadedName)) {
                // Prepare size validator.
                $fileSize = (int)Joomla\Utilities\ArrayHelper::getValue($image, 'size');

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
                    continue;
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
                    continue;
                }

                // Generate thumbnails.

                // Create thumbnail.
                $generatedName->generateRandomString(12, "reward_thumb_");
                $options     = array(
                    "width"       => $params->get("rewards_image_thumb_width", 200),
                    "height"      => $params->get("rewards_image_thumb_height", 200),
                    "scale"       => $params->get("image_resizing_scale", JImage::SCALE_INSIDE),
                    "destination" => JPath::clean($destFolder . DIRECTORY_SEPARATOR . $generatedName . "." . $ext)
                );
                $thumbSource = $file->createThumbnail($options);

                // Create square image.
                $generatedName->generateRandomString(12, "reward_square_");
                $options      = array(
                    "width"       => $params->get("rewards_image_square_width", 50),
                    "height"      => $params->get("rewards_image_square_height", 50),
                    "scale"       => $params->get("image_resizing_scale", JImage::SCALE_INSIDE),
                    "destination" => JPath::clean($destFolder . DIRECTORY_SEPARATOR . $generatedName . "." . $ext)
                );
                $squareSource = $file->createThumbnail($options);

                $names           = array("image" => "", "thumb" => "", "square" => "");
                $names['image']  = basename($imageSource);
                $names["thumb"]  = basename($thumbSource);
                $names["square"] = basename($squareSource);

                $images[$rewardId] = $names;
            }
        }

        return $images;
    }

    /**
     * Save reward images to the reward.
     *
     * @param array $images
     * @param string $imagesFolder
     *
     * @throws InvalidArgumentException
     */
    public function storeImages($images, $imagesFolder)
    {
        if (!$images or !is_array($images)) {
            throw new InvalidArgumentException(JText::_("COM_CROWDFUNDING_ERROR_INVALID_IMAGES"));
        }

        foreach ($images as $rewardId => $pictures) {

            // Get reward row.
            /** @var $table CrowdfundingTableReward */
            $table = $this->getTable();
            $table->load($rewardId);

            if (!$table->get("id")) {
                continue;
            }

            // Delete old reward image ( image, thumb and square ) from the filesystem.
            $this->deleteImages($table, $imagesFolder);

            // Store the new one.
            $image  = Joomla\Utilities\ArrayHelper::getValue($pictures, "image");
            $thumb  = Joomla\Utilities\ArrayHelper::getValue($pictures, "thumb");
            $square = Joomla\Utilities\ArrayHelper::getValue($pictures, "square");

            $table->set("image", $image);
            $table->set("image_thumb", $thumb);
            $table->set("image_square", $square);

            $table->store();
        }
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
}
