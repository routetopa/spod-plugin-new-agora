AGORA = {
    agoraJS:null,
    agoraUserNotification:null,
    agoraSearchJS:null,
    agoraUserCommentHandling:null,
    debounce:true,
    searchStringLenght:3
};

AGORA.init = function ()
{
    $('.agora_speech_text').highlightHashtag('#');

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

    // Handle right menu
    $('.agora_button').click(function(){
        AGORA.handleRightMenu($(this).attr('i'));
    });

    // Handle click on send button (submit message)
    $("#agora_comment_send").click(function(){
        if(!AGORA.agoraJS.submit())
            OW.error("Messaggio vuoto");
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
        previewFloatBox = OW.ajaxFloatBox('SPODPR_CMP_PrivateRoomCardViewer', {} , {top:'56px', width:'calc(100vw - 112px)', height:'calc(100vh - 112px)', iconClass: 'ow_ic_add', title: ''});
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

    $(".agora_right_comment .agora_speech_text").on('dblclick', function(e){
        AGORA.user_edit_comment(e);
    });

    // Handler realtime notification (socket.io)
    AGORA.handleRealtimeNotification();
    // Init datalet graph
    AGORA.initDataletGraph();
    // Init user graph
    AGORA.initUserGraph();
    // Init liquid sentiment
    AGORA.initSentimentLiquid();
};

AGORA.user_delete_comment = function (e)
{
    var comment = $(e.currentTarget).parent().parent().parent()[0];
    var comment_id = comment.id.replace("comment_", "");

    AGORA.agoraUserCommentHandling.deleteComment(comment_id).then(function(data){
        if(data.result == 'ok')
            comment.remove();
    });
};

AGORA.user_edit_comment = function (e)
{
    $(e.currentTarget).off('dblclick');
    var comment = $(e.currentTarget).parent().parent()[0];
    var comment_id = comment.id.replace("comment_", "");
    $(e.currentTarget).html(`<input id="edit_${comment_id}" type="text" value="${$(e.currentTarget).clone().children().remove().end().text()}">`);

    $(`#edit_${comment_id}`).keyup(function(e){
        if(e.keyCode === 13)
        {
            AGORA.agoraUserCommentHandling.editComment(comment_id, e.currentTarget.value).then(function(data){
                if(data.result == 'ok')
                    $(e.currentTarget).parent().html($(e.currentTarget).val());
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

    //Emoticonize
    $('.agora_speech_text').emoticonize();

    //Hashtag
    $('.agora_speech_text').highlightHashtag('#');

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
    var e = elem.children()[1];
    $(e).context.behavior.redraw();
};

AGORA.initAgoraJS = function ()
{
    AGORA.agoraJS = new agoraJs();
    AGORA.agoraJS.init($("#agora_comment"),AGORA.roomId, AGORA.agora_comment_endpoint, AGORA.agora_nested_comment_endpoint);
    AGORA.agoraJS.set_string_handler(AGORA.string_handler);
};

AGORA.onCommentAdded = function (e)
{
    AGORA.scroll_to();

    var elem = $("#agora_datalet_placeholder_" + e.post_id);
    var parent_children = elem.parent().children()[0];
    $(parent_children).emoticonize();
    $(parent_children).highlightHashtag('#');

    if(e.component != "") {
        elem.addClass("agora_fullsize_datalet " + e.component);
        $("#agora_preview_button").hide();
        ODE.reset();
    }

    $(elem).parent().find(".agora_speech_reply").click(function (e) {
        AGORA.onReplyClick(e);
    });

    // Handle user comment
    $(".delete_comment").on('click', function (e) {
        AGORA.user_delete_comment(e);
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
    $('#agora_left').perfectScrollbar();
    $('#agora_chat_container').perfectScrollbar();
    $('#agora_nested_chat_container').perfectScrollbar();
    $(".agora_unread_comments").perfectScrollbar();
    $(".agora_searched_comments").perfectScrollbar();
    $("#agora_datalet_graph_container").perfectScrollbar();/*ddr*/
    $('#agora_comment').autogrow();
    AGORA.scroll_to();

    // Emoticonize !!
    $('.agora_speech_text').emoticonize();
};

// UNREAD
AGORA.onClickUnreadComment = function (e)
{
    var id       = e.currentTarget.id.replace("unread_", "");
    var parentId = e.currentTarget.getAttribute("parent-id").match(/\d+/)[0];
    AGORA.goToComment(id, parentId);
};

// Go to comment
AGORA.goToComment = function (id, parentId)
{
    if(AGORA.agoraJS.get_parentId() == parentId || AGORA.agoraJS.get_parentId() == parentId)
    {
        // da livello 0 a 0 o stesso nested level
        AGORA.highlightMessage(id);
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
        var socket = io(window.location.origin + ":3000");

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

            if (AGORA.user_id != data.user_id) {

                if (data.comment_level == 0) {
                    target = $("#agora_chat_container");
                }else if(AGORA.agoraJS.get_parentId() == data.parent_id){
                    target = $("#agora_nested_chat_container");
                }else{
                    return;
                }

                AGORA.agoraJS.add_rt_comment(
                    AGORA.agora_static_resource_url + 'JSSnippet/rt_comment.tpl',
                    [data.message_id,
                        (data.sentiment == 0 ? 'neutral' : (data.sentiment == 1 ? 'satisfied' : 'dissatisfied')),
                        data.user_display_name,
                        data.user_url,
                        data.user_avatar,
                        data.comment,
                        data.message_id,
                        data.user_display_name,
                        OW.getLanguageText('spodagora', 'c_just_now'),
                        OW.getLanguageText('spodagora', 'c_reply')+' (0)'],
                    {component: data.component, params: data.params, fields: data.fields, data: ''},
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
    string = $('<div/>').text(string).html();
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

// Init datalet graph
AGORA.initDataletGraph = function()
{
    if (!AGORA.datalet_graph)
        return;

    var JSON_dataletGraph = JSON.parse("[" + AGORA.datalet_graph.substring(0, AGORA.datalet_graph.length - 1) + "]");

    var nodes = [];
    var links = [];

    var n = JSON_dataletGraph.length;

    var w = $("#agora_right").width();
    var h = 120 + n * 80;

    var datasets = [];
    for (var i in JSON_dataletGraph)
    {
        nodes.push({x: 80, y: 120+i*80, tooltip: (JSON_dataletGraph[i]["title"] != "" ? JSON_dataletGraph[i]["title"] : JSON_dataletGraph[i]["comment"]), type: "datalet", commentId: JSON_dataletGraph[i]["comment_id"], parentId: JSON_dataletGraph[i]["parent_id"]});

        if(datasets.indexOf(JSON_dataletGraph[i]["url"]) == -1)
            datasets.push(JSON_dataletGraph[i]["url"]);
    }

    datasets = datasets.reverse();
    for (var j in datasets)
        nodes.unshift({x: w-80, y: 120 + j*n*80/datasets.length, tooltip: datasets[j], type: "dataset"});

    for (var i in JSON_dataletGraph)
        links.push({source: datasets.indexOf(JSON_dataletGraph[i]["url"]), target: datasets.length + parseInt(i)});

    $("#svg_datalet_graph").attr("height", h + 40);

    var svg = d3.select("#svg_datalet_graph"),
        g = svg.append("g");

    var simulation = d3.forceSimulation(nodes)
        .force("charge", d3.forceManyBody().strength(-80))
        .force("link", d3.forceLink(links).distance(20).strength(1).iterations(10))
        .force("x", d3.forceX())
        .force("y", d3.forceY())
        .stop();

    var node_type = d3.scaleOrdinal(["dataset", "datalet"]);/*in json................*/

    var loading = svg.append("text")
        .attr("dx", "280")
        .attr("dy", "298")
        .attr("font-size", 16)
        .text("Loading...");

    // Setup the tool tip.  Note that this is just one example, and that many styling options are available.
    // See original documentation for more details on styling: http://labratrevenge.com/d3-tip/
    var datalet_tip = d3.tip()
        .attr("class", "d3-tip")
        .offset([-4, 0])
        .html(function(d) { return d; });

    var dataset_tip = d3.tip()
        .attr("class", "d3-tip")
        .offset([0, -4])
        .direction('w')
        .html(function(d) { return d; });

    // Use a timeout to allow the rest of the page to load first.
    d3.timeout(function() {
        loading.remove();

        svg.call(datalet_tip);
        svg.call(dataset_tip);

        g.append("g")
            .append("defs")
            .append("pattern")
            .attr("id", "dataset")
            .attr("patternUnits", "objectBoundingBox")
            .attr("height", "1")
            .attr("width", "1")
            .append("image")
            .attr("height", "32")
            .attr("width", "32")
            .attr("xlink:href", "/ow_static/plugins/agora/images/graph-dataset-node.svg");

        g.append("g")
            .append("defs")
            .append("pattern")
            .attr("id", "dataset_hover")
            .attr("patternUnits", "objectBoundingBox")
            .attr("height", "1")
            .attr("width", "1")
            .append("image")
            .attr("height", "64")
            .attr("width", "64")
            .attr("xlink:href", "/ow_static/plugins/agora/images/graph-dataset-node.svg");

        g.append("g")
            .append("defs")
            .append("pattern")
            .attr("id", "datalet")
            .attr("patternUnits", "objectBoundingBox")
            .attr("height", "1")
            .attr("width", "1")
            .append("image")
            .attr("height", "32")
            .attr("width", "32")
            .attr("xlink:href", "/ow_static/plugins/agora/images/graph-datalet-node.svg");

        g.append("g")
            .append("defs")
            .append("pattern")
            .attr("id", "datalet_hover")
            .attr("patternUnits", "objectBoundingBox")
            .attr("height", "1")
            .attr("width", "1")
            .append("image")
            .attr("height", "64")
            .attr("width", "64")
            .attr("xlink:href", "/ow_static/plugins/agora/images/graph-datalet-node.svg");

        //HEADER
        g.append("g")
            .append("text")
            .attr("fill", "white")
            .attr("x", 40 + (w-160)/4)
            .attr("y", 40)
            .text(OW.getLanguageText('spodagora', 'g_datalets'));

        g.append("g")
            .append("text")
            .attr("fill", "white")
            .attr("x", 40 + (w-160)/4*3)
            .attr("y", 40)
            .text(OW.getLanguageText('spodagora', 'g_datasets'));


        //CUT LINE
        g.append("g")
            .append("line")
            .attr("class", "cut_line")
            .attr("x1", w/2)
            .attr("y1", 40)
            .attr("x2", w/2)
            .attr("y2", h)
            .attr("marker-end", "url(#triangle)");

        g.append("g")
            .append("text")
            .attr("fill", "white")
            .attr("x", w/2 + 4)
            .attr("y", h-60)
            .attr("transform", "rotate(90, " + (w/2 + 4) + ", " + (h-60) + ")")
            .text(OW.getLanguageText('spodagora', 'g_time'));

        svg.append("svg:defs").append("svg:marker")
            .attr("id", "triangle")
            .attr("refX", 3)
            .attr("refY", 6)
            .attr("markerWidth", 12)
            .attr("markerHeight", 12)
            .attr("orient", "auto")
            .append("path")
            .attr("d", "M 0 0 12 6 0 12 3 6")
            .style("fill", "white");

        //LINKS
        g.append("g")
            .selectAll("line")
            .data(links)
            .enter().append("line")
            .attr("class", "links")
            .attr("x1", function (d) {
                return d.source.x;
            })
            .attr("y1", function (d) {
                return d.source.y;
            })
            .attr("x2", function (d) {
                return d.target.x;
            })
            .attr("y2", function (d) {
                return d.target.y;
            });

        //NODES
        g.append("g")
            .selectAll("circle")
            .data(nodes)
            .enter().append("circle")
            .attr("class", function (d) {
                return "nodes " + d.type;
            })
            .attr("ci", function (d) {
                return d.index;
            })
            .attr("cx", function (d) {
                return d.x;
            })
            .attr("cy", function (d) {
                return d.y;
            })
            .attr("r", 16)

            .on("mouseover", function (d) {
                var node = this;

                highlightsPath(node, "highlighted", true);

                if(d.type == "datalet")
                    datalet_tip.show(d3.select(node).data()[0].tooltip);
                else if(d.type == "dataset")
                    dataset_tip.show(d3.select(node).data()[0].tooltip);
            })
            .on("mouseout", function () {
                var node = this;
                highlightsPath(node, "highlighted", false);
                datalet_tip.hide();
                dataset_tip.hide();
            })
            .on("click", function () {
                var node = this;

                var flag = true;
                if (d3.select(node).attr("class").indexOf("selected") > -1)
                    flag = false;

                // highlightsPath(node, "selected", flag);
                // tool_tip.hide();

                var commentId = d3.select(node).data()[0].commentId;
                var parentId = d3.select(node).data()[0].parentId;
                if(commentId)
                    AGORA.goToComment("comment_" + commentId, parentId);
                else
                    highlightsPath(node, "selected", flag);
            });

    });

    var highlightsPath = function(node, cssClass, flag) {
        var classes;

        var links = [].slice.call(d3.selectAll(".links")._groups[0]);
        var linksArray = links.filter(function(l){
            return d3.select(l).data()[0].source.index == d3.select(node).data()[0].index || d3.select(l).data()[0].target.index == d3.select(node).data()[0].index;
        });

        var nodes = [].slice.call(d3.selectAll(".nodes")._groups[0]);
        var nodesArray = nodes.filter(function(n){
            for(var l of linksArray)
                if(d3.select(l).data()[0].target.index == d3.select(n).data()[0].index || d3.select(l).data()[0].source.index == d3.select(n).data()[0].index)
                    return true;
            return false;
        });

        if(flag) {
            classes = d3.selectAll(linksArray).attr("class");
            classes += " " + cssClass;
            d3.selectAll(linksArray).attr("class", classes);

            for(var n of nodesArray) {
                classes = d3.select(n).attr("class");
                classes += " " + cssClass;
                d3.select(n).attr("class", classes);
            }
        }
        else {
            classes = d3.selectAll(linksArray).attr("class");
            classes = classes.replace(" " + cssClass, "");
            d3.selectAll(linksArray).attr("class", classes);

            for(var n of nodesArray) {
                classes = d3.select(n).attr("class");
                classes = classes.replace(" " + cssClass, "");
                d3.select(n).attr("class", classes);
            }
        }
    }
};

// Init user graph
AGORA.initUserGraph = function()
{
    var u_nodes = [];
    var u_links = [];

    var w = $("#agora_right").width();
    // var h = 120 + n * 80;
    var h = w;
    var r = w*4/10;
    var n = Object.keys(AGORA.users_avatar).length;

    var i = 0;
    for (var user in AGORA.users_avatar) {

        u_nodes.push({
            x: w/2 + r * Math.cos((360/n*i) * Math.PI / 180),
            y: h/2 + r * Math.sin((360/n*i) * Math.PI / 180),
            tooltip: AGORA.users_avatar[user].title,
            type: "user",
            url: AGORA.users_avatar[user].url,
            fill: AGORA.users_avatar[user].src,
            id: AGORA.users_avatar[user].userId
        });

        i++;
    }

    for (var i in AGORA.user_friendship)
        u_links.push({source: Object.keys(AGORA.users_avatar).indexOf(AGORA.user_friendship[i]["userId"]), target: Object.keys(AGORA.users_avatar).indexOf(AGORA.user_friendship[i]["friendId"])});

    Object.keys(AGORA.users_avatar).indexOf("2");

    $("#svg_user_graph").attr("height", h);

    var svg = d3.select("#svg_user_graph"),
        g = svg.append("g");

    var simulation = d3.forceSimulation(u_nodes)
        .force("charge", d3.forceManyBody().strength(-80))
        .force("link", d3.forceLink(u_links).distance(20).strength(1).iterations(10))
        .force("x", d3.forceX())
        .force("y", d3.forceY())
        .stop();

    var loading = svg.append("text")
        .attr("dx", "280")
        .attr("dy", "298")
        .attr("font-size", 16)
        .text("Loading...");

    // Setup the tool tip.  Note that this is just one example, and that many styling options are available.
    // See original documentation for more details on styling: http://labratrevenge.com/d3-tip/
    var user_tip = d3.tip()
        .attr("class", "d3-tip")
        .offset([-12, 0])
        .html(function(d) { return d; });

    // Use a timeout to allow the rest of the page to load first.
    d3.timeout(function() {
        loading.remove();

        svg.call(user_tip);


        for (var user in AGORA.users_avatar) {
            g.append("g")
                .append("defs")
                .append("pattern")
                .attr("id", AGORA.users_avatar[user].userId)
                .attr("patternUnits", "objectBoundingBox")
                .attr("height", "1")
                .attr("width", "1")
                .append("image")
                .attr("height", "64")
                .attr("width", "64")
                .attr("xlink:href", AGORA.users_avatar[user].src);
        }

        //LINKS
        g.append("g")
            .selectAll("line")
            .data(u_links)
            .enter().append("line")
            .attr("class", "u_links")
            .attr("x1", function (d) {
                return d.source.x;
            })
            .attr("y1", function (d) {
                return d.source.y;
            })
            .attr("x2", function (d) {
                return d.target.x;
            })
            .attr("y2", function (d) {
                return d.target.y;
            });

        //NODES
        g.append("g")
            .selectAll("circle")
            .data(u_nodes)
            .enter().append("circle")
            .attr("class", function (d) {
                return "u_nodes " + d.type;
            })
            .attr("ci", function (d) {
                return d.index;
            })
            .attr("cx", function (d) {
                return d.x;
            })
            .attr("cy", function (d) {
                return d.y;
            })
            .attr("fill", function () {
                var node = this;
                return 'url("#' + d3.select(node).data()[0].id + '")';
            })
            .attr("r", 32)

            .on("mouseover", function () {
                var node = this;
                highlightsPath(node, "user_highlighted", true);
                user_tip.show(d3.select(node).data()[0].tooltip);
            })
            .on("mouseout", function () {
                var node = this;
                highlightsPath(node, "user_highlighted", false);
                user_tip.hide();
            })
            .on("click", function (d) {
                window.open(d.url, "_blank")
            });

    });

    var highlightsPath = function(node, cssClass, flag) {
        var classes;

        var u_links = [].slice.call(d3.selectAll(".u_links")._groups[0]);
        var linksArray = u_links.filter(function(l){
            return d3.select(l).data()[0].source.index == d3.select(node).data()[0].index || d3.select(l).data()[0].target.index == d3.select(node).data()[0].index;
        });

        var u_nodes = [].slice.call(d3.selectAll(".u_nodes")._groups[0]);
        var nodesArray = u_nodes.filter(function(n){
            for(var l of linksArray)
                if(d3.select(l).data()[0].target.index == d3.select(n).data()[0].index || d3.select(l).data()[0].source.index == d3.select(n).data()[0].index)
                    return true;
            return false;
        });

        if(flag) {
            classes = d3.selectAll(linksArray).attr("class");
            classes += " " + cssClass;
            d3.selectAll(linksArray).attr("class", classes);

            for(var n of nodesArray) {
                classes = d3.select(n).attr("class");
                classes += " " + cssClass;
                d3.select(n).attr("class", classes);
            }
        }
        else {
            classes = d3.selectAll(linksArray).attr("class");
            classes = classes.replace(" " + cssClass, "");
            d3.selectAll(linksArray).attr("class", classes);

            for(var n of nodesArray) {
                classes = d3.select(n).attr("class");
                classes = classes.replace(" " + cssClass, "");
                d3.select(n).attr("class", classes);
            }
        }
    }
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

jQuery.fn.highlightHashtag = function(pat) {
    function innerHighlight(node, pat) {
        var skip = 0;
        if (node.nodeType == 3) {
            var pos = node.data.toUpperCase().indexOf(pat);
            pos -= (node.data.substr(0, pos).toUpperCase().length - node.data.substr(0, pos).length);
            if (pos >= 0) {
                var spannode = document.createElement('span');
                spannode.className = 'hashtag';
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