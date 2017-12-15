AGORA = {
    agoraJS:null,
    agoraUserNotification:null,
    agoraSearchJS:null,
    agoraUserCommentHandling:null,
    debounce:true,
    searchStringLenght:3,
    lastScrollPosition:10e10,
    realtimeAddedComment:0,
    maxRealtimeMessage:10
};

AGORA.init = function ()
{
    $('.agora_speech_text').highlightWord('#', 'hashtag');
    $('.agora_speech_text').highlightWord('@', 'mention');

    // Set datalet preview target
    ODE.commentTarget = "agora_datalet_preview";

    // agoraJS
    AGORA.initAgoraJS();

    // agoraSearchJS
    AGORA.agoraSearchJS = new agoraSearchJS();

    // agoraUserNotification
    AGORA.agoraUserNotification = new agoraUserNotificationJS();

    // agoraUseCommentHandling
    AGORA.agoraUserCommentHandling = new agoraUserCommentHandling();

    // Set plugin preview to 'agora'
    ODE.pluginPreview = 'agora';

    // Handler for windows resize
    window.addEventListener("resize", function () {
    });

    // Handler for comment added
    $(window).on("comment_added", function(e){
        AGORA.onCommentAdded(e)
    });

    // Handle back button
    $(".agora_bookmark").click(function(){
        let location = ODE.ow_base_url + "aogra";
        window.location.href = location;
    });

    // Handle right menu
    $('.agora_button').click(function(){
        AGORA.handleRightMenu($(this).attr('i'));
    });

    // Handle click on send button (submit message)
    $("#agora_comment_send").click(function(){
        if(!AGORA.agoraJS.submit())
            OW.error(OW.getLanguageText('spodagora', 'empty_message'));
    });

    // Handle for sentiment button
    $("#agora_sentiment_button").click(function(){
        AGORA.onAgoraSentimentButton();
    });

    // Handler for document ready (init perfectScrollbar, resize page, init autogrow)
    $(document).ready(function () {
        AGORA.onDocumentReady();
    });

    //Handler datalet creator button
    $('#agora_controllet_button').click(function(){
        previewFloatBox = OW.ajaxFloatBox('ODE_CMP_Preview', {} , {top:'56px', width:'calc(100vw - 112px)', height:'calc(100vh - 112px)', iconClass: 'ow_ic_add', title: ''});
    });

    //Handler mySpace button
    $('#agora_myspace_button').click(function(){
        previewFloatBox = OW.ajaxFloatBox('SPODPR_CMP_PrivateRoomCardViewer', {data:['datalet']}, {top:'56px', width:'calc(100vw - 112px)', height:'calc(100vh - 112px)', iconClass: 'ow_ic_add', title: ''});
    });

    //Handler maplet button
    $('#agora_maplet_button').click(function(){
        previewFloatBox = OW.ajaxFloatBox('ODE_CMP_Preview', {component:'map-controllet'} , {top:'56px', width:'calc(100vw - 112px)', height:'calc(100vh - 112px)', iconClass: 'ow_ic_add', title: ''});
    });


    //Handler unreaded message section
    $('.agora_day_tab').click(function(e){
        AGORA.openDiv(e.currentTarget.id);
    });

    //Handle click on unread message
    $(".agora_unread_comment").click(function(e){
        AGORA.onClickUnreadComment(e);
    });

    // Handle preview
    $("#agora_preview_button").click(function () {
        AGORA.onPreviewButtonClick();
    });

    // Handle reply
    $(".agora_speech_reply").click(function (e) {
        AGORA.onReplyClick(e);
    });

    // Handle user notification toggle
    $("#user_notification_switch").on('click', function(e){
        AGORA.handleUserNotification(e);
    });

    // Handle search
    AGORA.handleSearchDOM();

    // Handle user comment
    $(".delete_comment").on('click', function (e) {
        AGORA.user_delete_comment(e);
    });

    $(".modify_comment").on('click', function(e){
        AGORA.user_edit_comment(e);
    });

    //File upload
    $("#agora_file_upload").change(AGORA.on_upload_file_change);
    $("#agora_load_button").on('click', function(e){
        AGORA.upload_image();
    });

    $(".close_preview").on('click', function(e)
    {
        AGORA.close_preview(e);
    });

    // Handler realtime notification (socket.io)
    AGORA.handleRealtimeNotification();
    // Init datalet graph
    try {
        AGORA.initDataletGraph();
    } catch(e) {
        console.log("ERROR initDataletGraph: " + e)
    }
    // Init user graph
    try {
        AGORA.initUserGraph();
    } catch(e) {
        console.log("ERROR initUserGraph: " + e)
    }
    // Init comment graph
    try {
        AGORA.initCommentGraph();
    } catch(e) {
        console.log("ERROR initCommentGraph: " + e)
    }
    // Init liquid sentiment
    try {
        AGORA.initSentimentLiquid();
    } catch(e) {
        console.log("ERROR initSentimentLiquid: " + e)
    }

    /* TEST */
    /*interval = setInterval(function(){
     AGORA.agoraUserCommentHandling.loadCommentPage(AGORA.roomId).then(function(data){
     if(!data)
     clearInterval(interval);

     console.log("load!!");
     AGORA.addComment(data, $("#agora_chat_container")[0]);
     });
     }, 1500);*/
    /* TEST */

};

