<?php
/**
 * @package      SocialCommunity
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// no direct access
defined('_JEXEC') or die;

class SocialCommunityModelImport extends JModelForm
{
    protected function populateState()
    {
        $app = JFactory::getApplication();
        /** @var $app JApplicationAdministrator */

        // Load the filter state.
        $value = $app->getUserStateFromRequest('import.context', 'type', "currencies");
        $this->setState('import.context', $value);
    }

    /**
     * Method to get the record form.
     *
     * @param   array   $data     An optional array of data for the form to interrogate.
     * @param   boolean $loadData True if the form is to load its own data (default case), false if not.
     *
     * @return  JForm   A JForm object on success, false on failure
     * @since   1.6
     */
    public function getForm($data = array(), $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm($this->option . '.import', 'import', array('control' => 'jform', 'load_data' => $loadData));
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
        $data = JFactory::getApplication()->getUserState($this->option . '.edit.import.data', array());

        return $data;
    }

    public function extractFile($file, $destFolder)
    {
        // extract type
        $zipAdapter = JArchive::getAdapter('zip');
        $zipAdapter->extract($file, $destFolder);

        $dir = new DirectoryIterator($destFolder);

        $fileName = JFile::stripExt(basename($file));
        $filePath = "";

        foreach ($dir as $fileinfo) {

            $currentFileName = JFile::stripExt($fileinfo->getFilename());

            if (!$fileinfo->isDot() and strcmp($fileName, $currentFileName) == 0) {
                $filePath = $destFolder . DIRECTORY_SEPARATOR . JFile::makeSafe($fileinfo->getFilename());
                break;
            }

        }

        return $filePath;
    }

    public function uploadFile($fileData, $type)
    {
        $app = JFactory::getApplication();
        /** @var $app JApplicationAdministrator */

        jimport('joomla.filesystem.archive');
        jimport('itprism.file');
        jimport('itprism.file.uploader.local');
        jimport('itprism.file.validator.size');
        jimport('itprism.file.validator.server');

        $uploadedFile = JArrayHelper::getValue($fileData, 'tmp_name');
        $uploadedName = JArrayHelper::getValue($fileData, 'name');
        $errorCode    = JArrayHelper::getValue($fileData, 'error');

        $destination = JPath::clean($app->get("tmp_path")) . DIRECTORY_SEPARATOR . JFile::makeSafe($uploadedName);

        $file = new Prism\File\File();

        // Prepare size validator.
        $KB       = 1024 * 1024;
        $fileSize = (int)$app->input->server->get('CONTENT_LENGTH');

        $mediaParams   = JComponentHelper::getParams("com_media");
        /** @var $mediaParams Joomla\Registry\Registry */

        $uploadMaxSize = $mediaParams->get("upload_maxsize") * $KB;

        // Prepare size validator.
        $sizeValidator = new Prism\File\Validator\Size($fileSize, $uploadMaxSize);

        // Prepare server validator.
        $serverValidator = new Prism\File\Validator\Server($errorCode, array(UPLOAD_ERR_NO_FILE));

        $file->addValidator($sizeValidator);
        $file->addValidator($serverValidator);

        // Validate the file
        if (!$file->isValid()) {
            throw new RuntimeException($file->getError());
        }

        // Prepare uploader object.
        $uploader = new Prism\File\Uploader\Local($uploadedFile);
        $uploader->setDestination($destination);

        // Upload the file
        $file->setUploader($uploader);
        $file->upload();

        $fileName = basename($destination);

        // Extract file if it is archive
        $ext = JString::strtolower(JFile::getExt($fileName));
        if (strcmp($ext, "zip") == 0) {

            $destFolder = JPath::clean($app->get("tmp_path")) . "/". $type;
            if (is_dir($destFolder)) {
                JFolder::delete($destFolder);
            }

            $filePath = $this->extractFile($destination, $destFolder);

        } else {
            $filePath = $destination;
        }

        return $filePath;
    }
    
    /**
     *
     * Import locations from TXT or XML file.
     * The TXT file comes from geodata.org
     * The XML file is generated by the current extension ( SocialCommunity )
     *
     * @param string $file    A path to file
     * @param bool   $resetId Reset existing IDs with new ones.
     * @param integer   $minPopulation Reset existing IDs with new ones.
     */
    public function importLocations($file, $resetId = false, $minPopulation = 0)
    {
        $ext = JString::strtolower(JFile::getExt($file));

        switch ($ext) {
            case "xml":
                $this->importLocationsXml($file, $resetId);
                break;
            default: // TXT
                $this->importLocationsTxt($file, $resetId, $minPopulation);
                break;
        }
    }

    protected function importLocationsTxt($file, $resetId, $minPopulation)
    {
        $content = file($file);

        if (!empty($content)) {
            $items = array();
            $db    = $this->getDbo();

            unset($file);

            $i = 0;
            $x = 0;
            foreach ($content as $geodata) {

                $item = mb_split("\t", $geodata);

                // Check for missing ascii characters name
                $name = JString::trim($item[2]);
                if (!$name) {
                    // If missing ascii characters name, use utf-8 characters name
                    $name = JString::trim($item[1]);
                }

                // If missing name, skip the record
                if (!$name) {
                    continue;
                }

                if ($minPopulation > (int)$item[14]) {
                    continue;
                }

                $id = (!$resetId) ? JString::trim($item[0]) : "null";

                $items[$x][] = $id . "," . $db->quote($name) . "," . $db->quote(JString::trim($item[4])) . "," . $db->quote(JString::trim($item[5])) . "," . $db->quote(JString::trim($item[8])) . "," . $db->quote(JString::trim($item[17]));
                $i++;
                if ($i == 500) {
                    $x++;
                    $i = 0;
                }
            }

            unset($content);

            $columns = array('id', 'name', 'latitude', 'longitude', 'country_code', 'timezone');

            foreach ($items as $item) {
                $query = $db->getQuery(true);

                $query
                    ->insert($db->quoteName("#__itpsc_locations"))
                    ->columns($db->quoteName($columns))
                    ->values($item);

                $db->setQuery($query);
                $db->execute();
            }
        }
    }

    protected function importLocationsXml($file, $resetId)
    {
        $xmlstr  = file_get_contents($file);
        $content = new SimpleXMLElement($xmlstr);

        if (!empty($content)) {
            $items = array();
            $db    = JFactory::getDbo();

            $i = 0;
            $x = 0;
            foreach ($content->location as $item) {

                // Check for missing ascii characters name
                $name = JString::trim($item->name);

                // If missing name, skip the record
                if (!$name) {
                    continue;
                }

                // Reset ID
                $id = (!empty($item->id) and !$resetId) ? JString::trim($item->id) : "null";

                $items[$x][] =
                    $id . "," . $db->quote($name) . "," . $db->quote(JString::trim($item->latitude)) . "," .
                    $db->quote(JString::trim($item->longitude)) . "," . $db->quote(JString::trim($item->country_code)) . "," .
                    $db->quote(JString::trim($item->timezone));

                $i++;
                if ($i == 500) {
                    $x++;
                    $i = 0;
                }
            }

            unset($item);
            unset($content);

            $columns = array('id', 'name', 'latitude', 'longitude', 'country_code', 'timezone');

            foreach ($items as $item) {
                $query = $db->getQuery(true);

                $query
                    ->insert($db->quoteName("#__itpsc_locations"))
                    ->columns($db->quoteName($columns))
                    ->values($item);

                $db->setQuery($query);
                $db->execute();
            }

        }

    }

    /**
     * Import countries from XML file.
     * The XML file is generated by the current extension ( SocialCommunity )
     * or downloaded from https://github.com/umpirsky/country-list
     *
     * @param string $file    A path to file
     * @param bool   $resetId Reset existing IDs with new ones.
     */
    public function importCountries($file, $resetId = false)
    {
        $xmlstr  = file_get_contents($file);
        $content = new SimpleXMLElement($xmlstr);

        if (!empty($content)) {

            // Check for existed countries.
            $db    = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query
                ->select("COUNT(*)")
                ->from($db->quoteName("#__itpsc_countries", "a"));

            $db->setQuery($query);
            $result = $db->loadResult();

            if (!empty($result)) { // Update current countries and insert newest.
                $this->updateCountries($content, $resetId);
            } else { // Insert new ones
                $this->insertCountries($content, $resetId);
            }

        }
    }

    protected function insertCountries($content, $resetId)
    {
        $items = array();

        $db = $this->getDbo();

        foreach ($content->country as $item) {

            $name = JString::trim($item->name);
            $code = JString::trim($item->code);
            if (!$name or !$code) {
                continue;
            }

            $id = (!$resetId) ? (int)$item->id : "null";

            $items[] = $id . "," . $db->quote($name) . "," . $db->quote($code) . "," . $db->quote($item->code4) . "," . $db->quote($item->latitude) . "," . $db->quote($item->longitude) . "," . $db->quote($item->currency) . "," . $db->quote($item->timezone);

        }

        unset($content);

        $columns = array("id", "name", "code", "code4", "latitude", "longitude", "currency", "timezone");

        $query = $db->getQuery(true);

        $query
            ->insert($db->quoteName("#__itpsc_countries"))
            ->columns($db->quoteName($columns))
            ->values($items);

        $db->setQuery($query);
        $db->execute();

    }

    /**
     * Update the countries with new columns,
     *
     * @param SimpleXMLElement $content
     */
    protected function updateCountries($content)
    {
        $db = $this->getDbo();
        JLoader::register("SocialCommunityTableCountry", JPATH_ADMINISTRATOR . "/components/com_socialcommunity/tables/country.php");

        foreach ($content->country as $item) {

            $code = JString::trim($item->code);

            $keys = array("code" => $code);

            $table = new SocialCommunityTableCountry($db);
            $table->load($keys);

            if (!$table->get("id")) {
                $table->set("name", JString::trim($item->name));
                $table->set("code", $code);
            }

            $table->code4     = JString::trim($item->code4);
            $table->latitude  = JString::trim($item->latitude);
            $table->longitude = JString::trim($item->longitude);
            $table->currency  = JString::trim($item->currency);
            $table->timezone  = JString::trim($item->timezone);

            $table->store();
        }

    }

    /**
     * Import states from XML file.
     * The XML file is generated by the current extension.
     *
     * @param string $file A path to file
     */
    public function importStates($file)
    {
        $xmlstr  = file_get_contents($file);
        $content = new SimpleXMLElement($xmlstr);

        $generator = (string)$content->attributes()->generator;

        switch ($generator) {

            case "socialcommunity":
                $this->importSocialCommunityStates($content);
                break;

            default:
                $this->importUnofficialStates($content);
                break;
        }
    }

    /**
     * Import states that are based on locations,
     * and which are connected to locations IDs.
     *
     * @param SimpleXMLElement $content
     */
    protected function importSocialCommunityStates($content)
    {
        if (!empty($content)) {

            $states = array();
            $db     = JFactory::getDbo();

            // Prepare data
            foreach ($content->state as $item) {

                // Check for missing state
                $stateCode = JString::trim($item->state_code);
                if (!$stateCode) {
                    continue;
                }

                $id = (int)$item->id;

                $states[$stateCode][] = "(" . $db->quoteName("id") . "=" . (int)$id . ")";

            }

            // Import data
            foreach ($states as $stateCode => $ids) {

                $query = $db->getQuery(true);

                $query
                    ->update("#__itpsc_locations")
                    ->set($db->quoteName("state_code") . "=" . $db->quote($stateCode))
                    ->where(implode(" OR ", $ids));

                $db->setQuery($query);
                $db->execute();
            }

            unset($states);
            unset($content);

        }
    }

    /**
     * Import states that are based on not official states data,
     * and which are not connected to locations IDs.
     *
     * @param SimpleXMLElement $content
     *
     * @todo remove this in next major version.
     */
    protected function importUnofficialStates($content)
    {
        if (!empty($content)) {

            $states = array();
            $db     = JFactory::getDbo();

            foreach ($content->city as $item) {

                // Check for missing ascii characters title
                $name = JString::trim($item->name);
                if (!$name) {
                    continue;
                }

                $code = JString::trim($item->state_code);

                $states[$code][] = "(" . $db->quoteName("name") . "=" . $db->quote($name) . " AND " . $db->quoteName("country_code") . "=" . $db->quote("US") . ")";

            }

            foreach ($states as $stateCode => $cities) {

                $query = $db->getQuery(true);

                $query
                    ->update("#__itpsc_locations")
                    ->set($db->quoteName("state_code") . " = " . $db->quote($stateCode))
                    ->where(implode(" OR ", $cities));

                $db->setQuery($query);
                $db->execute();
            }

            unset($states);
            unset($content);

        }

    }

    public function removeAll($resource)
    {
        if (!$resource) {
            throw new InvalidArgumentException("COM_SOCIALCOMMUNITY_ERROR_INVALID_RESOURCE_TYPE");
        }

        $db = $this->getDbo();

        switch ($resource) {

            case "countries":
                $db->truncateTable("#__itpsc_countries");
                break;

            case "locations":
                $db->truncateTable("#__itpsc_locations");
                break;

        }
    }
}
