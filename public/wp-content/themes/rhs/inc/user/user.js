jQuery(document).ready(function($) {
    $('.header_logo_upload').click(function(e) {
        e.preventDefault();

        var custom_uploader = wp.media({
            title: 'Custom Image',
            button: {
                text: 'Upload Image'
            },
            multiple: false
        }).on('select', function() {
            var attachment = custom_uploader.state().get('selection').first().toJSON();
            $('.header_logo').attr('src', attachment.url);
            $('.header_logo_url').val(attachment.url);
            $('.header_logo').closest('a').show();

            $('button.header_logo_upload').closest('div').hide();
        }).open();

    });
    
    $('.js-add-user-link').click(function() {
        var links = $('form#your-profile tr.user-links .input-group p').last().clone();
        $(links).find('input').attr('value','');
        $('form#your-profile tr.user-links .input-group').append(links);
    });

    $('form#your-profile tr.user-profile-picture').remove();
    $('form#your-profile table.field-add').insertAfter($('form#your-profile tr.user-description-wrap').closest('.form-table'));

    $('.js-add-link').click(function() {
        var links = $(this).closest('.panel-body').find('.links').last().clone();
        links.find('input').attr('value','').each(function(){
            this.name = this.name.replace(/\[(\d+)\]/, function(string,n1){return '[' + (parseInt(n1,10)+1) + ']'});
        });
        links.insertAfter($(this).closest('.panel-body').find('.links').last());
    });
    
    $('.remove-link').live("click", function() {
        $(this).closest('.links').remove();
    });

});
