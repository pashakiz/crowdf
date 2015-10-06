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
?>
<div class="cfbacking<?php echo $this->params->get("pageclass_sfx"); ?>">
    <?php if ($this->params->get('show_page_heading', 1)) : ?>
    <h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
    <?php endif; ?>
	
	<div class="row">
		<div class="col-md-12">
    		<?php 
        	  if(strcmp("three_steps", $this->wizardType) == 0) {
        		  $layout      = new JLayoutFile('payment_wizard');
    		  } else {
        		  $layout      = new JLayoutFile('payment_wizard_four_steps');
    		  }
        	  echo $layout->render($this->layoutData);
    		?>	
    	</div>
	</div>
	
    <?php
    if (!empty($this->event->onDisplay)) {
        echo $this->event->onDisplay;
    }
    ?>
</div>
