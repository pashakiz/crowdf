jQuery(document).ready(function() {
	
	// Validation script
    Joomla.submitbutton = function(task){
        if (task == 'reward.cancel' || document.formvalidator.isValid(document.getElementById('adminForm'))) {
            Joomla.submitform(task, document.getElementById('adminForm'));
        }
    };

    jQuery('.fileupload').fileinput();
    
});