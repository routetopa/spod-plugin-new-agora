AGORAMAIN = {};

AGORAMAIN.init = function()
{
    $(document).ready(function () {

        AGORAMAIN.handleAnimatedGridControllet();

        window.addEventListener('fullsize-page-with-card-controllet_attached', function () {
            AGORAMAIN.onFullsizePageAttached();
        });

        document.addEventListener('search-panel-controllet_content-changed', function (e) {
            AGORAMAIN.onSearchPanelControlletContentChanged(e);
        });

        /*setTimeout(function () {
         $("#infoToast")[0].show();
         }, 500);*/

    })
};

AGORAMAIN.addRoom = function()
{
    previewFloatBox = OW.ajaxFloatBox('SPODAGORA_CMP_AgoraRoomCreator', {} , {top: '60px', width:'60%', height:'480px', iconClass: 'ow_ic_add', title: ''});
};

AGORAMAIN.handleSuggestedDataset= function(publicRoomId)
{
    previewFloatBox = OW.ajaxFloatBox('SPODPUBLIC_CMP_Suggestion', {publicRoom : publicRoomId} , {top: '60px', width:'60%', height:'480px', iconClass: 'ow_ic_add', title: ''});
};

AGORAMAIN.addCommnet = function(e)
{
    $(e).parents().eq(5).find("textarea").trigger({type:"comment.test", isButton:true});
};

AGORAMAIN.handleAnimatedGridControllet = function()
{
    var scope = document.querySelector('template[is="dom-bind"]');

    scope._onTileClick = function (event) {
        $("#add_room_button").hide();
        $("#search_room").hide();
        this.$['fullsize-card'].color = event.detail.data.color;
        this.$['fullsize-card'].agora_url = ODE.ow_url_home + 'agora/' + event.detail.data.id;
        var hash = ODE.ow_url_home + 'agoraMain/#!/' + event.detail.data.id;
        history.pushState({}, "Public Room", hash);
        // TODO controllare se è utile
        //ODE.publicRoom = event.detail.data.id;
        this.$.pages.selected = 1;
    };

    scope._onFullsizeClick = function () {
        $("#add_room_button").show();
        $("#search_room").show();
        this.$['fullsize-card'].publicRoom = undefined;
        this.$.pages.selected = 0;
    };
};

AGORAMAIN.onFullsizePageAttached = function()
{
    var match = window.location.hash.match(/\/[0-9]*/g);
    if (match) {
        var agoraId = match[0].replace("/", "");
        $("#add_room_button").hide();
        $("#search_room").hide();
        $("#fullsize-card")[0].color = $("#" + agoraId).css('background-color');
        $("#fullsize-card")[0].agora_url = ODE.ow_url_home + 'agora/' +  agoraId;
        // TODO controllare se è utile
        //ODE.publicRoom = roomId;
        $("#pages")[0].selected = 1;
    }
};

AGORAMAIN.onSearchPanelControlletContentChanged = function (e)
{
    console.log(e.detail.searchKey);

    var rooms = document.querySelectorAll('room-controllet');
    for (var i = 0; i < rooms.length; i++) {
        var subject = rooms[i].subject;
        var body = rooms[i].body;

        var searchFlag = subject.indexOf(e.detail.searchKey) == -1 && body.indexOf(e.detail.searchKey) == -1;

        if (!searchFlag || e.detail.searchKey == "") {
            rooms[i].children[0].style.display = "inline-block";
        }
        else {
            rooms[i].children[0].style.display = "none";
        }
    }

    $('.grid').masonry();
};

AGORAMAIN.addNewRoom = function(event)
{
    previewFloatBox.close();
    var room = AGORAMAIN.createRoom(event);
    $('.grid').prepend(room).masonry('reloadItems').masonry('layout');

    var myPublicRoomMenu = document.getElementById('my-public-room-menu');
    var paperItem     = document.createElement('paper-item');
    var paperItemBody = document.createElement('paper-item-body');
    paperItem.setAttribute("onclick","AGORAMAIN.handleSuggestedDataset("+event.id+")");
    paperItemBody.setAttribute("two-lines","");
    paperItemBody.innerHTML = event.subject;
    paperItem.appendChild(paperItemBody);
    myPublicRoomMenu.insertBefore(paperItem, myPublicRoomMenu.firstChild);
};

AGORAMAIN.createRoom = function(event)
{
    var room = document.createElement('room-controllet');

    room.setAttribute('room-owner', ODE.currentUsername);
    room.setAttribute('room-shape', '["few","few","few"]');
    room.setAttribute('room-id', event.id);
    room.setAttribute('body', event.body);
    room.setAttribute('subject', event.subject);
    room.setAttribute('timestamp', 'Now');
    room.setAttribute('room-color', '#2C29FF');
    room.setAttribute('datasets', '0');
    room.setAttribute('comments', '0');
    room.setAttribute('room-width', 'grid-item-w20');
    room.setAttribute('room-height', 'grid-item-h200');
    room.setAttribute('room-views', '0');

    return room;
};