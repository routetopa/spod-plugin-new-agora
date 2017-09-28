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

    var friendship_tip = d3.tip()
        .attr("class", "d3-tip")
        // .offset([-12, 0])
        .html(function(d) { return d; });

    // Use a timeout to allow the rest of the page to load first.
    d3.timeout(function() {
        loading.remove();

        svg.call(user_tip);
        svg.call(friendship_tip);

        for (var user in AGORA.users_avatar) {
            g.append("g")
                .append("defs")
                .append("pattern")
                .attr("id", "u_"+ AGORA.users_avatar[user].userId)
                .attr("patternUnits", "objectBoundingBox")
                .attr("height", "1")
                .attr("width", "1")
                .append("image")
                .attr("height", "64")
                .attr("width", "64")
                .attr("xlink:href", AGORA.users_avatar[user].src != "" ? AGORA.users_avatar[user].src : ODE.ow_url_home + "ow_static/themes/spod_theme_matter/images/no-avatar.png");
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
            })
            .on("mouseover", function () {
                var link = this;

                var u_nodes = [].slice.call(d3.selectAll(".u_nodes")._groups[0]);

                var source = u_nodes[d3.select(link).data()[0].source.index];
                var target = u_nodes[d3.select(link).data()[0].target.index];

                var sourceUser = d3.select(source).data()[0].tooltip;
                var targetUser = d3.select(target).data()[0].tooltip;

                var classes = d3.select(source).attr("class") + " user_highlighted";
                d3.select(source).attr("class", classes);

                classes = d3.select(target).attr("class") + " user_highlighted";
                d3.select(target).attr("class", classes);

                friendship_tip.offset(function() {
                    return [link.getBBox().height / 2 - 12, 0];
                });

                friendship_tip.show(sourceUser + ' <span style="color: #FF9800;">' + OW.getLanguageText('spodagora', 'g_is_friend_of') + '</span> ' + targetUser);
            })
            .on("mouseout", function () {
                var link = this;

                var u_nodes = [].slice.call(d3.selectAll(".u_nodes")._groups[0]);

                var source = u_nodes[d3.select(link).data()[0].source.index];
                var target = u_nodes[d3.select(link).data()[0].target.index];

                var classes = d3.select(source).attr("class").replace(" user_highlighted", "");
                d3.select(source).attr("class", classes);

                classes = d3.select(target).attr("class").replace(" user_highlighted", "");
                d3.select(target).attr("class", classes);

                friendship_tip.hide();
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
                return 'url("#u_' + d3.select(node).data()[0].id + '")';
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

// Init comment graph
AGORA.initCommentGraph = function()
{
    var c_nodes = [];
    var c_links = [];

    var w = $("#agora_right").width();
    // var h = 120 + n * 80;
    var h = w;
    var r = w*4/10;
    var n = Object.keys(AGORA.users_avatar).length;

    var i = 0;
    for (var user in AGORA.users_avatar) {
        c_nodes.push({
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

    for (var i in AGORA.comment_graph)
        if(AGORA.comment_graph[i]["userId"] != AGORA.comment_graph[i]["friendId"])
            c_links.push({
                source: Object.keys(AGORA.users_avatar).indexOf(AGORA.comment_graph[i]["friendId"]),
                target: Object.keys(AGORA.users_avatar).indexOf(AGORA.comment_graph[i]["userId"]),
                reply: AGORA.comment_graph[i]["reply"],
                edgeNumber: 2
            });

    Object.keys(AGORA.users_avatar).indexOf("2");

    $("#svg_comment_graph").attr("height", h);

    var svg = d3.select("#svg_comment_graph"),
        g = svg.append("g");

    var simulation = d3.forceSimulation(c_nodes)
        .force("charge", d3.forceManyBody().strength(-80))
        .force("link", d3.forceLink(c_links).distance(20).strength(1).iterations(10))
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

    var reply_tip = d3.tip()
        .attr("class", "d3-tip")
        .html(function(d) { return d; });

    // Use a timeout to allow the rest of the page to load first.
    d3.timeout(function() {
        loading.remove();

        svg.call(user_tip);
        svg.call(reply_tip);

        for (var user in AGORA.users_avatar) {
            g.append("g")
                .append("defs")
                .append("pattern")
                .attr("id", "c_"+ AGORA.users_avatar[user].userId)
                .attr("patternUnits", "objectBoundingBox")
                .attr("height", "1")
                .attr("width", "1")
                .append("image")
                .attr("height", "40")
                .attr("width", "40")
                .attr("xlink:href", AGORA.users_avatar[user].src != "" ? AGORA.users_avatar[user].src : ODE.ow_url_home + "ow_static/themes/spod_theme_matter/images/no-avatar.png");
        }

        //LINKS
        svg.append("svg:defs").append("svg:marker")
            .attr("id", "c_arrow")
            .attr("refX", 6)
            .attr("refY", 6)
            .attr("markerWidth", 12)
            .attr("markerHeight", 12)
            .attr("orient", "auto")
            .attr("markerUnits", "userSpaceOnUse")
            .append("path")
            .attr("d", "M 0 0 12 6 0 12 0 6")
            .attr('fill', '#424242');

        svg.append("svg:defs").append("svg:marker")
            .attr("id", "c_arrow_w")
            .attr("refX", 6)
            .attr("refY", 6)
            .attr("markerWidth", 12)
            .attr("markerHeight", 12)
            .attr("orient", "auto")
            .attr("markerUnits", "userSpaceOnUse")
            .append("path")
            .attr("d", "M 0 0 12 6 0 12 0 6")
            .attr('fill', '#FFFFFF');

        g.append("g")
            .selectAll("line")
            .data(c_links)
            .enter().append("line")
            .attr("class", "c_links")
            .each(function (d) {
                var startCoords = offsetEdge(d, 32, 32);
                d3.select(this)
                    .attr("x1", startCoords.x1)
                    .attr("y1", startCoords.y1)
                    .attr("x2", startCoords.x2)
                    .attr("y2", startCoords.y2)
            })
            .style("stroke-width", function (d) {
                return getStrokeWidth(d.reply);
            })
            // .style("stroke", function (d) {
            //     return getStroke(d.reply);
            // })
            .attr("marker-end", "url(#c_arrow)")
            .on("mouseover", function (d) {
                var link = this;

                $(link).attr("marker-end", "url(#c_arrow_w)");

                var c_nodes = [].slice.call(d3.selectAll(".c_nodes")._groups[0]);

                var source = c_nodes[d3.select(link).data()[0].source.index];
                var target = c_nodes[d3.select(link).data()[0].target.index];

                var sourceUser = d3.select(source).data()[0].tooltip;
                var targetUser = d3.select(target).data()[0].tooltip;

                var classes = d3.select(source).attr("class") + " user_highlighted";
                d3.select(source).attr("class", classes);

                classes = d3.select(target).attr("class") + " user_highlighted";
                d3.select(target).attr("class", classes);

                reply_tip.offset(function() {
                    return [link.getBBox().height / 2 - 12, 0];
                });

                reply_tip.show(sourceUser + ' <span style="color: #FF9800;">' + OW.getLanguageText('spodagora', 'g_has_replied') + ' ' +  d.reply  + ' ' + OW.getLanguageText('spodagora', 'g_times') + '</span> ' + targetUser);
            })
            .on("mouseout", function () {
                var link = this;

                $(link).attr("marker-end", "url(#c_arrow)");

                var c_nodes = [].slice.call(d3.selectAll(".c_nodes")._groups[0]);

                var source = c_nodes[d3.select(link).data()[0].source.index];
                var target = c_nodes[d3.select(link).data()[0].target.index];

                var classes = d3.select(source).attr("class").replace(" user_highlighted", "");
                d3.select(source).attr("class", classes);

                classes = d3.select(target).attr("class").replace(" user_highlighted", "");
                d3.select(target).attr("class", classes);

                reply_tip.hide();
            });

        //https://bl.ocks.org/emeeks/c408363501ccc4410dbd
        function offsetEdge(d, sourceSize, targetSize) {
            var sourceCirc = sourceSize * 2 * Math.PI;
            var targetCirc = targetSize * 2 * Math.PI;
            var stRatio = sourceCirc/targetCirc;

            var diffX = d.target.y - d.source.y;
            var diffY = d.target.x - d.source.x;

            var angle0 = ( Math.atan2( diffY, diffX ) + ( Math.PI / 2 ) );
            var angle1 = angle0 + ( (Math.PI * 0.75) + (d.edgeNumber * 0.25) );
            var angle2 = angle0 + ( (Math.PI * 0.25) - (d.edgeNumber * 0.25) );

            var x1 = d.source.x + (sourceSize * Math.cos(angle1));
            var y1 = d.source.y - (sourceSize * Math.sin(angle1));
            var x2 = d.target.x + (targetSize * Math.cos(angle2));
            var y2 = d.target.y - (targetSize * Math.sin(angle2));

            return {x1: x1, y1: y1, x2: x2, y2: y2}
        }

        function getStrokeWidth(reply) {
            if(reply >= 100)
                return 6;
            if(reply >= 50)
                return 5;
            if(reply >= 20)
                return 4;
            if(reply >= 10)
                return 3;
            return 2;
        }

        // function getStroke(reply) {
        //     if(reply >= 100)
        //         return '#E65100';
        //     if(reply >= 50)
        //         return '#EF6C00';
        //     if(reply >= 20)
        //         return '#F57C00';
        //     if(reply >= 10)
        //         return '#FB8C00';
        //     return '#FF9800';
        // }

        //NODES
        g.append("g")
            .selectAll("circle")
            .data(c_nodes)
            .enter().append("circle")
            .attr("class", function (d) {
                return "c_nodes " + d.type;
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
                return 'url("#c_' + d3.select(node).data()[0].id + '")';
            })
            .attr("r", 20)

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

        var c_links = [].slice.call(d3.selectAll(".c_links")._groups[0]);
        var linksArray = c_links.filter(function(l){
            return d3.select(l).data()[0].source.index == d3.select(node).data()[0].index || d3.select(l).data()[0].target.index == d3.select(node).data()[0].index;
        });

        var c_nodes = [].slice.call(d3.selectAll(".c_nodes")._groups[0]);
        var nodesArray = c_nodes.filter(function(n){
            for(var l of linksArray)
                if(d3.select(l).data()[0].target.index == d3.select(n).data()[0].index || d3.select(l).data()[0].source.index == d3.select(n).data()[0].index)
                    return true;
            return false;
        });

        if(flag) {
            classes = d3.selectAll(linksArray).attr("class");
            classes += " " + cssClass;
            d3.selectAll(linksArray).attr("class", classes);
            d3.selectAll(linksArray).attr("marker-end", "url(#c_arrow_w)")

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
            d3.selectAll(linksArray).attr("marker-end", "url(#c_arrow)")

            for(var n of nodesArray) {
                classes = d3.select(n).attr("class");
                classes = classes.replace(" " + cssClass, "");
                d3.select(n).attr("class", classes);
            }
        }
    }
};