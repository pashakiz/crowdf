jQuery(document).ready(function() {
	"use strict";

	jQuery("#js-register-bt").on("click", function(event){

        if (confirm(Joomla.JText._("PLG_CROWDFUNDINGPAYMENT_BANKTRANSFER_REGISTER_TRANSACTION_QUESTION"))) {

            var data = {
                pid: jQuery(this).data("project-id"),
                amount: jQuery(this).data("amount"),
                payment_service: "banktransfer"
            };

            jQuery.ajax({
                url: "index.php?option=com_crowdfunding&task=notifier.notifyAjax&format=raw",
                type: "POST",
                data: data,
                dataType: "text json",
                cache: false,
                beforeSend: function () {
                    jQuery("#js-banktransfer-ajax-loading").show();
                    jQuery("#js-register-bt").prop('disabled', true);
                },
                success: function (response) {

                    // Hide ajax loading image
                    jQuery("#js-banktransfer-ajax-loading").hide();

                    // Hide the button
                    jQuery("#js-register-bt").hide();

                    // Set the information about transaction and show it.
                    jQuery("#js-bt-alert").html(response.text).show();

                    // Display the button that points to next step
                    if (response.success) {
                        jQuery("#js-continue-bt").attr("href", response.redirect_url).show();
                    } else {
                        if (response.redirect_url) {
                            setTimeout("location.href = '" + response.redirect_url + "'", 1500);
                        }
                    }

                }

            });
        }

	});

});