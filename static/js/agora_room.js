var debounce = true;

AGORA = {};

AGORA.init = function ()
{
    // Emoticonize !!
    $('.agora_speech_text').emoticonize();

    // Set datalet preview target
    ODE.commentTarget = "agora_datalet_preview";

    // agoraJS
    var message = new agoraJs($("#agora_comment"),
                              AGORA.roomId,
                              AGORA.agora_comment_endpoint,
                              AGORA.agora_nested_comment_endpoint);
    message.init();
    message.set_string_handler(AGORA.string_handler);

    // Handler for windows resize
    window.addEventListener("resize", function () {
       AGORA.resize();
    });

    // Handler for comment added
    $(window).on("comment_added", function(e){

        AGORA.scroll_bottom();

        var elem = $("#agora_datalet_placeholder_" + e.post_id);
        var parent_children = elem.parent().children()[0];
        $(parent_children).emoticonize();

        if(e.component != "") {
            elem.addClass("agora_fullsize_datalet " + e.component);
            $("#agora_preview_button").hide();
            ODE.reset();
        }
    });

    // Handle for click on send button (submit message)
    $("#agora_comment_send").click(function(){
        if(!message.submit())
            OW.error("Messaggio vuoto");
    });

    // Handle for sentiment button
    $("#agora_sentiment_button").click(function(){
        switch(message.get_sentiment())
        {
            case 0 :
                $("#agora_sentiment_button").css('background', '#4CAF50 url("/ow_static/plugins/agora/images/sentiment-satisfied.svg") no-repeat 0 0');
                message.set_sentiment(1);
                break;

            case 1 :
                $("#agora_sentiment_button").css('background', '#F44336 url("/ow_static/plugins/agora/images/sentiment-dissatisfied.svg") no-repeat 0 0');
                message.set_sentiment(2);
                break;

            default :
                $("#agora_sentiment_button").css('background', '#9E9E9E url("/ow_static/plugins/agora/images/sentiment-neutral.svg") no-repeat 0 0');
                message.set_sentiment(0);
        }
    });

    // Handler for document ready (init perfectScrollbar, resize page, init autogrow)
    $(document).ready(function () {
        $('#agora_chat_container').perfectScrollbar();
        $('#agora_nested_chat_container').perfectScrollbar();
        AGORA.resize();
        $('#agora_comment').autogrow();
    });

    //Handler datalet creator button
    $('#agora_controllet_button').click(function(e){
        ODE.pluginPreview = 'public-room';
        previewFloatBox = OW.ajaxFloatBox('ODE_CMP_Preview', {} , {top:'56px', width:'calc(100vw - 112px)', height:'calc(100vh - 112px)', iconClass: 'ow_ic_add', title: ''});
    });

    //Handler mySpace button
    $('#agora_myspace_button').click(function(e){
        ODE.pluginPreview = 'public-room';
        previewFloatBox = OW.ajaxFloatBox('SPODPR_CMP_PrivateRoomCardViewer', {} , {top:'56px', width:'calc(100vw - 112px)', height:'calc(70vh)', iconClass: 'ow_ic_add', title: ''});
    });

    //Handler unreaded message section
    $('.agora_day_tab').click(function(e){
        AGORA.openDiv(e.currentTarget.id);
    });

    $(".agora_unread_comments").perfectScrollbar();

    //Handle click on unread message
    $(".agora_unread_comment").click(function(e){

        var id = e.currentTarget.id.replace("unread_", "");

        $('#agora_chat_container').scrollTop($('#agora_chat_container').scrollTop() + $("#"+id).position().top);
        $("#"+id+" .agora_speech").css("background", "#FFEB3B");

        setTimeout(
            function()
            {
                $("#"+id+" .agora_speech").css("transition", "background-color 1s ease");
                $("#"+id+" .agora_speech").css("background-color", "#EEEEEE");
            }, 0);

        setTimeout(
            function()
            {
                $("#"+id+" .agora_speech").css("transition", "");
            }, 1000);
    });

    // Handle preview
    $("#agora_preview_button").click(function () {
        $("#agora_datalet_preview").toggle();
        var e = $("#agora_datalet_preview").children()[1]
        $(e).context.behavior.redraw();
    });

    // Handle reply
    $(".agora_speech_reply").click(function (e) {
        var elem = $(e.currentTarget).parents().eq(2);
        message.set_level_up();
        message.set_parentId(elem.attr('id'));
        message.get_nested_comment($("#agora_nested_chat_container"));
    });

    $(window).on("nested_comment_added", function(e) {

        AGORA.slideToNested();

        $("#agora_back").click(function () {
            AGORA.slideFromNested();
            message.set_level_down();
        });

        $("#agora_nested_comment").mouseover(function () {
            $("#agora_nested_speech_text").removeClass("agora_nested_speech_text");
        });

        $("#agora_nested_comment").mouseout(function () {
            $("#agora_nested_speech_text").addClass("agora_nested_speech_text");
        });

    });

    $("#agora_graph_button").click(function () {
        AGORA.showGraph();
    });



    // Handle realtime communication
    // var socket = io(window.location.origin + ":3000");
    //
    // socket.emit('online_notification', {user_id:AGORA.user_id, room_id:AGORA.roomId, plugin:'spodpublic'});
    //
    // socket.on('online_notification_' + AGORA.roomId, function(data) {
    //    data.forEach(function(e){
    //        $("#user_avatar_"+e).addClass("online");
    //    });
    // });
    //
    // socket.on('offline_notification', function(id) {
    //     $("#user_avatar_"+id).removeClass("online");
    // });
    //
    // socket.on('realtime_message_' + AGORA.roomId, function(data) {
    //     if(AGORA.user_id != data.user_id)
    //     {
    //         message.add_rt_comment($("#agora_chat_container"),
    //             AGORA.agora_static_resource_url + 'JSSnippet/rt_comment.tpl',
    //             [(data.sentiment == 0 ? 'neutral' : (data.sentiment == 1 ?'satisfied' : 'dissatisfied')),
    //                 data.user_display_name,
    //                 data.user_url,
    //                 data.user_avatar,
    //                 data.comment,
    //                 data.message_id,
    //                 data.user_display_name,
    //                 'just now',
    //                 '0'],
    //             data.message_id, {component:data.component, params:data.params, fields:data.fields, data:''});
    //     }
    // });
};

