jQuery(document).ready(function() {
    "use strict";

    var $rewardsWrapperElement = jQuery("#rewards_wrapper");

	jQuery("#cf_add_new_reward").bind("click", function(event) {
		event.preventDefault();

        var $itemsNumberElement = jQuery("#items_number");

		var item 		= jQuery("#reward_tmpl").clone();
		var itemsNumber = parseInt($itemsNumberElement.attr("value"));
		itemsNumber 	= itemsNumber + 1;
		
		// Clone element
		jQuery(item).attr("id", "reward_box_d");
		jQuery(item).appendTo("#rewards_wrapper");
		
		// Element wrapper 
		jQuery("#reward_box_d", "#rewards_wrapper").attr("id", "reward_box_"+itemsNumber);
		
		// Amount
		jQuery("#reward_amount_label_d", "#rewards_wrapper").attr("for", "reward_amount_"+itemsNumber);
		jQuery("#reward_amount_label_d", "#rewards_wrapper").removeAttr("id");
		jQuery("#reward_amount_d", "#rewards_wrapper").attr("name", "rewards["+itemsNumber+"][amount]");
		jQuery("#reward_amount_d", "#rewards_wrapper").attr("id", "reward_amount_"+itemsNumber);
		
		// Title
		jQuery("#reward_title_title_d", "#rewards_wrapper").attr("for", "reward_title_"+itemsNumber);
		jQuery("#reward_title_title_d", "#rewards_wrapper").removeAttr("id");
		jQuery("#reward_title_d", "#rewards_wrapper").attr("name", "rewards["+itemsNumber+"][title]");
		jQuery("#reward_title_d", "#rewards_wrapper").attr("id", "reward_title_"+itemsNumber);
		
		// Description
		jQuery("#reward_description_title_d", "#rewards_wrapper").attr("for", "reward_description_"+itemsNumber);
		jQuery("#reward_description_title_d", "#rewards_wrapper").removeAttr("id");
		jQuery("#reward_description_d", "#rewards_wrapper").attr("name", "rewards["+itemsNumber+"][description]");
		jQuery("#reward_description_d", "#rewards_wrapper").attr("id", "reward_description_"+itemsNumber);
		
		// Availble
		jQuery("#reward_number_title_d", "#rewards_wrapper").attr("for", "reward_number_"+itemsNumber);
		jQuery("#reward_number_title_d", "#rewards_wrapper").removeAttr("id");
		jQuery("#reward_number_d", "#rewards_wrapper").attr("name", "rewards["+itemsNumber+"][number]");
		jQuery("#reward_number_d", "#rewards_wrapper").attr("id", "reward_number_"+itemsNumber);
		
		// Delivery
		jQuery("#reward_delivery_title_d", "#rewards_wrapper").attr("for", "reward_delivery_"+itemsNumber);
		jQuery("#reward_delivery_title_d", "#rewards_wrapper").removeAttr("id");
		jQuery("#reward_delivery_d", "#rewards_wrapper").attr("name", "rewards["+itemsNumber+"][delivery]");
		jQuery("#reward_delivery_d", "#rewards_wrapper").attr("id", "reward_delivery_"+itemsNumber);

		// Reward ID
		jQuery("#reward_id_d", "#rewards_wrapper").attr("name", "rewards["+itemsNumber+"][id]");
		jQuery("#reward_id_d", "#rewards_wrapper").removeAttr("id");
		
		// Number of elements
        $itemsNumberElement.attr("value", itemsNumber);
		
		// The button "remove"
		var elementRemove = jQuery("#reward_remove_d", "#rewards_wrapper");
		jQuery(elementRemove).attr("id", "reward_remove_"+itemsNumber);
		jQuery(elementRemove).data("index-id", itemsNumber);
		
		// Display form
		jQuery(item).show();
		
		// Calendar 
		jQuery("#reward_delivery_d_img", "#rewards_wrapper").attr("id", "reward_delivery_"+itemsNumber+"_img");
        jQuery("#reward_delivery_"+itemsNumber).datetimepicker({
            format: projectWizard.dateFormat,
            locale: projectWizard.locale
        });
		
	});

    $rewardsWrapperElement.on("click", ".js-btn-remove-reward", function(event) {
		event.preventDefault();
		
		var index    = jQuery(this).data("index-id");
		var rewardId = jQuery(this).data("reward-id");
		
		var rewardTitle = jQuery("#reward_title_"+index).val();
		var rewardDesc 	= jQuery("#reward_description_"+index).val();
		
		// Confirm reward removing.
		if((rewardId > 0) || (rewardTitle.length > 0) || (rewardDesc.length > 0)) {
			if(!window.confirm(Joomla.JText._('COM_CROWDFUNDING_QUESTION_REMOVE_REWARD')) ) {
				return;
			}
		}
		
		if(rewardId) { // Delete the reward in database and remove the element it from UI.
			
			var data = "rid[]="+rewardId;
			
			jQuery.ajax({
				url: "index.php?option=com_crowdfunding&format=raw&task=rewards.remove",
				type: "POST",
				data: data,
				dataType: "text json",
				success: function(response) {
					
					if(response.success) {
						jQuery("#reward_box_"+index).remove();
                        PrismUIHelper.displayMessageSuccess(response.title, response.text);
					} else {
                        PrismUIHelper.displayMessageFailure(response.title, response.text);
					}
					
				}
					
			});
			
		} else { // Remove the element 
			jQuery("#reward_box_"+index).remove();
		}
		
	});


    // Check for image elements and enabled rewards functionality.
    var imageWrappers = jQuery(".js-reward-image-wrapper");
    if (imageWrappers.length > 0) {
        // Style file input
        jQuery(".js-reward-image").fileinput({
            browseLabel: Joomla.JText._('COM_CROWDFUNDING_PICK_IMAGE'),
            browseClass: "btn btn-success",
            showUpload: false,
            showPreview: false,
            removeLabel: "",
            removeClass: "btn btn-danger"
        });
    }

    $rewardsWrapperElement.on("click", ".js-btn-remove-reward-image", function(event) {
		event.preventDefault();
		
		var rewardId = jQuery(this).data("reward-id");
		
		// Confirm reward removing.
		if(rewardId) {
			if(!window.confirm(Joomla.JText._('COM_CROWDFUNDING_QUESTION_REMOVE_IMAGE')) ) {
				return;
			}
		}
		
		var self = this;
		
		// Delete the reward image.
		if(rewardId) { 
			
			jQuery.ajax({
				url: "index.php?option=com_crowdfunding",
				type: "POST",
				data: {
                    rid: rewardId,
                    format: "raw",
                    task: "rewards.removeImage"
                },
				dataType: "text json",
				success: function(response) {
					
					if(response.success) {
						jQuery("#js-reward-image-"+rewardId).attr("src", "media/com_crowdfunding/images/no_image.png");
						jQuery(self).remove();

                        PrismUIHelper.displayMessageSuccess(response.title, response.text);
					} else {
                        PrismUIHelper.displayMessageFailure(response.title, response.text);
					}
					
				}
					
			});
			
		}
		
	});
});