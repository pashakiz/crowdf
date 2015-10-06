jQuery(document).ready(function() {

    jQuery("#js-cfdata-btn-submit").on("click", function(event){
		event.preventDefault();

        var form = jQuery("#js-cfdata-form");

		jQuery.ajax({
            url: form.attr("action"),
            type: "POST",
            data: form.serialize(),
            dataType: "text json",
            cache: false,
            beforeSend: function () {
                // Display ajax loading image
                jQuery("#js-cfdata-ajax-loading").show();
            }
        }).done(function(response) {

            console.log(response);
            // Hide ajax loading image
            jQuery("#js-cfdata-ajax-loading").hide();

            // Hide the button
            jQuery("#js-cfdata-btn-submit").hide();

            // Display the button that points to next step
            if(response.success) {

                // Display information about process of submission.
                jQuery("#js-cfdata-btn-alert").append(response.text).show();

                jQuery("#js-continue-btn").attr("href", response.redirect_url).show();

            } else {
                if(response.redirect_url) {
                    setTimeout("location.href = '"+ response.redirect_url +"'", 1500);
                }
            }

        });
    });
});