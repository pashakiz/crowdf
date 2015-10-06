jQuery(document).ready(function() {

    var projectId = jQuery("#js-cffiles-project-id").val();

    // Add image
    jQuery('#js-cffiles-fileupload').fileupload({
        dataType: 'text json',
        formData: {project_id: projectId},
        singleFileUploads: true,
        send: function () {
            jQuery("#js-cffiles-ajax-loader").show();
        },
        fail: function () {
            jQuery("#js-cffiles-ajax-loader").hide();
        },
        done: function (event, response) {

            if (!response.result.success) {

                PrismUIHelper.displayMessageFailure(response.result.title, response.result.text);

            } else {

                var element = jQuery("#js-cffiles-element").clone(false);

                jQuery(element).attr("id", "js-cffiles-file" + response.result.data.id);
                jQuery(element).children("td").eq(0).html(response.result.data.title);
                jQuery(element).children("td").eq(1).html(response.result.data.filename);
                jQuery(element).removeAttr("style")

                jQuery(element).find("a:first").attr("href", response.result.data.file);
                jQuery(element).find("a:last").data("file-id", response.result.data.id).addClass("js-cffile-btn-remove");

                jQuery("#js-cffiles-list").append(element);
            }

            // Hide ajax loader.
            jQuery("#js-cffiles-ajax-loader").hide();
        }
    });

    jQuery("#js-cffiles-list").on("click", ".js-cffile-btn-remove", function (event) {
        event.preventDefault();

        if (confirm(Joomla.JText._('PLG_CROWDFUNDING_FILES_DELETE_QUESTION'))) {

            var fileId = parseInt(jQuery(this).data("file-id"));

            if (fileId > 0) {
                var fields = {
                    id: fileId
                };

                jQuery.ajax({
                    url: "index.php?option=com_crowdfundingfiles&task=files.remove&format=raw",
                    type: "POST",
                    data: fields,
                    dataType: "text json"
                }).done(function (response) {

                        if (response.success) {
                            jQuery("#js-cffiles-file" + response.data.file_id).remove();
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