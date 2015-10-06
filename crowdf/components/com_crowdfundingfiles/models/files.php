<?php
/**
 * @package      CrowdfundingFiles
 * @subpackage   Component
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2014 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// no direct access
defined('_JEXEC') or die;

/**
 * Get a list of items
 */
class CrowdfundingFilesModelFiles extends JModelLegacy
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
    public function getTable($type = 'File', $prefix = 'CrowdfundingFilesTable', $config = array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }

    public function uploadFiles($files, $options)
    {
        $result      = array();
        $destination = JArrayHelper::getValue($options, "destination");
        $maxSize     = JArrayHelper::getValue($options, "max_size");
        $legalExtensions     = JArrayHelper::getValue($options, "legal_extensions");
        $legalFileTypes      = JArrayHelper::getValue($options, "legal_types");

        // check for error
        foreach ($files as $fileData) {

            // Upload image
            if (!empty($fileData['name'])) {

                $uploadedFile = JArrayHelper::getValue($fileData, 'tmp_name');
                $uploadedName = JArrayHelper::getValue($fileData, 'name');
                $errorCode    = JArrayHelper::getValue($fileData, 'error');

                $file = new Prism\File\File();

                // Prepare size validator.
                $KB            = 1024 * 1024;
                $fileSize      = JArrayHelper::getValue($fileData, "size");
                $uploadMaxSize = $maxSize * $KB;

                // Prepare file size validator
                $sizeValidator = new Prism\File\Validator\Size($fileSize, $uploadMaxSize);

                // Prepare server validator.
                $serverValidator = new Prism\File\Validator\Server($errorCode, array(UPLOAD_ERR_NO_FILE));

                // Prepare image validator.
                $typeValidator = new Prism\File\Validator\Type($uploadedFile, $uploadedName);

                // Get allowed MIME types.
                $mimeTypes = explode(",", $legalFileTypes);
                $mimeTypes = array_map('trim', $mimeTypes);
                $typeValidator->setMimeTypes($mimeTypes);

                // Get allowed file extensions.
                $fileExtensions = explode(",", $legalExtensions);
                $fileExtensions = array_map('trim', $fileExtensions);
                $typeValidator->setLegalExtensions($fileExtensions);

                $file
                    ->addValidator($sizeValidator)
                    ->addValidator($typeValidator)
                    ->addValidator($serverValidator);

                // Validate the file
                if (!$file->isValid()) {
                    throw new RuntimeException($file->getError());
                }

                // Generate file name
                $baseName = JString::strtolower(JFile::makeSafe(basename($fileData['name'])));
                $ext      = JFile::getExt($baseName);

                $generatedName = new Prism\String();
                $generatedName->generateRandomString(6);

                $destinationFile = $destination . DIRECTORY_SEPARATOR . $generatedName . "." . $ext;

                // Prepare uploader object.
                $uploader = new Prism\File\Uploader\Local($uploadedFile);
                $uploader->setDestination($destinationFile);

                // Upload temporary file
                $file->setUploader($uploader);

                $file->upload();

                // Get file
                $fileSource = $file->getFile();

                if (!JFile::exists($fileSource)) {
                    throw new RuntimeException(JText::_("COM_CROWDFUNDING_ERROR_FILE_CANT_BE_UPLOADED"));
                }

                $result[] = array(
                    "title"    => $baseName,
                    "filename" => basename($fileSource)
                );
            }
        }

        return $result;
    }

    /**
     * Store the files into database.
     *
     * @param array $files
     * @param int $projectId
     * @param int $userId
     * @param string $fileUri
     *
     * @return array
     */
    public function storeFiles($files, $projectId, $userId, $fileUri)
    {
        settype($files, "array");
        settype($projectId, "integer");
        $result = array();

        if (!empty($files) and !empty($projectId)) {

            $db = JFactory::getDbo();
            /** @var $db JDatabaseDriver */

            foreach ($files as $file) {

                $query = $db->getQuery(true);
                $query
                    ->insert($db->quoteName("#__cffiles_files"))
                    ->set($db->quoteName("title") . "=" . $db->quote($file["title"]))
                    ->set($db->quoteName("filename") . "=" . $db->quote($file["filename"]))
                    ->set($db->quoteName("project_id") . "=" . (int)$projectId)
                    ->set($db->quoteName("user_id") . "=" . (int)$userId);

                $db->setQuery($query);
                $db->execute();

                $lastId = $db->insertid();

                // Add URI path to images
                $result = array(
                    "id"    => $lastId,
                    "title" => $file["title"],
                    "filename" => $file["filename"],
                    "file"  => $fileUri . "/" . $file["filename"]
                );
            }

        }

        return $result;
    }

    /**
     * Delete files.
     *
     * @param integer $fileId File ID
     * @param string  $mediaFolder A path to files folder.
     * @param integer $userId
     *
     * @throws RuntimeException
     */
    public function removeFile($fileId, $mediaFolder, $userId)
    {
        $file = new Prism\File\File();

        // Validate owner of the file.
        $ownerValidator = new CrowdfundingFiles\Validator\Owner(JFactory::getDbo(), $fileId, $userId);
        if (!$ownerValidator->isValid()) {
            throw new RuntimeException(JText::_("COM_CROWDFUNDINGFILES_INVALID_FILE"));
        }

        // Remove the file.
        $remover = new CrowdfundingFiles\File\Remover(JFactory::getDbo(), $fileId, $mediaFolder);
        $remover->remove();
    }
}
