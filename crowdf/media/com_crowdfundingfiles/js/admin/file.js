jQuery(document).ready(function() {
	
	// Validation script
    Joomla.submitbutton = function(task){
        if (task == 'file.cancel' || document.formvalidator.isValid(document.id('adminForm'))) {
            Joomla.submitform(task, document.getElementById('adminForm'));
        }
    };
    
});