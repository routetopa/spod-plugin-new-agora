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

    var init = function(elem, entityId, endpoint, endpoint_nested) {
        _elem = elem;
        _entityId = entityId;
        _endpoint = endpoint;
        _endpoint_nested = endpoint_nested;
        _level = 0;
        _parentId = _entityId;
        _sentiment = 0;
        _stringHandler = null;

        _agoraCommentJS = new agoraCommentJS();
        _elem.keyup(return_handler);
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

    var return_handler = function (e) {
        var key = e.which || e.keyCode;
        if (key === 13 && !e.shiftKey ) { // 13 is enter
            handle_message(_stringHandler ? _stringHandler(_elem.val()) : _elem.val());
        }
    };

    var submit = function () {
        if(_elem.val() == "") return false;
        handle_message(_stringHandler ? _stringHandler(_elem.val()) : _elem.val());
        return true;
    };

    var handle_message = function(message) {
        _message = message;

        var send_data = {
            comment: _message,
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
                _agoraCommentJS.addComment(_level == 0 ? $("#agora_chat_container") : $("#agora_nested_chat_container"),
                                       AGORA.agora_static_resource_url + 'JSSnippet/comment.tpl',
                                       [raw_data.post_id, (_sentiment == 0 ? 'neutral' : (_sentiment == 1 ?'satisfied' : 'dissatisfied')),AGORA.username, AGORA.user_url,AGORA.user_avatar_src,_message,raw_data.post_id,AGORA.username,'just now','0'],
                                       raw_data.post_id, ODE.dataletParameters
                );

            }else{
                console.log("Error on comment add");
            }

            _elem.val("");

        } catch (e){
            console.log("Error in on_request_success");
        }
    };

    var add_rt_comment = function(target, snippet_url, snippet_data, post_id, datalet){
        _agoraCommentJS.addComment(target, snippet_url, snippet_data, post_id, datalet);
    };

    var on_request_error = function( XMLHttpRequest, textStatus, errorThrown ){
        OW.error(textStatus);
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

function agoraCommentJS(){}

agoraCommentJS.prototype = (function () {
    return {
        construct: agoraCommentJS,

        // TODO cache the snippet
        addComment : function (target, snippet_url, snippet_data, post_id, datalet)
        {
            $.get(snippet_url, function(data)
            {
                var re = /{\d}/g;
                var index = 0;

                var k = data.replace(re, function (match, tag, string) {
                    return snippet_data[index++];
                });

                $(target).append(k);

                if(datalet.component != "")
                {
                    ODE.loadDatalet(datalet.component,
                                    JSON.parse(datalet.params),
                                    JSON.parse("["+datalet.fields+"]"),
                                    datalet.data,
                                    "agora_datalet_placeholder_" + post_id);
                }

                $(window).trigger({type:"comment_added",
                                   post_id:post_id,
                                   component:datalet.component});
            });
        },

        getNestedComment : function (entity_id, parent_id, level, endpoint, placeholder) {
            return $.ajax({
                type: 'POST',
                url : endpoint,
                data: {entity_id:entity_id, parent_id:parent_id, level:level},
                dataType : 'TEXT'
            });
        }
    }
})();