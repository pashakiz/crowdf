jQuery(document).ready(function() {

    // Initialize the form
    jQuery("#js-cfpartners-form").on("submit", function(event) {
        event.preventDefault();

        var url = jQuery(this).attr("action");
        var fields = jQuery(this).serialize();

        jQuery.ajax({
            url: url,
            type: "POST",
            data: fields,
            dataType: "text json",
            beforeSend: function() {
                jQuery("#js-cfpartners-ajax-loader").show();
            }
        }).done(function(response) {

                if(!response.success) {
                    PrismUIHelper.displayMessageFailure(response.title, response.text);
                } else {

                    var element = jQuery("#js-cfpartners-element").clone(false);

                    jQuery(element).attr("id", "js-cfpartners-partner"+response.data.id);
                    jQuery(element).children("td:nth-child(2)").html(response.data.name);
                    jQuery(element).removeAttr("style");

                    jQuery(element).find("img").attr("src", response.data.avatar);
                    jQuery(element).find("a").data("partner-id", response.data.id).addClass("js-cfpartners-btn-remove");

                    jQuery("#js-cfpartners-list").append(element);

                    PrismUIHelper.displayMessageSuccess(response.title, response.text);
                }

                // Hide ajax loader.
                jQuery("#js-cfpartners-ajax-loader").hide();

            }
        );

    });

    // Initialize buttons that remove partners
    jQuery("#js-cfpartners-list").on("click", ".js-cfpartners-btn-remove", function(event) {
        event.preventDefault();

        if (confirm(Joomla.JText._('PLG_CROWDFUNDING_PARTNERS_DELETE_QUESTION'))) {

            var partnerId = parseInt(jQuery(this).data("partner-id"));

            if (partnerId > 0) {
                var fields = {
                    id: partnerId
                };

                jQuery.ajax({
                    url: "index.php?option=com_crowdfundingpartners&task=partners.remove&format=raw",
                    type: "POST",
                    data: fields,
                    dataType: "text json"
                }).done(function (response) {

                        if (response.success) {
                            jQuery("#js-cfpartners-partner" + response.data.id).remove();
                            PrismUIHelper.displayMessageSuccess(response.title, response.text);
                        } else {
                            PrismUIHelper.displayMessageFailure(response.title, response.text);
                        }

                    }
                );
            }
        }

    });

});