/**
 * Created by br006093 on 6/16/2015.
 */
console.log('file posting javascript file loaded');

var meta = {
    quickLinks: {
        names: {
            text: "meta-quicklink-text[]",
            url: "meta-quicklink-url[]"
        },
        placeholders: {
            text: "Enter the Quicklink\'s display text here",
            url: "Enter the Quicklink\'s url text here"
        }
    },
    spotlight:{
        names: {
            text: "meta-spotlight-text[]",
            url: "meta-spotlight-url[]",
            image: "meta-spotlight-image[]"
        },
        placeholders: {
            text: "Enter the Spotlight\'s display text here",
            url: "Enter the Spotlight\'s url text here",
            image: "Enter the Spotlight\'s image here"
        }
    }

};

/*
 * Attaches the image uploader to the input field
 */
jQuery(document).ready(function($){

    // Instantiates the variable that holds the media library frame.
    var meta_image_frame;

    function imageManager( elem ) {

        meta_image_frame = wp.media.frames.file_frame = wp.media({ // Sets up the media library frame
            title: 'Choose or Upload a File',
            button: { text:  'Use this File' },

            multiple: false
        });

        // Runs when an image is selected.
        meta_image_frame.on('select', function(){
            var media_attachment = meta_image_frame.state().get('selection').first().toJSON(); // Grabs the attachment selection and creates a JSON representation of the model.

            $( elem ).val( media_attachment.url ); // Sends the attachment URL to our custom image input field.
        });

        meta_image_frame.open(); // Opens the media library frame.
    }

    // Runs when the image button is clicked.
    $('#meta-file-button').click(function(e){
        var $file_holder =
        // Prevents the default action from occuring.
        e.preventDefault();

        imageManager( $("input[name=file_url]") );
    });
});