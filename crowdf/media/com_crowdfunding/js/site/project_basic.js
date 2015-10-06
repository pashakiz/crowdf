jQuery(document).ready(function () {
    "use strict";

    var projectId = parseInt(jQuery("#jform_id").val());
    if(!projectId) {
        projectId = 0;
    }

    // Initialize symbol length indicator
    jQuery('#jform_short_desc').maxlength({
        alwaysShow: true,
        placement: 'bottom-right'
    });

    // Load locations from the server
    var $inputTypeahead = jQuery('#jform_location_preview');
    $inputTypeahead.typeahead({
        minLength: 3,
        hint: false
    }, {
        source: function(query, syncResults, asyncResults) {

            jQuery.ajax({
                url: "index.php?option=com_crowdfunding&format=raw&task=project.loadLocation",
                type: "GET",
                data: {query: query},
                dataType: "text json",
                async: true,
                beforeSend : function() {
                    // Show ajax loader.
                    //$loader.show();
                }
            }).done(function(response){
                // Hide ajax loader.
                //$loader.hide();

                if (response.success === false) {
                    return false;
                }

                return asyncResults(response.data);
            });

        },
        async: true,
        limit: 5,
        display: "name"
    });

    $inputTypeahead.bind('typeahead:select', function(event, suggestion) {
        jQuery("#jform_location_id").attr("value", suggestion.id);
    });

    // Validate the fields.
    jQuery('#js-cf-project-form').parsley({
        uiEnabled: false
    });

    /** Image Tools **/

    var aspectWidth  = cfImageWidth * 2;
    var aspectHeight = cfImageHeight + 50;

    // Set picture wrapper size.
    var $pictureWrapper = jQuery("#js-fixed-dragger-cropper");
    $pictureWrapper.css({
        width: aspectWidth,
        height: aspectHeight
    });

    var $image       = $pictureWrapper.find("img");
    var currentImage = $image.attr("src");

    var $btnImageRemove = jQuery("#js-btn-remove-image");

    $image.cropperInitialized = false;

    // Prepare the token as an object.
    var tokenArray = jQuery("#js-image-tools-form").serializeArray();
    var tokenObject = [];
    tokenObject[tokenArray[0].name] = tokenArray[0].value;

    // Prepare form fields.
    var formData = PrismUIHelper.extend({}, {id: projectId}, tokenObject);

    // Get the loader.
    var $loader  = jQuery("#js-thumb-fileupload-loader");

    // Upload image.
    jQuery('#js-thumb-fileupload').fileupload({
        dataType: 'text json',
        formData: formData,
        singleFileUploads: true,
        send: function() {
            $loader.show();
        },
        fail: function() {
            $loader.hide();
        },
        done: function (event, response) {

            if(!response.result.success) {
                PrismUIHelper.displayMessageFailure(response.result.title, response.result.text);
            } else {

                if ($image.cropperInitialized) {
                    $image.cropper("replace", response.result.data);
                } else {

                    $image.attr("src", response.result.data);

                    $image.cropper({
                        aspectRatio: 1/1,
                        autoCropArea: 0.6, // Center 60%
                        multiple: false,
                        dragCrop: false,
                        dashed: false,
                        movable: false,
                        resizable: true,
                        zoomable: false,
                        minWidth: cfImageWidth,
                        minHeight: cfImageHeight,
                        built: function() {
                            jQuery("#js-image-tools").show();
                            $image.cropperInitialized = true;
                        }
                    });
                }

            }

            // Hide ajax loader.
            $loader.hide();
        }
    });

    // Set event to the button "Cancel".
    jQuery("#js-crop-btn-cancel").on("click", function() {
        $image.cropper("destroy");
        $image.attr("src", currentImage);
        $image.cropperInitialized = false;
        jQuery("#js-image-tools").hide();

        // Add the token.
        var fields = PrismUIHelper.extend({}, tokenObject);

        jQuery.ajax({
            url: "index.php?option=com_crowdfunding&format=raw&task=project.cancelImageCrop",
            type: "POST",
            data: fields,
            dataType: "text json",
            beforeSend : function() {
                // Show ajax loader.
                $loader.show();
            }
        }).done(function(){
            // Hide ajax loader.
            $loader.hide();
        });
    });

    // Set event to the button "Crop Image".
    jQuery("#js-crop-btn").on("click", function(event) {
        var croppedData = $image.cropper("getData");

        // Prepare data.
        var data = {
            width: Math.round(croppedData.width),
            height: Math.round(croppedData.height),
            x: Math.round(croppedData.x),
            y: Math.round(croppedData.y),
            id: projectId
        };

        // Add the token.
        var fields = PrismUIHelper.extend({}, data, tokenObject);

        jQuery.ajax({
            url: "index.php?option=com_crowdfunding&format=raw&task=project.cropImage",
            type: "POST",
            data: fields,
            dataType: "text json",
            beforeSend : function() {
                // Show ajax loader.
                $loader.show();
            }

        }).done(function(response) {

            if(!response.success) {
                PrismUIHelper.displayMessageFailure(response.title, response.text);
            } else {
                $image.cropper("destroy");
                $image.attr("src", response.data);
                $image.cropperInitialized = false;

                currentImage = response.data;

                jQuery("#js-image-tools").hide();

                // Hide ajax loader.
                $loader.hide();

                // Display the button "Remove Image".
                if (projectId > 0) {
                    $btnImageRemove.show();
                }
            }

        });

    });

    // Add confirmation question to the remove image button.
    $btnImageRemove.on("click", function(event){
        event.preventDefault();

        var url = jQuery(this).attr("href");

        if (window.confirm(Joomla.JText._('COM_CROWDFUNDING_QUESTION_REMOVE_IMAGE'))) {
            window.location = url;
        }

    });
});