jQuery(document).ready(function($){
	"use strict";
    // Instantiates the variable that holds the media library frame.
    var meta_image_frame, $fileField, $fileIDfield, $fileAddBtn, $fileRemoveBtn;
	
    // Runs when the image button is clicked.
    $('.meta-upload-button').on('click', function(e){
        e.preventDefault();
		
		$fileField = $(this).parent().children('input.como-upload-field');
		$fileIDfield = $(this).parent().children('input.como-upload-id-field');
		$fileAddBtn = $(this).parent().children('.meta-upload-button');
		$fileRemoveBtn = $(this).parent().children('.remove-upload-button');
 
        // If the frame already exists, re-open it.
        if ( meta_image_frame ) {
            meta_image_frame.open();
            return;
        }
 
        // Sets up the media library frame
        meta_image_frame = wp.media.frames.meta_image_frame = wp.media({
            title: meta_image.title,
            button: { text:  meta_image.button }
        });
 
        // Runs when an image is selected.
        meta_image_frame.on('select', function(){
            var media_attachment = meta_image_frame.state().get('selection').first().toJSON();
            $fileField.val(media_attachment.url);
			$fileIDfield.val( media_attachment.id);
			$fileAddBtn.addClass( 'hidden' );
			$fileRemoveBtn.removeClass( 'hidden' );
        });
        meta_image_frame.open();
    });
	
	// DELETE FILE LINK
	$('.remove-upload-button').on('click', function(e){
		event.preventDefault();
		$fileField = $(this).parent().children('input.como-upload-field');
		$fileIDfield = $(this).parent().children('input.como-upload-id-field');
		$fileAddBtn = $(this).parent().children('.meta-upload-button');
		$fileRemoveBtn = $(this).parent().children('.remove-upload-button');
		$fileField.val('');
		$fileIDfield.val('');
		$fileAddBtn.removeClass( 'hidden' );
		$fileRemoveBtn.addClass( 'hidden' );
	});
});