AGORA.resize = function()
{
    var h = Math.max(document.documentElement.clientHeight, window.innerHeight || 0) - 250 - $('#agora_header_description').height();/*fixed agora_header_description 48px now*/

    $("#agora_chat_container").height(h);

    $("#agora_chat_container").scrollTop( $( "#agora_chat_container" ).prop( "scrollHeight" ) );
    $("#agora_chat_container").perfectScrollbar('update');
};

AGORA.scroll_bottom = function ()
{
    $("#agora_chat_container").scrollTop( $( "#agora_chat_container" ).prop( "scrollHeight" ) );
    $("#agora_chat_container").perfectScrollbar('update');
};

AGORA.openDiv = function (tab_id)
{
    var h = Math.max(document.documentElement.clientHeight, window.innerHeight || 0) - 56 - 345;

    var tab_to_close = $(".agora_tab_opened")[0];
    var tab_to_open = $(".agora_unread_comments")[tab_id];

    if (debounce && tab_to_open != tab_to_close) {
        debounce = false;

        $(tab_to_close).animate({ //close
            display: "none",
            opacity: 0,
            height: 0
        }, 1000);

        $(tab_to_open).css("display", "block");

        $(tab_to_open).animate({ //open
            display: "block",
            opacity: 1,
            height: h
        }, 1000);

        setTimeout(
            function () {
                $(tab_to_open).delay(0).addClass("agora_tab_opened");
                $(tab_to_close).delay(0).removeClass("agora_tab_opened");

                $(tab_to_close).css("display", "none");

                debounce = true;
            }, 1000);
    }
};

AGORA.string_handler = function(string)
{
    return string.replace(/\n/g, "<br/>");
};

AGORA.slideToNested = function()
{
    var chat = $("#agora_chat_container")[0];
    var nested_chat = $("#agora_nested_chat_container")[0];

    $(nested_chat).show();

    $(nested_chat).delay(500).animate({
        opacity: 1
    }, 500);

    $(chat).animate({
        opacity: 0
    }, 500, function(){$(chat).hide()});
};

AGORA.slideFromNested = function()
{
    var chat = $("#agora_chat_container")[0];
    var nested_chat = $("#agora_nested_chat_container")[0];

    $(chat).show();

    $(chat).delay(500).animate({
        opacity: 1
    }, 500);

    $(nested_chat).animate({
        opacity: 0
    }, 500, function(){
        $(nested_chat).hide();
        $(nested_chat).html("")
    });

};

AGORA.showGraph = function()
{
    var tab_container = $("#agora_tab_container")[0];

    $(tab_container).hide();
    console.log(tab_container);
};
