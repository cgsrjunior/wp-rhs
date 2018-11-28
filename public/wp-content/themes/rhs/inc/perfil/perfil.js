jQuery( function( $ ) {
    
    var trigger_modal = ".modal-delete-account";
    $(trigger_modal).on("click", function(e) {
        var name = $(this).data('displayname');
        var user_total_posts = $(this).data('total-posts');
        swal({
            type: 'warning',
            title: "Deseja realmente excluir sua conta?",
            showConfirmButton: true,
            showCancelButton: true,
            cancelButtonText: "Cancelar",
            confirmButtonText: "Sim",
            confirmButtonClass: "btn-danger",
             closeOnConfirm: false,
                closeOnCancel: false
        }, function(isConfirm) {
            if(isConfirm) {
                renderConfirmExclusion(user_total_posts, name, e);
            } else {
                swal(name, "Obrigado por continuar contribuindo conosco!", "success");
            }
        });
    });

    function renderConfirmExclusion(posts_count, name, el) {
        var $other = "";
        var $reason_delete = $(".reason-delete").html();
        var $modal_header_img = $(".encerra-header-img").text();
        var html_content = "<i class='fa fa-spinner fa-spin' id='spinner-content-download'></i>";
        if(posts_count > 0) {
            var $other = "<hr>" + $(".manage-content").html();
            html_content += $other;
        }

        html_content += $reason_delete;
        html_content += "<div class='col-md-12'> <a class='btn btn-danger delete-my-account send-to-legacy' data-user='" + name + "' data-send-to-legacy-user='true'>Excuir conta definitivamente</a>"+
            "<a class='btn btn-danger delete-my-account dont-send-to-legacy col-md-6' data-send-to-legacy-user='false'>Excuir conta definitivamente</a> </div> <br>";

        el.preventDefault();
        swal({
            title: name + ", sentimos muito que você tenha que sair!",
            text: html_content,
            html: true,
            showConfirmButton: false,
            showCancelButton: true,
            cancelButtonText: "Cancelar",
            imageUrl: $modal_header_img,
            containerClass: 'deleteAccount'
        });

        $('.dont-send-to-legacy, #spinner-content-download').hide();

        $('#send-to-legacy-user').bind('change', function() {
            if (this.checked) {
                $('.dont-send-to-legacy').hide();
                $('.send-to-legacy').show();
            } else {;
                $('.send-to-legacy').hide();
                $('.dont-send-to-legacy').show();
            }
        });
    }

    $(document).on('click', '.download-my-content', function() {
        var d = new Date();
        var filename = "RHS_meu_backup_de_posts_" + d.getDate() + "" + (d.getMonth() + 1) + ""+ d.getFullYear() + ".xls";
        $('.download-my-content').hide();
        $('#spinner-content-download').show();
        $.ajax({
            type: "POST",
            url: user_vars.ajaxurl,
            cache: false,
            data: {
                action: 'generate_backup_file',
                vars_to_generate: user_vars
            },
            success: function(output) {
                var blob = new Blob(["\ufeff", output]);
                var link = document.createElement('a');
                link.href = window.URL.createObjectURL(blob);
                link.download = filename;
                link.click();
                $('#spinner-content-download').hide();
                $('.download-my-content').show();
            }
        });

    });
    $(document).on('click', '.delete-my-account', function() {
        var send_to_legacy_user = $(this).data('send-to-legacy-user');
        var del_reason = $("input.delreason").last().val();
        var user = $(this).data('user');

        $.ajax({
            type: "POST",
            url: user_vars.ajaxurl,
            cache: false,
            data: {
                action: 'delete_my_account',
                send_to_legacy_user: send_to_legacy_user,
                reason: del_reason,
                user: user
            },
            success: function(output) {
                swal({
                    title: "Excluída!", 
                    text: "Conta excluída com sucesso.", 
                    type: "success"
                  }, function() {
                    window.location.href = window.location.origin;
                  });
            }
        });

    });
});