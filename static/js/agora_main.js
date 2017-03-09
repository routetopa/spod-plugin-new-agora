AGORAMAIN = {};

AGORAMAIN.init = function()
{
    $(".agora_room").on('click', AGORAMAIN.handleAgoraRoomSelection);
    $(".agora_header_add_button").on('click', AGORAMAIN.handleAgoraRoomCreation);
};

AGORAMAIN.handleAgoraRoomCreation = function(e)
{
    previewFloatBox = OW.ajaxFloatBox('SPODAGORA_CMP_AgoraRoomCreator', {} , {top: '60px', width:'60%', height:'480px', iconClass: 'ow_ic_add', title: ''});
};

AGORAMAIN.handleAgoraRoomSelection = function(e)
{
    var id_selected_room = e.currentTarget.id.replace("agora_room_","agora_detail_");

    $(".agora_room_selected").removeClass("agora_room_selected");
    $(e.currentTarget).addClass("agora_room_selected");
    $(".selected_room").removeClass("selected_room");
    $("#"+id_selected_room).addClass("selected_room");
};

AGORAMAIN.addNewRoom = function(event)
{
    console.log(event);
};