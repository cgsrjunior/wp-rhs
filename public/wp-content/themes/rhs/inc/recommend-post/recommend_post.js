var $ = jQuery;
var input_recommend_post = '#input-recommend-post';
var button_to_submit_recommend = '#submit-recommend';

$(function() {
    var ms = $(input_recommend_post).magicSuggest({
        data: vars.ajaxurl,
        dataUrlParams: { 
            action: 'show_people_to_recommend'
        },
        method: 'post',
        valueField: 'user_id',
        placeholder: 'Insira o nome do usuário',
        allowFreeEntries: false,
        maxSuggestions: 10,
        noSuggestionText: 'Sem sugestões para {{query}}',
        renderer: function(data){
            return '<div style="padding: 5px; overflow:hidden;">' +
                '<div style="float: left; margin-left: 5px">' +
                    '<div style="font-weight: bold; color: #333; font-size: 14px; line-height: 13px">' + data.name + '</div>' +
                '</div>' +
            '</div><div style="clear:both;"></div>';
        }
    });

    $(button_to_submit_recommend).click(function () {
        var id_post = $(this).data('post-id');
        var not_sent_title = "Não enviado!";
        var json_users = JSON.stringify(ms.getSelection());
        $(button_to_submit_recommend).text('Enviando...');

        $.ajax({
            type: 'POST',
            url: vars.ajaxurl,
            dataType: 'json',
            data: {
                'post_id': id_post,
                'users': json_users,
                'action': 'recommend_the_post'
            }, success: function (result) {
                $(button_to_submit_recommend).text('Enviado');
                if(result.msgErr) {
                    swal(not_sent_title, result.msgErr, "error");
                } else if(result.messages.success && result.user.sent_name) {
                    swal("Enviado!", "Indicação enviada com sucesso", "success");
                    ms.clear();
                } else {
                    swal(not_sent_title, "Tente novamente mais tarde!", "error");
                }
               
            },
            error: function () {
                swal(not_sent_title, "Sua indicação não foi enviada.", "error");
                $(button_to_submit_recommend).text('Enviar');
            }
        });
    
    });
});