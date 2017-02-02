$( document ).ready(function() {

    var entity_id = $("#agora_id").val();
    var endpoint  = $("#agora_endpoint").val();

    var message = new agoraJs($("#agora_comment"), entity_id, endpoint);
    message.init();
});