AGORA.dataltet_preview_added = function(){

    $("#agora_datalet_preview_container").show();
    $("#agora_image_preview").hide();

};

AGORA.close_preview = function(e)
{
    //IMAGE PREVIEW
    $("#agora_image_preview").hide();
    $("#agora_image_preview img").remove();
    AGORA.agoraJS.set_attachment('');
    $("#agora_file_upload").val('');

    //DATALET PREVIEW
    ODE.reset();
    $("#agora_datalet_preview_container").hide();
    $("#agora_datalet_preview").empty();
};

AGORA.upload_image = function ()
{
    let agora_file_upload = $("#agora_file_upload");
    agora_file_upload.click();
};

AGORA.on_upload_file_change = function()
{
    let agora_image_preview = $("#agora_image_preview");
    let input_file = document.getElementById('agora_file_upload');

    if (input_file.files && input_file.files[0])
    {
        if(input_file.files[0].type.indexOf("image") < 0 && input_file.files[0].type.indexOf("pdf") < 0) {
            OW.error("Only image or pdf file");
            return;
        }

        if(input_file.files[0].size > 2000000) {
            OW.error("Max file size 2MB");
            return;
        }

        AGORA.agoraJS.set_attachment(input_file.files[0]);

        if(input_file.files[0].type.indexOf("image") !== -1 ) {
            let reader = new FileReader();

            reader.onload = function (e) {
                $("#agora_image_preview img").remove();
                agora_image_preview.append($('<img>').attr('src', e.target.result));
                agora_image_preview.show();
                $("#agora_datalet_preview_container").hide();
            };

            reader.readAsDataURL(input_file.files[0]);
        }else{
            $("#agora_image_preview img").remove();
            agora_image_preview.append($('<img>').attr('class', 'attach'));
            agora_image_preview.show();
            $("#agora_datalet_preview_container").hide();
        }
    }
};

AGORA.user_delete_comment = function (e)
{
    var comment = $(e.currentTarget).parents().eq(3)[0];
    var comment_id = comment.id.replace("comment_", "");

    AGORA.agoraUserCommentHandling.deleteComment(comment_id).then(function(data){
        if(data.result === 'ok')
            comment.remove();
    });
};

AGORA.user_edit_comment = function (e)
{
    var comment = $(e.currentTarget).parents().eq(3)[0];
    var comment_txt = $(comment).find(".agora_speech_text");
    var comment_id = comment.id.replace("comment_", "");

    $(comment_txt).html(`<input id="edit_${comment_id}" type="text" value="${$(comment_txt).clone().children().remove().end().text()}">`);

    $(`#edit_${comment_id}`).keyup(function(e){
        if(e.keyCode === 13)
        {
            AGORA.agoraUserCommentHandling.editComment(comment_id, e.currentTarget.value).then(function(data){
                if(data.result === 'ok')
                    $(e.currentTarget).parent().html(AGORA.string_handler($(e.currentTarget).val()));
            });
        }
    });
};

AGORA.handleSearchDOM = function ()
{
    var selectedUser = -1;

    // Handle user research (click)
    $("#agora_search_button").on('click', function () {
        AGORA.handleSearch($("#agora_search_input").val(), selectedUser);
        $("#agora_search_users").hide();
    });

    // Handle user research (return)
    $("#agora_search_input").keypress(function (e) {
        if (e.which == 13) {
            AGORA.handleSearch($("#agora_search_input").val(), selectedUser);
            $("#agora_search_users").hide();
        }
    });

    $("#agora_all_users").on('click', function () {
        $("#agora_search_users").toggle();
    });

    $(".agora_search_user").on('click', function (e) {
        $("#search_selected_user").attr("src", $(e.currentTarget).find("img").attr("src"));
        selectedUser = $(e.currentTarget).find(".ow_avatar").attr("id").replace("user_avatar_", "");
        $("#agora_search_users").toggle();
    });
};

