<?php
/**
 * @package         Prism
 * @subpackage      Files\Interfaces
 * @author          Todor Iliev
 * @copyright       Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license         GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Prism\File;

defined('JPATH_PLATFORM') or die;

/**
 * An interface of file removers.
 *
 * @package         Prism
 * @subpackage      Files\Interfaces
 */
interface RemoverInterface
{
    public function remove();
    public function setFile($file);
    public function getFile();
}
