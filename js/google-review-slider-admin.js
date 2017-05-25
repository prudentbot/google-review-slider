(function( $ ) {

  // Add Color Picker to all inputs that have 'color-field' class
  $(function() {
    $('.color-field').wpColorPicker();
  });

  $('.header_logo_upload').click(function(e) {
    e.preventDefault();

    var custom_uploader = wp.media({
      title: 'Custom Image',
      button: {
          text: 'Upload Image'
      },
      multiple: false  // Set this to true to allow multiple files to be selected
    })
    .on('select', function() {
      var attachment = custom_uploader.state().get('selection').first().toJSON();
      // $('.header_logo').attr('src', attachment.url);
      $('.header_logo_url').val(attachment.url);

    })
    .open();
  });


})( jQuery );
