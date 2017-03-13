AGORAMAIN = {};

AGORAMAIN.init = function()
{
    $("#agora_room_container").perfectScrollbar();
    $(".right_scroller_container").perfectScrollbar();


    $(".agora_room").on('click', AGORAMAIN.handleAgoraRoomSelection);
    $(".tab").on('click', AGORAMAIN.handleAgoraRoomTab);

    //previewFloatBox = OW.ajaxFloatBox('SPODAGORA_CMP_AgoraRoomCreator', {} , {top: '60px', width:'60%', height:'480px', iconClass: 'ow_ic_add', title: ''});
};

AGORAMAIN.handleAgoraRoomTab = function()
{
    $(".tab").removeClass("selected");
    $(this).addClass("selected");
};

AGORAMAIN.handleAgoraRoomSelection = function()
{
    $(".agora_room").removeClass("selected");
    $(".box").removeClass("selected");

    var box = $(this).find(".box")[0];
    $(this).addClass("selected");
    $(box).addClass("selected");
};

AGORAMAIN.addNewRoom = function(event)
{
    console.log(event);
};