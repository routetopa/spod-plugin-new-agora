AGORA.admin_delete_comment = function() {
    $(".agora_editor").on('click', function (e) {
        var comment_id = $(e.currentTarget).parent()[0].id.replace("comment_", "");
        $.ajax({
            type: 'POST',
            url: AGORA.delete_comment_endpoint,
            data: {commentId: comment_id},
            dataType: 'JSON'

        }).done(function (data) {
            if (data.result == 'ok') {
                $(e.currentTarget).parent().remove();
            }
        });
    });
}();