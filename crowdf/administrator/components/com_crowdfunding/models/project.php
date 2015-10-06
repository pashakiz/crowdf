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

class CrowdfundingModelProject extends JModelAdmin
{
    /**
     * Returns a reference to the a Table object, always creating it.
     *
     * @param   string $type   The table type to instantiate
     * @param   string $prefix A prefix for the table class name. Optional.
     * @param   array  $config Configuration array for model. Optional.
     *
     * @return  JTable  A database object
     * @since   1.6
     */
    public function getTable($type = 'Project', $prefix = 'CrowdfundingTable', $config = array())
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
        $form = $this->loadForm($this->option . '.project', 'project', array('control' => 'jform', 'load_data' => $loadData));
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
        $data = JFactory::getApplication()->getUserState($this->option . '.edit.project.data', array());
        if (empty($data)) {
            $data = $this->getItem();

            if (!empty($data->location_id)) {

                // Load location from database.
                $location = new Crowdfunding\Location(JFactory::getDbo());
                $location->load($data->location_id);
                $locationName = $location->getName(true);

                // Set the name to the form element.
                if (!empty($locationName)) {
                    $data->location_preview = $locationName;
                }
            }
        }

        return $data;
    }

    /**
     * Save data into the DB
     *
     * @param array $data The data about item
     *
     * @return  int    Item ID
     */
    public function save($data)
    {
        $id          = Joomla\Utilities\ArrayHelper::getValue($data, "id", 0, "int");
        $title       = Joomla\Utilities\ArrayHelper::getValue($data, "title");
        $alias       = Joomla\Utilities\ArrayHelper::getValue($data, "alias");
        $catId       = Joomla\Utilities\ArrayHelper::getValue($data, "catid", 0, "int");
        $typeId      = Joomla\Utilities\ArrayHelper::getValue($data, "type_id", 0, "int");
        $userId      = Joomla\Utilities\ArrayHelper::getValue($data, "user_id", 0, "int");
        $locationId  = Joomla\Utilities\ArrayHelper::getValue($data, "location_id");
        $published   = Joomla\Utilities\ArrayHelper::getValue($data, "published", 0, "int");
        $approved    = Joomla\Utilities\ArrayHelper::getValue($data, "approved", 0, "int");
        $shortDesc   = Joomla\Utilities\ArrayHelper::getValue($data, "short_desc");
        $created     = Joomla\Utilities\ArrayHelper::getValue($data, "created");

        $goal        = Joomla\Utilities\ArrayHelper::getValue($data, "goal");
        $funded      = Joomla\Utilities\ArrayHelper::getValue($data, "funded");
        $fundingType = Joomla\Utilities\ArrayHelper::getValue($data, "funding_type");

        $pitchVideo  = Joomla\Utilities\ArrayHelper::getValue($data, "pitch_video");
        $description = Joomla\Utilities\ArrayHelper::getValue($data, "description");

        // Load a record from the database
        $row = $this->getTable();
        $row->load($id);

        $row->set("title", $title);
        $row->set("alias", $alias);
        $row->set("catid", $catId);
        $row->set("type_id", $typeId);
        $row->set("user_id", $userId);
        $row->set("location_id", $locationId);
        $row->set("published", $published);
        $row->set("approved", $approved);
        $row->set("short_desc", $shortDesc);
        $row->set("created", $created);

        $row->set("goal", $goal);
        $row->set("funded", $funded);
        $row->set("funding_type", $fundingType);

        $row->set("pitch_video", $pitchVideo);
        $row->set("description", $description);

        $this->prepareTableData($row, $data);

        $row->store();

        return $row->get("id");

    }

    /**
     * Prepare project images before saving.
     *
     * @param   object $table
     * @param   array  $data
     *
     * @throws Exception
     *
     * @since    1.6
     */
    protected function prepareTableData($table, $data)
    {
        // Set order value
        if (!$table->get("id") and !$table->get("ordering")) {

            $db    = $this->getDbo();
            $query = $db->getQuery(true);

            $query
                ->select("MAX(ordering)")
                ->from($db->quoteName("#__crowdf_projects"));

            $db->setQuery($query, 0, 1);
            $max = $db->loadResult();

            $table->set("ordering", $max + 1);
        }

        // Prepare image.
        if (!empty($data["image"])) {

            // Delete old image if I upload a new one
            if (!empty($table->image)) {

                $params       = JComponentHelper::getParams($this->option);
                /** @var  $params Joomla\Registry\Registry */

                $imagesFolder = $params->get("images_directory", "images/crowdfunding");

                // Remove an image from the filesystem
                $fileImage  = JPath::clean(JPATH_ROOT . DIRECTORY_SEPARATOR . $imagesFolder . DIRECTORY_SEPARATOR . $table->image);
                $fileSmall  = JPath::clean(JPATH_ROOT . DIRECTORY_SEPARATOR . $imagesFolder . DIRECTORY_SEPARATOR . $table->image_small);
                $fileSquare = JPath::clean(JPATH_ROOT . DIRECTORY_SEPARATOR . $imagesFolder . DIRECTORY_SEPARATOR . $table->image_square);

                if (is_file($fileImage)) {
                    JFile::delete($fileImage);
                }

                if (is_file($fileSmall)) {
                    JFile::delete($fileSmall);
                }

                if (is_file($fileSquare)) {
                    JFile::delete($fileSquare);
                }

            }
            $table->set("image", $data["image"]);
            $table->set("image_small", $data["image_small"]);
            $table->set("image_square", $data["image_square"]);
        }


        // Prepare pitch image.
        if (!empty($data["pitch_image"])) {

            // Delete old image if I upload a new one
            if (!empty($table->pitch_image)) {

                $params       = JComponentHelper::getParams($this->option);
                $imagesFolder = $params->get("images_directory", "images/crowdfunding");

                // Remove an image from the filesystem
                $pitchImage = JPath::clean(JPATH_ROOT . DIRECTORY_SEPARATOR . $imagesFolder . DIRECTORY_SEPARATOR . $table->pitch_image);

                if (is_file($pitchImage)) {
                    JFile::delete($pitchImage);
                }
            }

            $table->set("pitch_image", $data["pitch_image"]);

        }

        // If an alias does not exist, I will generate the new one using the title.
        if (!$table->alias) {
            $table->alias = $table->title;
        }
        $table->alias = JApplicationHelper::stringURLSafe($table->alias);

        // Prepare funding duration

        $durationType = Joomla\Utilities\ArrayHelper::getValue($data, "duration_type");
        $fundingStart = Joomla\Utilities\ArrayHelper::getValue($data, "funding_start");
        $fundingEnd   = Joomla\Utilities\ArrayHelper::getValue($data, "funding_end");
        $fundingDays  = Joomla\Utilities\ArrayHelper::getValue($data, "funding_days");

        // Prepare funding start date.
        $fundingStartValidator = new Prism\Validator\Date($fundingStart);
        if (!$fundingStartValidator->isValid()) {
            $table->funding_start = "0000-00-00";
        } else {
            $date = new JDate($fundingStart);
            $table->funding_start = $date->toSql();
        }

        switch ($durationType) {

            case "days":

                // Set funding day.
                $table->funding_days = $fundingDays;

                // Calculate end date
                $fundingStartValidator = new Prism\Validator\Date($table->funding_start);
                if (!$fundingStartValidator->isValid()) {
                    $table->funding_end = "0000-00-00";
                } else {
                    $fundingStartDate   = new Crowdfunding\Date($table->funding_start);
                    $fundingEndDate     = $fundingStartDate->calculateEndDate($table->funding_days);
                    $table->funding_end = $fundingEndDate->toSql();
                }

                break;

            case "date":

                $fundingEndValidator = new Prism\Validator\Date($fundingEnd);
                if (!$fundingEndValidator->isValid()) {
                    throw new Exception(JText::_("COM_CROWDFUNDING_ERROR_INVALID_DATE"));
                }

                $date = new JDate($fundingEnd);

                $table->funding_days = 0;
                $table->funding_end  = $date->toSql();

                break;

            default:
                $table->funding_days = 0;
                $table->funding_end  = "0000-00-00";
                break;
        }

    }

    /**
     * Method to change the approved state of one or more records.
     *
     * @param   array   $pks   A list of the primary keys to change.
     * @param   integer $value The value of the approved state.
     *
     * @throws Exception
     */
    public function approve(array $pks, $value)
    {
        $pks = (array)$pks;

        $db = JFactory::getDbo();

        $query = $db->getQuery(true);
        $query
            ->update($db->quoteName("#__crowdf_projects"))
            ->set($db->quoteName("approved") ." = " . (int)$value)
            ->where($db->quoteName("id") ." IN (" . implode(",", $pks) . ")");

        $db->setQuery($query);
        $db->execute();

        // Trigger change state event

        $context = $this->option . '.' . $this->name;

        // Include the content plugins for the change of state event.
        JPluginHelper::importPlugin('content');

        // Trigger the onContentChangeState event.
        $dispatcher = JEventDispatcher::getInstance();
        $result     = $dispatcher->trigger($this->event_change_state, array($context, $pks, $value));

        if (in_array(false, $result, true)) {
            throw new RuntimeException(JText::_("COM_CROWDFUNDING_ERROR_TRIGGERING_PLUGIN"));
        }

        // Clear the component's cache
        $this->cleanCache();

    }

    /**
     * Method to toggle the featured setting of articles.
     *
     * @param   array   $pks   The ids of the items to toggle.
     * @param   integer $value The value to toggle to.
     *
     * @return  boolean  True on success.
     */
    public function featured(array $pks, $value = 0)
    {
        $db = $this->getDbo();

        $query = $db->getQuery(true);
        $query
            ->update($db->quoteName("#__crowdf_projects"))
            ->set($db->quoteName("featured") ." = " . (int)$value)
            ->where($db->quoteName("id"). " IN (" . implode(",", $pks) . ")");

        $db->setQuery($query);
        $db->execute();

        // Clear the component's cache
        $this->cleanCache();
    }

    /**
     * Method to change the published state of one or more records.
     *
     * @param   array   &$pks  A list of the primary keys to change.
     * @param   integer $value The value of the published state.
     *
     * @throws Exception
     *
     * @return  boolean  True on success.
     *
     * @since   12.2
     */
    public function publish(&$pks, $value = 0)
    {
        $table = $this->getTable();
        /** @var $table CrowdfundingTableProject */

        $pks   = (array)$pks;

        // Access checks.
        foreach ($pks as $pk) {

            $table->reset();

            if ($table->load($pk)) {

                if ($value == Prism\Constants::PUBLISHED) { // Publish a project

                    // Validate funding period
                    $fundingEndValidator = new Prism\Validator\Date($table->funding_end);

                    if (!$table->funding_days and !$fundingEndValidator->isValid()) {
                        throw new RuntimeException(JText::_("COM_CROWDFUNDING_ERROR_INVALID_DURATION_PERIOD"));
                    }


                    // Calculate starting date if the user publish a project for first time.
                    $fundingStartValidator = new Prism\Validator\Date($table->funding_start);
                    if (!$fundingStartValidator->isValid()) {
                        $fundingStart         = new JDate();
                        $table->funding_start = $fundingStart->toSql();

                        // If funding type is "days", calculate end date.
                        if (!empty($table->funding_days)) {
                            $fundingStartDate   = new Crowdfunding\Date($table->funding_start);
                            $fundingEndDate     = $fundingStartDate->calculateEndDate($table->funding_days);
                            $table->funding_end = $fundingEndDate->toSql();
                        }
                    }

                    // Validate the period if the funding type is days
                    $params = JComponentHelper::getParams($this->option);
                    /** @var  $params Joomla\Registry\Registry */

                    $minDays = $params->get("project_days_minimum", 15);
                    $maxDays = $params->get("project_days_maximum");

                    $fundingStartValidator = new Prism\Validator\Date($table->funding_start);
                    if ($fundingStartValidator->isValid()) {

                        $dateValidator = new Crowdfunding\Validator\Project\Period($table->funding_start, $table->funding_end, $minDays, $maxDays);
                        if (!$dateValidator->isValid()) {
                            if (!empty($maxDays)) {
                                throw new RuntimeException(JText::sprintf("COM_CROWDFUNDING_ERROR_INVALID_ENDING_DATE_MIN_MAX_DAYS", $minDays, $maxDays));
                            } else {
                                throw new RuntimeException(JText::sprintf("COM_CROWDFUNDING_ERROR_INVALID_ENDING_DATE_MIN_DAYS", $minDays));
                            }
                        }

                    }

                    $table->set("published", Prism\Constants::PUBLISHED);
                    $table->store();

                } else { // Set other states - unpublished, trash,...
                    $table->publish(array($pk), $value);
                }
            }
        }

        // Trigger change state event

        $context = $this->option . '.' . $this->name;

        // Include the content plugins for the change of state event.
        JPluginHelper::importPlugin('content');

        // Trigger the onContentChangeState event.
        $dispatcher = JEventDispatcher::getInstance();
        $result     = $dispatcher->trigger($this->event_change_state, array($context, $pks, $value));

        if (in_array(false, $result, true)) {
            throw new Exception(JText::_("COM_CROWDFUNDING_ERROR_CHANGE_STATE"));
        }

        // Clear the component's cache
        $this->cleanCache();

    }

    /**
     * A protected method to get a set of ordering conditions.
     *
     * @param    object $table A record object.
     *
     * @return    array    An array of conditions to add to add to ordering queries.
     * @since    1.6
     */
    protected function getReorderConditions($table)
    {
        $condition   = array();
        $condition[] = 'catid = ' . (int)$table->catid;

        return $condition;
    }

    /**
     * Method to delete one or more records.
     *
     * @param   array &$pks An array of record primary keys.
     *
     * @return  boolean  True if successful, false if an error occurs.
     *
     * @since   12.2
     */
    public function delete(&$pks)
    {
        $params       = JComponentHelper::getParams($this->option);
        /** @var  $params Joomla\Registry\Registry */

        $folderImages = $params->get("images_directory", "images/crowdfunding");

        foreach ($pks as $id) {

            $project = new Crowdfunding\Project(JFactory::getDbo());
            $project->load($id);

            $this->deleteProjectImages($project, $folderImages);
            $this->deleteAdditionalImages($project, $folderImages);
            $this->removeIntentions($project);
            $this->removeComments($project);
            $this->removeUpdates($project);
            $this->removeRewards($project);
            $this->removeTransactions($project);

        }

        return parent::delete($pks);
    }

    protected function deleteAdditionalImages(Crowdfunding\Project $project, $folderImages)
    {
        $db = $this->getDbo();

        $projectId = $project->getId();

        // Get the extra image
        $query = $db->getQuery(true);
        $query
            ->select("a.image, a.thumb")
            ->from($db->quoteName("#__crowdf_images", "a"))
            ->where("a.project_id =" . (int)$projectId);

        $db->setQuery($query);
        $results = $db->loadObjectList();
        if (!$results) {
            $results = array();
        }

        // Remove
        foreach ($results as $images) {

            $image = JPath::clean(JPATH_ROOT . DIRECTORY_SEPARATOR . $folderImages . DIRECTORY_SEPARATOR . "user" . $project->getUserId() . DIRECTORY_SEPARATOR . $images->image);
            if (JFile::exists($image)) {
                JFile::delete($image);
            }

            $thumb = JPath::clean(JPATH_ROOT . DIRECTORY_SEPARATOR . $folderImages . DIRECTORY_SEPARATOR . "user" . $project->getUserId() . DIRECTORY_SEPARATOR . $images->thumb);
            if (JFile::exists($thumb)) {
                JFile::delete($thumb);
            }
        }

        // Delete records of the images
        $query = $db->getQuery(true);
        $query
            ->delete($db->quoteName("#__crowdf_images"))
            ->where($db->quoteName("project_id") . "=" . (int)$projectId);

        $db->setQuery($query);
        $db->execute();
    }

    protected function deleteProjectImages(Crowdfunding\Project $project, $folderImages)
    {
        $images = array(
            "image"        => $project->getImage(),
            "image_square" => $project->getSquareImage(),
            "image_small"  => $project->getSmallImage()
        );

        // Remove
        foreach ($images as $image) {

            $imageFile = JPath::clean(JPATH_ROOT . DIRECTORY_SEPARATOR . $folderImages . DIRECTORY_SEPARATOR . $image);
            if (JFile::exists($imageFile)) {
                JFile::delete($imageFile);
            }
        }
    }

    protected function removeIntentions(Crowdfunding\Project $project)
    {
        // Create query object
        $db    = $this->getDbo();
        $query = $db->getQuery(true);

        $query
            ->delete($db->quoteName("#__crowdf_intentions"))
            ->where($db->quoteName("project_id") . "=" . (int)$project->getId());

        $db->setQuery($query);
        $db->execute();
    }

    protected function removeComments(Crowdfunding\Project $project)
    {
        // Create query object
        $db    = $this->getDbo();
        $query = $db->getQuery(true);

        $query
            ->delete($db->quoteName("#__crowdf_comments"))
            ->where($db->quoteName("project_id") . "=" . (int)$project->getId());

        $db->setQuery($query);
        $db->execute();
    }

    protected function removeUpdates(Crowdfunding\Project $project)
    {
        // Create query object
        $db    = $this->getDbo();
        $query = $db->getQuery(true);

        $query
            ->delete($db->quoteName("#__crowdf_updates"))
            ->where($db->quoteName("project_id") . "=" . (int)$project->getId());

        $db->setQuery($query);
        $db->execute();
    }

    protected function removeRewards(Crowdfunding\Project $project)
    {
        // Create query object
        $db    = $this->getDbo();
        $query = $db->getQuery(true);

        $query
            ->delete($db->quoteName("#__crowdf_rewards"))
            ->where($db->quoteName("project_id") . "=" . (int)$project->getId());

        $db->setQuery($query);
        $db->execute();
    }

    protected function removeTransactions(Crowdfunding\Project $project)
    {
        // Create query object
        $db    = $this->getDbo();
        $query = $db->getQuery(true);

        $query
            ->delete($db->quoteName("#__crowdf_transactions"))
            ->where($db->quoteName("project_id") . "=" . (int)$project->getId());

        $db->setQuery($query);
        $db->execute();
    }

    /**
     * Upload and resize the image
     *
     * @param array $image
     *
     * @throws Exception
     *
     * @return array
     */
    public function uploadImage($image)
    {
        $app = JFactory::getApplication();
        /** @var $app JApplicationAdministrator */

        $uploadedFile = Joomla\Utilities\ArrayHelper::getValue($image, 'tmp_name');
        $uploadedName = Joomla\Utilities\ArrayHelper::getValue($image, 'name');
        $errorCode    = Joomla\Utilities\ArrayHelper::getValue($image, 'error');

        // Load parameters.
        $params     = JComponentHelper::getParams($this->option);
        /** @var  $params Joomla\Registry\Registry */

        $destFolder = JPath::clean(JPATH_ROOT . DIRECTORY_SEPARATOR . $params->get("images_directory", "images/crowdfunding"));

        $tmpFolder = $app->get("tmp_path");

        // Joomla! media extension parameters
        $mediaParams = JComponentHelper::getParams("com_media");
        /** @var  $mediaParams Joomla\Registry\Registry */

        $file = new Prism\File\File();

        // Prepare size validator.
        $KB            = 1024 * 1024;
        $fileSize      = (int)$app->input->server->get('CONTENT_LENGTH');
        $uploadMaxSize = $mediaParams->get("upload_maxsize") * $KB;

        // Prepare file size validator
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
        $ext = JFile::makeSafe(JFile::getExt($image['name']));

        $generatedName = new Prism\String();
        $generatedName->generateRandomString(32);

        $tmpDestFile = $tmpFolder . DIRECTORY_SEPARATOR . $generatedName . "." . $ext;

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

        $imageName  = $generatedName . "_image.png";
        $smallName  = $generatedName . "_small.png";
        $squareName = $generatedName . "_square.png";

        $imageFile  = $destFolder . DIRECTORY_SEPARATOR . $imageName;
        $smallFile  = $destFolder . DIRECTORY_SEPARATOR . $smallName;
        $squareFile = $destFolder . DIRECTORY_SEPARATOR . $squareName;

        // Create main image
        $width  = $params->get("image_width", 200);
        $height = $params->get("image_height", 200);
        $image->resize($width, $height, false);
        $image->toFile($imageFile, IMAGETYPE_PNG);

        // Create small image
        $width  = $params->get("image_small_width", 100);
        $height = $params->get("image_small_height", 100);
        $image->resize($width, $height, false);
        $image->toFile($smallFile, IMAGETYPE_PNG);

        // Create square image
        $width  = $params->get("image_square_width", 50);
        $height = $params->get("image_square_height", 50);
        $image->resize($width, $height, false);
        $image->toFile($squareFile, IMAGETYPE_PNG);

        $names = array(
            "image"        => $imageName,
            "image_small"  => $smallName,
            "image_square" => $squareName
        );

        // Remove the temporary file.
        if (is_file($tmpDestFile)) {
            JFile::delete($tmpDestFile);
        }

        return $names;
    }

    /**
     * Upload a pitch image.
     *
     * @param  array $image
     *
     * @throws Exception
     *
     * @return array
     */
    public function uploadPitchImage($image)
    {
        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        $uploadedFile = Joomla\Utilities\ArrayHelper::getValue($image, 'tmp_name');
        $uploadedName = Joomla\Utilities\ArrayHelper::getValue($image, 'name');
        $errorCode    = Joomla\Utilities\ArrayHelper::getValue($image, 'error');

        // Load parameters.
        $params     = JComponentHelper::getParams($this->option);
        /** @var  $params Joomla\Registry\Registry */

        $destFolder = JPath::clean(JPATH_ROOT . DIRECTORY_SEPARATOR . $params->get("images_directory", "images/crowdfunding"));

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

        $tmpDestFile = $tmpFolder . DIRECTORY_SEPARATOR . $generatedName . "." . $ext;

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
        $imageFile = JPath::clean($destFolder . DIRECTORY_SEPARATOR . $imageName);

        // Create main image
        $width  = $params->get("pitch_image_width", 600);
        $height = $params->get("pitch_image_height", 400);
        $image->resize($width, $height, false);
        $image->toFile($imageFile, IMAGETYPE_PNG);

        // Remove the temporary
        if (is_file($tmpDestFile)) {
            JFile::delete($tmpDestFile);
        }

        return $imageName;
    }

    /**
     * Delete image only.
     *
     * @param integer $id Item id
     */
    public function removeImage($id)
    {
        // Load category data
        /** @var $row CrowdfundingTableProject */
        $row = $this->getTable();
        $row->load($id);

        // Delete old image if I upload the new one
        if (!empty($row->image)) {

            $params       = JComponentHelper::getParams($this->option);
            /** @var  $params Joomla\Registry\Registry */

            $imagesFolder = JPath::clean(JPATH_ROOT . DIRECTORY_SEPARATOR . $params->get("images_directory", "images/crowdfunding"));

            // Remove an image from the filesystem
            $fileImage  = $imagesFolder . DIRECTORY_SEPARATOR . $row->image;
            $fileSmall  = $imagesFolder . DIRECTORY_SEPARATOR . $row->image_small;
            $fileSquare = $imagesFolder . DIRECTORY_SEPARATOR . $row->image_square;

            if (is_file($fileImage)) {
                JFile::delete($fileImage);
            }

            if (is_file($fileSmall)) {
                JFile::delete($fileSmall);
            }

            if (is_file($fileSquare)) {
                JFile::delete($fileSquare);
            }

        }

        $row->set("image", "");
        $row->set("image_small", "");
        $row->set("image_square", "");
        $row->store();

    }

    /**
     * Delete pitch image.
     *
     * @param integer $id Item id
     */
    public function removePitchImage($id)
    {
        // Load category data
        $row = $this->getTable();
        $row->load($id);

        // Delete old image if I upload the new one
        if (!empty($row->pitch_image)) {

            $params       = JComponentHelper::getParams($this->option);
            /** @var  $params Joomla\Registry\Registry */

            $imagesFolder = JPath::clean(JPATH_ROOT . DIRECTORY_SEPARATOR . $params->get("images_directory", "images/crowdfunding"));

            // Remove an image from the filesystem
            $pitchImage = $imagesFolder . DIRECTORY_SEPARATOR . $row->pitch_image;

            if (is_file($pitchImage)) {
                JFile::delete($pitchImage);
            }

        }

        $row->set("pitch_image", "");
        $row->store();
    }
}