AGORA.handleSearch = function (search_string, search_user)
{
    if(search_string.length < AGORA.searchStringLenght) {
        OW.error("Search string at last "+AGORA.searchStringLenght+" character");
        return;
    }

    AGORA.agoraSearchJS.handleSearch(search_string, search_user).then(function(data){
        // The search result structure is identical to the unread comment
        // so we can handle it with the same function
        $("#agora_search_results").html(data);
        $(".agora_unread_comment").click(function(e){
            AGORA.onClickUnreadComment(e);
        });
    });
};

AGORA.handleRightMenu = function(i)
{
    // $('#agora_right_header').text($($(".agora_button_title")[i]).text().toUpperCase());
    $('#agora_right_header').text($($(".agora_button_title")[i]).text());
    $('.agora_right_container').css("display", "none");
    $($('.agora_right_container')[i]).css("display", "block");

    $('.agora_button').removeClass("selected");
    $($('.agora_button')[i]).addClass("selected");
};

AGORA.handleUserNotification = function (e)
{
    AGORA.agoraUserNotification.handleUserNotification($(e.currentTarget).is(':checked'));
};

AGORA.onReplyClick = function (e)
{
    AGORA.agoraJS.set_level_up();
    AGORA.agoraJS.set_parentId($(e.currentTarget).parents().eq(2).attr('id'));
    AGORA.agoraJS.get_nested_comment().then(AGORA.levelUp).then();
};

AGORA.levelUp = function (data)
{
    var ancc = $("#agora_nested_chat_container");

    ancc.html(data);

    var anc = $("#agora_nested_comment");
    var ans = $("#agora_nested_speech_text");

    $("#agora_back").click(function () {
        AGORA.levelDown().then(function(){ancc.html("");});
    });

    anc.mouseover(function () {
        ans.removeClass("agora_nested_speech_text");
    });

    anc.mouseout(function () {
        ans.addClass("agora_nested_speech_text");
    });

    // Handle user comment
    $(".delete_comment").on('click', function (e) {
        AGORA.user_delete_comment(e);
    });
    $(".modify_comment").on('click', function(e){
        AGORA.user_edit_comment(e);
    });

    //Emoticonize
    $('.agora_speech_text').emoticonize();

    //Hashtag
    $('.agora_speech_text').highlightWord('#', 'hashtag');
    $('.agora_speech_text').highlightWord('@', 'mention');


    return AGORA.fadeToPromise($("#agora_chat_container")[0], ancc[0]);
};

AGORA.levelDown = function ()
{
    AGORA.agoraJS.set_parentId(AGORA.roomId);
    AGORA.agoraJS.set_level_down();
    return AGORA.fadeToPromise($("#agora_nested_chat_container")[0], $("#agora_chat_container")[0]);
};

AGORA.switchLevel = function (data)
{
    var _data = data;

    var anc = $("#agora_nested_comment");
    var ans = $("#agora_nested_speech_text");
    var ancc = $("#agora_nested_chat_container");

    return $(ancc).animate({
        opacity: 0
    }, 250, function () {
        ancc.html(_data);
        $("#agora_back").click(function () {
            AGORA.levelDown().then(function(){ancc.html("");});
        });

        anc.mouseover(function () {
            ans.removeClass("agora_nested_speech_text");
        });

        anc.mouseout(function () {
            ans.addClass("agora_nested_speech_text");
        });
    }).animate({
        opacity: 1
    }, 250).promise();

};

AGORA.onPreviewButtonClick = function ()
{
    var elem = $("#agora_datalet_preview");
    elem.toggle();
    try {
        var e = elem.children()[1];
        $(e).context.behavior.redraw();
    } catch(e) {}

    try {
        var e = elem.children()[0];
        $(e).context.behavior.redraw();
    } catch(e) {}
};

AGORA.initAgoraJS = function ()
{
    AGORA.agoraJS = new agoraJs();
    AGORA.agoraJS.init($("#agora_comment"),AGORA.roomId, AGORA.agora_comment_endpoint, AGORA.agora_nested_comment_endpoint);
    AGORA.agoraJS.set_string_handler(AGORA.string_handler);
};

