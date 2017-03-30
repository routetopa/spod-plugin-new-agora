function agoraJs() {};

agoraJs.prototype = (function(){

    var _elem;
    var _entityId;
    var _endpoint;
    var _endpoint_nested;
    var _level;
    var _parentId;
    var _sentiment;
    var _message;
    var _agoraCommentJS;
    var _stringHandler;
    var _processedUrl;
    var _preview;

    var init = function(elem, entityId, endpoint, endpoint_nested) {
        _elem = elem;
        _entityId = entityId;
        _endpoint = endpoint;
        _endpoint_nested = endpoint_nested;
        _level = 0;
        _parentId = _entityId;
        _sentiment = 0;
        _stringHandler = null;

        _processedUrl = '';
        _preview = '';

        _agoraCommentJS = new agoraCommentJS();
        _elem.keydown(keydown_handler);
        _elem.keyup(debounce(keyup_handler, 500));
    };

    var set_string_handler = function(stringHandler){
        _stringHandler = stringHandler;
    };

    var set_parentId = function(parentId){
        if(isNaN(parentId))
            _parentId =  parentId.match(/\d+/)[0];
        else
            _parentId = parentId;
    };

    var get_parentId = function(){
        return _parentId;
    };

    var set_sentiment = function(sentiment){
        _sentiment = sentiment;
    };

    var set_level_up = function () {
        _level = _level + 1;
    };

    var set_level_down = function () {
        _level = _level - 1;
    };

    var get_sentiment = function () {
        return _sentiment;
    };

    var check_if_link = function(str)
    {
        if(str.length == 0) return null;

        var source = (str || '').toString();
        var matchArray;

        // Regular expression to find FTP, HTTP(S) and email URLs.
        var regexToken = /(((ftp|https?):\/\/)[\-\w@:%_\+.~#?,&\/\/=]+)|((mailto:)?[_.\w-]+@([\w][\w\-]+\.)+[a-zA-Z]{2,3})/g;

        // Iterate through any URLs in the text.
        if ( (matchArray = regexToken.exec( source )) !== null )
        {
            return matchArray[0];
        }

        return null;
    };

    var keyup_handler = function(e) {

        if( (_elem.val().length != 0) && (url = check_if_link(_elem.val())) !== null && url != _processedUrl)
        {
            //TODO check if url already processed
            _agoraCommentJS.getSiteMetaTags(url).then(function(data){

                _processedUrl = url;

                if(data.image) {
                    var snippet_template = AGORA.agora_static_resource_url + 'JSSnippet/site_preview.tpl';
                    var snippet_data = [data.url, data.image, data.url, data.title, data.description, data.site_name];
                }
                else
                {
                    var snippet_template = AGORA.agora_static_resource_url + 'JSSnippet/site_preview_text.tpl';
                    var snippet_data = [data.url, data.title ? data.title : "link", data.description ? data.description : "", data.site_name];
                }

                _agoraCommentJS.getSnippet(snippet_template).then(function (snippet) {
                    _preview = fill_snippet(snippet, snippet_data);
                    $("#agora_datalet_preview").html(_preview);
                    $("#agora_datalet_preview").show();
                });
            });
        }
    };

    var keydown_handler = function (e) {
        var key = e.which || e.keyCode;
        if (key === 13 && !e.shiftKey ) { // 13 is enter
            e.preventDefault();
            if(_elem.val() == "") return false;
            handle_message(_elem.val());
        }
    };

    var submit = function () {
        if(_elem.val() == "") return false;
        handle_message(_elem.val());
        return true;
    };

    var handle_message = function(message) {

        _message = message;

        var send_data = {
            comment: _message,
            preview: _preview,
            entityId: _entityId,
            parentId: _parentId,
            level: _level,
            sentiment: _sentiment,
            datalet: ODE.dataletParameters,
            plugin: ODE.pluginPreview,
            username: AGORA.username,
            user_url: AGORA.user_url,
            user_avatar_src: AGORA.user_avatar_src
        };

        $.ajax({
            type: 'POST',
            url : _endpoint,
            data: send_data,
            dataType : 'JSON',
            success : on_request_success,
            error: on_request_error
        });
    };

    var on_request_success = function(raw_data){
        try
        {
            if(raw_data.result == "ok")
            {
                var target           = (_level === 0) ? $("#agora_chat_container") : $("#agora_nested_chat_container");
                var snippet_url      = (_level === 0) ? AGORA.agora_static_resource_url + 'JSSnippet/comment.tpl' : AGORA.agora_static_resource_url + 'JSSnippet/nested_comment.tpl';
                var sentiment        = (_sentiment === 0 ? 'neutral' : (_sentiment === 1 ?'satisfied' : 'dissatisfied'));
                var snippet_data     = (_level === 0) ? [raw_data.post_id, sentiment, AGORA.username, AGORA.user_url, AGORA.user_avatar_src, (_stringHandler(_message) + _preview), raw_data.post_id, AGORA.username, OW.getLanguageText('spodagora', 'c_just_now'), OW.getLanguageText('spodagora', 'c_reply')+' (0)', OW.getLanguageText('spodagora', 't_delete'), OW.getLanguageText('spodagora', 't_modify')] :
                                                        [raw_data.post_id, sentiment, AGORA.username, AGORA.user_url, AGORA.user_avatar_src, (_stringHandler(_message) + _preview), raw_data.post_id, AGORA.username, OW.getLanguageText('spodagora', 'c_just_now'), OW.getLanguageText('spodagora', 't_delete'), OW.getLanguageText('spodagora', 't_modify')];
                var datalet          = ODE.dataletParameters;
                var post_id          = raw_data.post_id;

                append_comment(snippet_url, snippet_data, datalet, post_id, target).then(function(){
                    _processedUrl = '';
                    _preview = '';
                    $("#agora_datalet_preview").hide()
                });

            }else{
                console.log("Error on comment add");
            }

            _elem.val("");
            //Simulate canc in order to shrink textarea
            _elem.trigger({type:"keyup", ctrlKey:false, which:46});

        } catch (e){
            console.log("Error on on_request_success");
        }
    };

    var add_rt_comment = function(snippet_url, snippet_data, datalet, post_id, target){
        append_comment(snippet_url, snippet_data, datalet, post_id, target);
    };

    var append_comment = function(snippet_template, snippet_data, datalet, post_id, target)
    {
        return _agoraCommentJS.getSnippet(snippet_template).then(function(snippet){

            return new Promise(function(res, rej) {

                $(target).append(fill_snippet(snippet, snippet_data));

                if (datalet.component != "") {
                    ODE.loadDatalet(datalet.component,
                        JSON.parse(datalet.params),
                        JSON.parse("[" + datalet.fields + "]"),
                        datalet.data,
                        "agora_datalet_placeholder_" + post_id);
                }

                $(window).trigger({
                    type: "comment_added",
                    post_id: post_id,
                    component: datalet.component
                });

                res();
            });
        });
    };

    var on_request_error = function( XMLHttpRequest, textStatus, errorThrown ){
        OW.error(textStatus);
    };

    var fill_snippet = function(snippet, snippet_data)
    {
        var re = /{[0-9]+}/g;
        var index = 0;

        var k = snippet.replace(re, function (match, tag, string) {
            return snippet_data[index++];
        });

        return k;
    };

    var debounce = function(f, debounce)
    {
        var timeout;
        return function()
        {
            //var args = arguments;
            if(timeout)
                clearTimeout(timeout);
            timeout = setTimeout(f.bind(null, arguments), debounce)
        }
    };

    // PUBLIC METHOD
    return {
        construct : agoraJs,

        init : function (elem, entityId, endpoint, endpoint_nested) {
            init(elem, entityId, endpoint, endpoint_nested);
        },

        submit : function () {
            return submit();
        },

        set_sentiment : function (sentiment) {
            set_sentiment(sentiment);
        },

        get_sentiment : function () {
           return get_sentiment();
        },

        set_parentId : function (parentId) {
            set_parentId(parentId);
        },

        get_parentId : function () {
            return get_parentId();
        },

        set_level_up : function () {
            set_level_up();
        },

        set_level_down : function () {
            set_level_down();
        },

        set_string_handler : function(stringHandler){
            set_string_handler(stringHandler);
        },

        add_rt_comment : function (target, snippet_url, snippet_data, post_id, datalet) {
            add_rt_comment(target, snippet_url, snippet_data, post_id, datalet);
        },

        get_nested_comment : function () {
            return _agoraCommentJS.getNestedComment(_entityId, _parentId, _level, _endpoint_nested);
        }
    };

})();

function agoraCommentJS(){
    this._snippetCache = {};
}

agoraCommentJS.prototype = (function () {
    return {
        construct: agoraCommentJS,

        getSnippet : function (snippet_url) {

            var cache = this._snippetCache;

            return new Promise(function(res, rej){
                if(!cache[snippet_url]) {
                    $.get(snippet_url, function (data) {
                        cache[snippet_url] = data;
                        res(cache[snippet_url]);
                    });
                }else{
                    res(cache[snippet_url]);
                }
            });
        },

        getNestedComment : function (entity_id, parent_id, level, endpoint) {
            return $.ajax({
                type: 'POST',
                url : endpoint,
                data: {entity_id:entity_id, parent_id:parent_id, level:level},
                dataType : 'TEXT'
            });
        },

        getSiteMetaTags : function (url) {
            return $.ajax({
                type: 'POST',
                url : AGORA.get_site_tag_endpoint,
                data: {url:url},
                dataType : 'JSON'
            });
        }
    }
})();

function agoraUserNotificationJS(){}

agoraUserNotificationJS.prototype = (function(){
    return {
        construct:agoraUserNotificationJS,

        handleUserNotification: function(val)
        {
            $.ajax({
                type: 'POST',
                url : AGORA.user_notification_url,
                data: {roomId:AGORA.roomId, addUserNotification:val},
                dataType : 'JSON'
            });
        }
    }
})();

function agoraSearchJS(){}

agoraSearchJS.prototype = (function () {
    return {
        construct:agoraSearchJS,

        handleSearch: function (searchString, searchUser) {
            return $.ajax({
                type: 'POST',
                url : AGORA.search_url,
                data: {entity_id:AGORA.roomId, search_string:searchString, user_id:searchUser},
                dataType : 'TEXT'
            });
        }
    }
})();

function agoraUserCommentHandling(){}

agoraUserCommentHandling.prototype = (function(){

    return {
        construct:agoraUserCommentHandling,

        deleteComment: function(comment_id) {
            return $.ajax({
                type: 'POST',
                url : AGORA.delete_user_comment_endpoint,
                data: {commentId:comment_id},
                dataType : 'JSON'
            });
        },

        editComment: function(comment_id, comment) {
            return $.ajax({
                type: 'POST',
                url : AGORA.edit_user_comment_endpoint,
                data: {commentId:comment_id, comment:comment},
                dataType : 'JSON'
            });
        }

    }

})();