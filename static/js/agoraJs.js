function agoraJs(elem, entityId, endpoint, level, parentId) {
    this.elem = elem;
    this.entityId = entityId;
    this.endpoint = endpoint;
    this.level = level;
    this.parentId = parentId;
};

agoraJs.prototype = (function(){

    var _elem;
    var _entityId;
    var _endpoint;
    var _level;
    var _parentId;
    var _sentiment;
    var _message;
    var _agoraCommentJS;
    var _stringHandler;

    var initialize_text_area = function(elem, entityId, endpoint) {
        _elem = elem;
        _entityId = entityId;
        _endpoint = endpoint;
        _level = 0;
        _parentId = _entityId;
        _sentiment = 0;
        _stringHandler = null;

        _agoraCommentJS = new agoraCommentJS();
        //_elem.keyup(return_handler);
    };

    var set_string_handler = function(stringHandler){
        _stringHandler = stringHandler;
    };

    var set_parentId = function(parentId){
        _parentId = parentId;
    };

    var set_sentiment = function(sentiment){
        _sentiment = sentiment;
    };

    var set_level = function (level) {
        _level = level;
    };

    var get_sentiment = function () {
        return _sentiment;
    };

    var return_handler = function (e) {
        var key = e.which || e.keyCode;
        if (key === 13) { // 13 is enter
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
                _agoraCommentJS.addComment($("#agora_chat_container"),
                                       AGORA.agora_static_resource_url + 'JSSnippet/comment.tpl',
                                       [(_sentiment == 0 ? 'neutral' : (_sentiment == 1 ?'satisfied' : 'dissatisfied')),AGORA.username, AGORA.user_url,AGORA.user_avatar_src,_message,raw_data.post_id,AGORA.username,'just now','0'],
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

    return {
        construct : agoraJs,

        init : function () {
            initialize_text_area(this.elem, this.entityId, this.endpoint);
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

        set_level : function (level) {
            set_level(level);
        },

        set_string_handler : function(stringHandler){
            set_string_handler(stringHandler);
        },

        add_rt_comment : function (target, snippet_url, snippet_data, post_id, datalet) {
            add_rt_comment(target, snippet_url, snippet_data, post_id, datalet);
        }
    };

})();

function agoraCommentJS(){}

agoraCommentJS.prototype = (function () {
    return {
        construct: agoraCommentJS,

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

                $(window).trigger("comment_added");
            });
        }
    }
})();