AGORA.onCommentAdded = function (e)
{
    if(AGORA.realtimeAddedComment >= AGORA.maxRealtimeMessage)
        $("#agora_chat_container").children().first().remove();

    AGORA.scroll_to();

    var elem = $("#agora_datalet_placeholder_" + e.post_id);
    var parent_children = elem.parent().children()[0];
    $(parent_children).emoticonize();
    $('.agora_speech_text').highlightWord('#', 'hashtag');
    $('.agora_speech_text').highlightWord('@', 'mention');

    if(e.component != "") {
        elem.addClass("agora_fullsize_datalet " + e.component);
        $("#agora_datalet_preview_container").hide();
        ODE.reset();
    }

    $("#agora_image_preview img").remove();
    $("#agora_image_preview").hide();

    $(elem).parent().find(".agora_speech_reply").click(function (e) {
        AGORA.onReplyClick(e);
    });

    // Handle user comment
    $(".delete_comment").on('click', function (e) {
        AGORA.user_delete_comment(e);
    });

    $(".modify_comment").on('click', function(e){
        AGORA.user_edit_comment(e);
    });

};

AGORA.onAgoraSentimentButton = function()
{
    var sb = $("#agora_sentiment_button");

    switch(AGORA.agoraJS.get_sentiment())
    {
        case 0 :
            sb.css('background', '#4CAF50 url("/ow_static/plugins/agora/images/sentiment-satisfied.svg") no-repeat 0 0');
            AGORA.agoraJS.set_sentiment(1);
            break;

        case 1 :
            sb.css('background', '#F44336 url("/ow_static/plugins/agora/images/sentiment-dissatisfied.svg") no-repeat 0 0');
            AGORA.agoraJS.set_sentiment(2);
            break;

        default :
            sb.css('background', '#9E9E9E url("/ow_static/plugins/agora/images/sentiment-neutral.svg") no-repeat 0 0');
            AGORA.agoraJS.set_sentiment(0);
    }
};

AGORA.onDocumentReady = function ()
{
    $("#agora_left").perfectScrollbar();
    $("#agora_chat_container").perfectScrollbar();
    $("#agora_nested_chat_container").perfectScrollbar();
    $(".agora_unread_comments").perfectScrollbar();
    $(".agora_searched_comments").perfectScrollbar();
    $("#agora_datalet_graph_container").perfectScrollbar();/*ddr*/
    $("#agora_comment").autogrow();
    $("#agora_chat_container").on("scroll", AGORA.onChatContainerScroll);
    AGORA.scroll_to();

    // Emoticonize !!
    $('.agora_speech_text').emoticonize();
};

AGORA.addComment = function(data, target)
{
    return new Promise(function(res, rej)
    {
        if (data === '') {
            $("#loader").hide();
            return;
        }

        var first_child = $(target).children().first();
        var go_to = 0;

        $(target).prepend(data);

        first_child.prevAll().each(function () {
            //console.log(this.id + " " + $(this).outerHeight());
            go_to += $(this).outerHeight();
        });

        $(target).scrollTop(go_to);
        //$(target).perfectScrollbar();
        //$(target).perfectScrollbar('update');

        // Handle user comment
        $(".delete_comment").on('click', function (e) {
            AGORA.user_delete_comment(e);
        });
        $(".modify_comment").on('click', function (e) {
            AGORA.user_edit_comment(e);
        });

        // Handle reply
        $(".agora_speech_reply").click(function (e) {
            AGORA.onReplyClick(e);
        });

        //Emoticonize
        $('.agora_speech_text').emoticonize();

        //Hashtag
        $('.agora_speech_text').highlightWord('#', 'hashtag');
        $('.agora_speech_text').highlightWord('@', 'mention');

        AGORA.lastScrollPosition = 10e10;

        $("#loader").hide();

        res();
    });
};

AGORA.onChatContainerScroll = function(e)
{
    if(e.currentTarget.scrollTop < AGORA.lastScrollPosition)
    {
        AGORA.lastScrollPosition = document.getElementById('agora_chat_container').scrollTop;

        if(e.currentTarget.scrollTop < 20)
        {
            AGORA.lastScrollPosition = 0;
            $("#loader").show();
            AGORA.agoraUserCommentHandling.loadCommentPage(AGORA.roomId).then(function(data){
                AGORA.addComment(data, e.currentTarget);
            });
        }
    }
};

// UNREAD
AGORA.onClickUnreadComment = function (e)
{
    var id       = e.currentTarget.id.replace("unread_", "");
    var parentId = e.currentTarget.getAttribute("parent-id").match(/\d+/)[0];
    AGORA.goToComment(id, parentId);
};

