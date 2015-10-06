<?php
/**
 * @package      Crowdfunding
 * @subpackage   Component
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('JPATH_PLATFORM') or die;

/**
 * Abstract class defining methods that can be
 * implemented by an Observer class of a JTable class (which is an Observable).
 * Attaches $this Observer to the $table in the constructor.
 * The classes extending this class should not be instanciated directly, as they
 * are automatically instanciated by the JObserverMapper
 *
 * @package      Crowdfunding
 * @subpackage   Component
 * @link         http://docs.joomla.org/JTableObserver
 * @since        3.1.2
 */
class CrowdfundingObserverReward extends JTableObserver
{
    /**
     * The pattern for this table's TypeAlias
     *
     * @var    string
     * @since  3.1.2
     */
    protected $typeAliasPattern = null;

    /**
     * Creates the associated observer instance and attaches it to the $observableObject
     * $typeAlias can be of the form "{variableName}.type", automatically replacing {variableName} with table-instance variables variableName
     *
     * @param   JObservableInterface $observableObject The subject object to be observed
     * @param   array                $params           ( 'typeAlias' => $typeAlias )
     *
     * @return  CrowdfundingObserverReward
     *
     * @since   3.1.2
     */
    public static function createObserver(JObservableInterface $observableObject, $params = array())
    {
        $observer = new self($observableObject);
        $observer->typeAliasPattern = JArrayHelper::getValue($params, 'typeAlias');

        return $observer;
    }

    /**
     * Pre-processor for $table->delete($pk)
     *
     * @param   mixed $pk An optional primary key value to delete.  If not set the instance property value is used.
     *
     * @return  void
     *
     * @since   3.1.2
     * @throws  UnexpectedValueException
     */
    public function onBeforeDelete($pk)
    {
        $userId       = CrowdfundingHelper::getUserIdByRewardId($this->table->id);
        $imagesFolder = CrowdfundingHelper::getImagesFolder($userId);

        // Remove image.
        if (!empty($this->table->image)) {
            $fileSource = $imagesFolder . DIRECTORY_SEPARATOR . $this->table->image;
            if (JFile::exists($fileSource)) {
                 JFile::delete($fileSource);
            }
        }

        // Remove thumbnail.
        if (!empty($this->table->image_thumb)) {
            $fileSource = $imagesFolder . DIRECTORY_SEPARATOR . $this->table->image_thumb;
            if (JFile::exists($fileSource)) {
                 JFile::delete($fileSource);
            }
        }

        // Remove square image.
        if (!empty($this->table->image_square)) {
            $fileSource = $imagesFolder . DIRECTORY_SEPARATOR . $this->table->image_square;
            if (JFile::exists($fileSource)) {
                 JFile::delete($fileSource);
            }
        }
    }
}
