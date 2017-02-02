AGORA = {};

AGORA.init = function () {
    var message = new agoraJs($("#agora_comment"), AGORA.roomId, AGORA.agora_comment_endpoint);
    message.init();

    //var test = new agoraCommentJS();
    //test.addComment($("#agora_chat_container"), AGORA.agora_static_resource_url + 'JSSnippet/comment.tpl', ['zero', 'uno', 'due', 'tre', 'quattro', 'cinque', 'sei']);
};