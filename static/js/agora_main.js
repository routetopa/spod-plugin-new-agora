AGORAMAIN = {
    _selected_Room:''
};

AGORAMAIN.init = function(agora_id)
{
    AGORAMAIN._selected_Room = agora_id ? agora_id.toString() : '';
    $("#user_notification_switch").attr('checked', (AGORAMAIN.user_room_notification.indexOf(AGORAMAIN._selected_Room) != -1 ? true : false));

    $("#agora_room_container").perfectScrollbar();
    $(".right_scroller_container").perfectScrollbar();


    $(".agora_room").on('click', AGORAMAIN.handleAgoraRoomSelection);
    $(".tab").on('click', AGORAMAIN.handleAgoraRoomTab);

    $("#agora_header_add").on('click', function(){
        previewFloatBox = OW.ajaxFloatBox('SPODAGORA_CMP_AgoraRoomCreator', {} , {top: '64px', width:'60%', height:'480px', iconClass: 'ow_ic_add', title: ''});
    });

    $(".add_suggested_dataset").on('click', function(){
        previewFloatBox = OW.ajaxFloatBox('SPODAGORA_CMP_AgoraRoomSuggestion', {room_id:AGORAMAIN._selected_Room} , {top: '64px', width:'60%', height:'480px', iconClass: 'ow_ic_add', title: ''});
    });

    $("#agora_enter_button").on('click', function(){
        if(AGORAMAIN._selected_Room != '') {
            var url = window.location.href;
            url = url[url.length - 1] == '/' ? url : url + '/';
            window.open(url + AGORAMAIN._selected_Room, "_self");
        }
    });

    $("#user_notification_switch").on('click', function(e){
        AGORAMAIN.handleUseNotificationSwitch($(e.currentTarget).is(':checked'));
    });

    new autoComplete({
        selector: '#agora_search_input',
        minChars: 2,
        source: function(term, suggest){
            term = term.toLowerCase();
            var choices =  AGORAMAIN.hashtag;
            var matches = [];
            for (let i=0; i<choices.length; i++)
                if (~choices[i].toLowerCase().indexOf(term)) matches.push(choices[i]);
            suggest(matches);
        }
    });

    $("#agora_search_input").on('keyup', function(e){
        var room = $('#agora_room_container').find('.agora_room');
        room.show();

        if(e.currentTarget.value == '')
            return;

        room.each(function(){
            //search on room hashtags
            if(e.currentTarget.value[0] === '#')
            {
                if(e.currentTarget.value.length < 2)
                    return;

                if($(this).attr('data-hashtag').toLowerCase().indexOf(e.currentTarget.value.toLowerCase() + ' ') === -1){
                    $(this).hide();
                }
            }
            //search on title and description
            else if($(this).find(".box_title")[0].innerHTML.toLowerCase().indexOf(e.currentTarget.value.toLowerCase()) === -1 &&
                    $(this).find(".box_bottom")[0].innerHTML.toLowerCase().indexOf(e.currentTarget.value.toLowerCase()) === -1) {
                $(this).hide();
            }
        });
    });
};

AGORAMAIN.handleUseNotificationSwitch = function(value)
{
    $.ajax({
        type: 'POST',
        url : AGORAMAIN.notification_endpoint,
        data: {addUserNotification:value, roomId:AGORAMAIN._selected_Room},
        dataType : 'JSON',
        success : function(){
            if(value && AGORAMAIN.user_room_notification.indexOf(AGORAMAIN._selected_Room) == -1){
                AGORAMAIN.user_room_notification.push(AGORAMAIN._selected_Room);
            }else if(!value && AGORAMAIN.user_room_notification.indexOf(AGORAMAIN._selected_Room) != -1){
                AGORAMAIN.user_room_notification.splice(AGORAMAIN.user_room_notification.indexOf(AGORAMAIN._selected_Room), 1);
            }
        }
    });
};

AGORAMAIN.handleAgoraRoomTab = function(e)
{
    var _class;
    if ($(this).hasClass("unsort"))
        _class = "sort-down";
    else if ($(this).hasClass("sort-down"))
        _class = "sort-up";
    else if ($(this).hasClass("sort-up"))
        _class = "sort-down";
    else
        _class = "unsort";

    $($(".tab")[0]).removeClass("unsort sort-down sort-up");
    $($(".tab")[0]).addClass(_class);

    $(".tab").removeClass("selected");
    $(this).addClass("selected");

    AGORAMAIN.sortAgoraRoom("latest", (_class == 'sort-down'));
    $("#agora_room_container div.agora_room:not(.owner)").show();

    switch (e.currentTarget.attributes["order-by"].value)
    {
        case "myagora"  : $("#agora_room_container div.agora_room:not(.owner)").hide(); break;
        case "latest"   : break;
        default         : AGORAMAIN.sortAgoraRoom(e.currentTarget.attributes["order-by"].value); break;
    }
};

AGORAMAIN.sortAgoraRoom = function(param, asc=true)
{
    var container = $('#agora_room_container');
    var room = container.find('.agora_room');

    [].sort.call(room, function(a,b) {
        return asc ?  +$(b).attr('data-'+param) - +$(a).attr('data-'+param) : +$(a).attr('data-'+param) - +$(b).attr('data-'+param);
    });

    room.each(function(){
        container.append(this);
    });
};

AGORAMAIN.handleAgoraRoomSelection = function(e)
{
    var room_id = e.currentTarget.id.replace("agora_room_", "agora_room_detail_");
    AGORAMAIN._selected_Room = e.currentTarget.id.replace("agora_room_", "");

    $(".agora_room").removeClass("selected");
    $(".box").removeClass("selected");

    $(".detail_selected_agora").removeClass("detail_selected_agora");
    $("#"+room_id).addClass("detail_selected_agora");


    var box = $(this).find(".box")[0];
    $(this).addClass("selected");
    $(box).addClass("selected");

    $("#user_notification_switch").attr('checked', (AGORAMAIN.user_room_notification.indexOf(AGORAMAIN._selected_Room) != -1 ? true : false));

};

AGORAMAIN.addNewRoom = function(data)
{
    previewFloatBox.close();

    var room_dom = jQuery.parseHTML(data.html);
    $("#agora_room_container").prepend(room_dom[0]);
    $("#agora_right").prepend(room_dom[2]);
    $(".agora_room").on('click', AGORAMAIN.handleAgoraRoomSelection);
    $("#agora_room_container").perfectScrollbar();
    $(".right_scroller_container").perfectScrollbar();
    $(".add_suggested_dataset").on('click', function(){
        previewFloatBox = OW.ajaxFloatBox('SPODAGORA_CMP_AgoraRoomSuggestion', {room_id:data.id} , {top: '60px', width:'60%', height:'480px', iconClass: 'ow_ic_add', title: ''});
    });
};

AGORAMAIN.addSuggestion = function(data)
{
    previewFloatBox.close();
    $("#agora_room_detail_"+data.room_id).find(".suggestion_dataset_container").append("<div class='datasetUrl'><a href='"+data.dataset+"' target='_blank'>"+data.comment+"</a></div>");
};