AGORA.loadMissingComment = function(comment_dom_id, comment_id)
{
    return new Promise(function(res, rej){
        if($("#"+comment_dom_id).length)
            res();
        else {
            AGORA.agoraUserCommentHandling.loadCommentMissing(AGORA.roomId, comment_id).then(function(data){
                AGORA.addComment(data, $("#agora_chat_container")).then(res);
            });
        }
    });
};

// Go to comment
AGORA.goToComment = function (id, parentId)
{
    if(AGORA.agoraJS.get_parentId() == parentId || AGORA.agoraJS.get_parentId() == parentId)
    {
        // da livello 0 a 0 o stesso nested level
        AGORA.loadMissingComment(id, id.match(/\d+/)[0]).then(AGORA.highlightMessage.bind(null, id));
    }
    else if(parentId == AGORA.roomId && AGORA.agoraJS.get_parentId() != AGORA.roomId)
    {
        // da livello 1 a 0
        AGORA.levelDown().then(AGORA.highlightMessage.bind(null,id));
    }
    else if(AGORA.agoraJS.get_parentId() != parentId && parentId != AGORA.roomId && AGORA.agoraJS.get_parentId() != AGORA.roomId)
    {
        // da livello 1 a 1
        AGORA.agoraJS.set_parentId(parentId);
        AGORA.agoraJS.get_nested_comment().then(AGORA.switchLevel).then(AGORA.highlightMessage.bind(null,id));
    }
    else
    {
        // da livello 0 a 1
        AGORA.agoraJS.set_level_up();
        AGORA.agoraJS.set_parentId(parentId);
        AGORA.agoraJS.get_nested_comment().then(AGORA.levelUp).then(AGORA.highlightMessage.bind(null,id));
    }

};

AGORA.highlightMessage = function(id)
{
    var as  = $("#" + id + " .agora_speech");

    AGORA.scroll_to(id).then(function () {
        as.css("background-color", "#FFEB3B");

        setTimeout(
            function () {
                as.css("transition", "background-color 1s ease");
                as.css("background-color", "#EEEEEE");
            }, 100);

        setTimeout(
            function () {
                as.css("transition", "");
            }, 1100);
    });
};

AGORA.handleRealtimeNotification = function ()
{
    try {
        // Handle realtime communication
        var socket = io(window.location.origin, {path: "/realtime_notification"/*, transports: [ 'polling' ]*/});

        socket.emit('online_notification', {user_id: AGORA.user_id, room_id: AGORA.roomId, plugin: 'agora'});

        socket.on('online_notification_' + AGORA.roomId, function (data) {
            data.forEach(function (e) {
                $("#user_avatar_" + e).addClass("online");
            });
        });

        socket.on('offline_notification', function (id) {
            $("#user_avatar_" + id).removeClass("online");
        });

        socket.on('realtime_message_' + AGORA.roomId, function (data) {

            var target;

            // Increment reply count
            if(AGORA.roomId != data.parent_id)
            {
                var reply_el = $("#comment_" + data.parent_id).find('.agora_speech_reply');
                var reply_text = reply_el.html().replace(/([0-9]+)/g, function (match, tag, string) {
                    return ++match;
                });
                reply_el.html(reply_text);
            }

            //if (AGORA.user_id != data.user_id) {
            if (AGORA.user_id != data.user_id || !AGORA.agoraJS.is_sending()) {

                if (data.comment_level == 0) {
                    target = $("#agora_chat_container");
                    AGORA.realtimeAddedComment++;
                }else if(AGORA.agoraJS.get_parentId() == data.parent_id){
                    target = $("#agora_nested_chat_container");
                }else{
                    return;
                }

                let comment_class = (AGORA.user_id != data.user_id) ? 'agora_left_comment' : 'agora_right_comment';

                AGORA.agoraJS.add_rt_comment(
                    AGORA.agora_static_resource_url + 'JSSnippet/rt_comment.tpl',
                    [comment_class,
                        data.message_id,
                        (data.sentiment == 0 ? 'neutral ' : (data.sentiment == 1 ? 'satisfied ' : 'dissatisfied ')) + data.user_avatar_css,
                        data.user_display_name,
                        data.user_url,
                        data.user_avatar,
                        data.user_avatar_initial,
                        data.comment,
                        data.message_id,
                        data.dataletId,
                        data.user_display_name,
                        OW.getLanguageText('spodagora', 'c_just_now'),
                        OW.getLanguageText('spodagora', 'c_reply')+' (0)'],
                    {component: data.component, params: data.params, fields: data.fields, data: data.data},
                    data.message_id,
                    target
                );
            }
        });
    }catch(e){
        console.log(e);
    }
};

