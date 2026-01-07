(function($){
  $(function(){
    // Color picker
    $('.bscs-color').wpColorPicker();

    // Media uploader for logo
    var frame;
    $('#bscs_logo_select').on('click', function(e){
      e.preventDefault();
      if (frame) { frame.open(); return; }
      frame = wp.media({
        title: 'Logo auswählen',
        button: { text: 'Logo verwenden' },
        library: { type: 'image' },
        multiple: false
      });
      frame.on('select', function(){
        var attachment = frame.state().get('selection').first().toJSON();
        if (!attachment || !attachment.url) return;
        $('#bscs_logo_url').val(attachment.url).trigger('change');
        $('#bscs_logo_preview').attr('src', attachment.url).show();
      });
      frame.open();
    });

    $('#bscs_logo_clear').on('click', function(e){
      e.preventDefault();
      $('#bscs_logo_url').val('').trigger('change');
      $('#bscs_logo_preview').attr('src','').hide();
    });
  });
})(jQuery);
