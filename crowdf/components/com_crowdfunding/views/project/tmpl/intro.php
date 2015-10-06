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

if (!empty($this->article)) {

    if ($this->params->get("project_intro_article_title", 0)) {
        echo "<h2>".$this->escape($this->article->title)."</h2>";
    }

    echo $this->article->introtext;
    echo $this->article->fulltext;
    
} else {
    echo JText::_("COM_CROWDFUNDING_INTRO_ARTICLE_INFO");
}
