<?php
/**
 * @package      Prism
 * @subpackage   Controllers
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Prism\Controller\Form;

use Prism\Controller\Form;
use Joomla\Utilities\ArrayHelper;

defined('JPATH_PLATFORM') or die;

/**
 * This class contains common methods and properties
 * used in work with forms on the back-end.
 *
 * @package      Prism
 * @subpackage   Controllers
 */
class Backend extends Form
{
    /**
     * This method prepare a link where the user will be redirected
     * after action he has done.
     *
     * @param array $options
     *
     * # Example:
     * array(
     *        "view",
     *        "layout"
     *        "id",
     *        "url_var",
     *        "force_direction" // This is a link that will be used instead generated by the system.
     * );
     *
     * @return string
     */
    protected function prepareRedirectLink($options)
    {
        $view           = ArrayHelper::getValue($options, "view");
        $task           = ArrayHelper::getValue($options, "task");
        $itemId         = ArrayHelper::getValue($options, "id", 0, "uint");
        $urlVar         = ArrayHelper::getValue($options, "url_var", "id");

        // Remove standard parameters
        unset($options["view"]);
        unset($options["task"]);
        unset($options["id"]);
        unset($options["url_var"]);

        $link = $this->defaultLink;

        // Redirect to different of common views
        if (!empty($view)) {
            $link .= "&view=" . $view;
            if (!empty($itemId)) {
                $link .= $this->getRedirectToItemAppend($itemId, $urlVar);
            } else {
                $link .= $this->getRedirectToListAppend();
            }

            return $link;
        }

        // Prepare redirection
        switch ($task) {
            case "apply":
                $link .= "&view=" . $this->view_item . $this->getRedirectToItemAppend($itemId, $urlVar);
                break;

            case "save2new":
                $link .= "&view=" . $this->view_item . $this->getRedirectToItemAppend();
                break;

            default:
                $link .= "&view=" . $this->view_list . $this->getRedirectToListAppend();
                break;
        }

        // Generate additional parameters
        $extraParams = $this->prepareExtraParameters($options);

        return $link . $extraParams;
    }
}