//scroll to element #id, if id empty scroll to bottom
AGORA.scroll_to = function (id)
{
    var st = AGORA.agoraJS.get_parentId() == AGORA.roomId ? $("#agora_chat_container") : $("#agora_nested_chat_container");

    if(id)
        st.scrollTop(st.scrollTop() + $("#" + id).position().top);
    else
        st.scrollTop(st.prop("scrollHeight"));

    return st.perfectScrollbar('update').promise();
};

AGORA.openDiv = function (tab_id)
{
    var h = Math.max(document.documentElement.clientHeight, window.innerHeight || 0) - 56 - 56 - 345;

    var tab_to_close = $(".agora_tab_opened")[0];
    var tab_to_open = $(".agora_unread_comments")[tab_id];

    if (AGORA.debounce && tab_to_open != tab_to_close) {
        AGORA.debounce = false;

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

                AGORA.debounce = true;
            }, 1000);
    }
};

AGORA.string_handler = function(string)
{
    //string = $('<div/>').text(string).html();
    string = string.replace(/\n/g, "<br/>");
    return string;
};

AGORA.fadeToPromise = function(from, to)
{
    $(to).show();

    $(from).animate({
        opacity: 0
    }, 250, function () {
        $(from).hide();
    });

    return $(to).delay(250).animate({
        opacity: 1
    }, 250).promise();
};

AGORA.initSentimentLiquid = function()
{
    // https://gist.github.com/luvaas/5563f33e90b166f32657116cb53afd05

    var w = $("#agora_right").width();
    $(".fillgauge").attr("width", w/2);
    $(".fillgauge").attr("height", w/2);


    var config1 = liquidFillGaugeDefaultSettings();
    config1.circleColor = "#4CAF50";//500
    config1.textColor = "#4CAF50";//500
    config1.waveTextColor = "#A5D6A7";//200
    config1.waveColor = "#C8E6C9";//100
    config1.circleThickness = 0.1;
    config1.textVertPosition = 0.5;
    config1.waveAnimateTime = 1000;
    // config1.waveRiseTime = 3000;
    var gauge1= loadLiquidFillGauge("fillgauge1", 0, config1);


    var config2 = liquidFillGaugeDefaultSettings();
    config2.circleColor = "#F44336";//500
    config2.textColor = "#F44336";//500
    config2.waveTextColor = "#EF9A9A";//200
    config2.waveColor = "#FFCDD2";//100
    config2.circleThickness = 0.1;
    config2.textVertPosition = 0.5;
    config2.waveAnimateTime = 1000;
    // config2.waveRiseTime = 3000;
    var gauge2= loadLiquidFillGauge("fillgauge2", 0, config2);

    $('#agora_button_sentiments').click(function(){
        gauge1.update(AGORA.sat_prctg);
        gauge2.update(AGORA.unsat_prctg);
    });

};

jQuery.fn.highlightWord = function(pat, _class) {
    function innerHighlight(node, pat) {
        var skip = 0;
        if (node.nodeType == 3) {
            var pos = node.data.toUpperCase().indexOf(pat);
            pos -= (node.data.substr(0, pos).toUpperCase().length - node.data.substr(0, pos).length);
            if (pos >= 0) {
                var spannode = document.createElement('span');
                spannode.className = _class;
                var middlebit = node.splitText(pos);

                /*ddr*/
                var len = middlebit.data.indexOf(' ');
                if(len == -1)
                    len = middlebit.data.length;
                len -= 1;
                var endbit = middlebit.splitText(pat.length + len);
                /*ddr*/

                // var endbit = middlebit.splitText(pat.length);
                var middleclone = middlebit.cloneNode(true);
                spannode.appendChild(middleclone);
                middlebit.parentNode.replaceChild(spannode, middlebit);
                skip = 1;
            }
        }
        else if (node.nodeType == 1 && node.childNodes && !/(script|style)/i.test(node.tagName)) {
            for (var i = 0; i < node.childNodes.length; ++i) {
                i += innerHighlight(node.childNodes[i], pat);
            }
        }
        return skip;
    }
    return this.length && pat && pat.length ? this.each(function() {
        innerHighlight(this, pat.toUpperCase());
    }) : this;
};