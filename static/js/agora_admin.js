AGORA.admin_delete_comment = function() {
    $(".delete_comment").on('click', function (e) {
        var comment = $(e.currentTarget).parents().eq(3)[0];
        var comment_id = comment.id.replace("comment_", "");
        $.ajax({
            type: 'POST',
            url: AGORA.delete_comment_endpoint,
            data: {commentId: comment_id},
            dataType: 'JSON'

        }).done(function (data) {
            if (data.result == 'ok') {
                comment.remove();
            }
        });
    });
}();