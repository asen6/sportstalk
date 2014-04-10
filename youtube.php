<html>
<head>
<script src="js/jquery-1.5.2.js" type="text/javascript" charset="utf-8"></script>
<script type="text/javascript"> 

// Replaces hyperlinks in a string with <a href> tags.
function dummy(){
    var text = $("#url_input")[0].value;
    var youtubeid = detect_first_youtube_link_id(text);
    
    if (youtubeid != ""){
        loadthumbnail(youtubeid);
    }
    
}

function detect_first_youtube_link_id(text)
{
    var p = /\b((?:https?:\/\/|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}\/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:'".,<>???????]))/i;
    
    while (true) {
        var matcharray = text.match(p);
        
        if (matcharray == null || matcharray.length == 0) {
            break;
        }

        var link = RegExp.$1;
        var youtubeid = getyoutubeidfromlink(link);
        
        if (youtubeid != ""){
            return youtubeid;
        }
        
        text = RegExp.rightContext;
    } 
    return ""; 
}


function loadthumbnail(yid){

    var link = document.createElement("a");
    link.href = "#video";
    link.onclick = function(){loadvideo(yid)};

    var img = document.createElement("img");
    img.src = "http://img.youtube.com/vi/" + yid + "/0.jpg";
    img.height = 100;
    img.width = 160;

    link.appendChild(img);
    var container = $("#vid")[0];
    container.appendChild(link);
    
    // create stuff for callback to use
    //$.getScript( 'http://gdata.youtube.com/feeds/api/videos/' + encodeURIComponent(yid) + '?v=2&alt=json-in-script&callback=youtubeFetchDataCallback' );
}

function youtubeFetchDataCallback(data)
{
  var s = '';
  s += '<img src="' + data.entry[ "media$group" ][ "media$thumbnail" ][ 0 ].url + '" width="' + data.entry[ "media$group" ][ "media$thumbnail" ][ 0 ].width + '" height="' + data.entry[ "media$group" ][ "media$thumbnail" ][ 0 ].height + '" alt="Default Thumbnail" align="right"/>';
  s += '<b>Title:</b> ' + data.entry[ "title" ].$t + '<br/>';
  s += '<b>Author:</b> ' + data.entry[ "author" ][ 0 ].name.$t + '<br/>';
  s += '<b>Published:</b> ' + new Date( data.entry[ "published" ].$t.substr( 0, 4 ), data.entry[ "published" ].$t.substr( 5, 2 ) - 1, data.entry[ "published" ].$t.substr( 8, 2 ) ).toLocaleDateString( ) + '<br/>';
  s += '<b>Duration:</b> ' + Math.floor( data.entry[ "media$group" ][ "yt$duration" ].seconds / 60 ) + ':' + ( data.entry[ "media$group" ][ "yt$duration" ].seconds % 60 ) + ' (' + data.entry[ "media$group" ][ "yt$duration" ].seconds + ' seconds)<br/>';
  s += '<b>Rating:</b> ' + new Number( data.entry[ "gd$rating" ].average ).toFixed( 1 ) + ' out of ' + data.entry[ "gd$rating" ].max + '; ' + data.entry[ "gd$rating" ].numRaters + ' rating(s)' + '<br/>';
  s += '<b>Statistics:</b> ' + data.entry[ "yt$statistics" ].favoriteCount + ' favorite(s); ' + data.entry[ "yt$statistics" ].viewCount + ' view(s)' + '<br/>';
  s += '<br/>' + data.entry[ "media$group" ][ "media$description" ].$t.replace( /\n/g, '<br/>' ) + '<br/>';
  s += '<br/><a href="' + data.entry[ "media$group" ][ "media$player" ].url + '" target="_blank">Watch on YouTube</a>';
  $('#youtubeDataFetcherOutput').html( s );
}

function loadvideo(yid){
    $("#vid").empty();
    
    var vidblock = document.createElement("object");
    vidblock.width = "425";
    vidblock.height = "350";
    vidblock.data= "http://www.youtube.com/v/" + yid;
    vidblock.type = "application/x-shockwave-flash";
    
    var paramblock = document.createElement("param");
    paramblock.name = "src";
    paramblock.value = "http://www.youtube.com/v/" + yid;
    
    vidblock.appendChild(paramblock);
    var container = $("#vid")[0];
    container.appendChild(vidblock);
}

function getyoutubeid()
{
    var url = $("#url_input")[0].value;
    
    if (!url.Contains("youtube.com") && !url.Contains("youtu.be")){
        return "";
    }
    
    var id = getParameter(url,"v");
    
    if (id == null || id == "") {
        $("#yid").text("No match");
    }
    else {
        $("#yid").text(id);
        loadthumbnail(id);
    }
    return "";
}

if (url.indexOf("youtube.com") == -1 && url.indexOf("youtu.be") == -1){
        return "";
}

function getParameter(url, name) {
    var urlparts = url.split('?');
    if (urlparts.length > 1) {
        var parameters = urlparts[1].split('&');
        for (var i = 0; i < parameters.length; i++) {
            var paramparts = parameters[i].split('=');
            if (paramparts.length > 1 && unescape(paramparts[0]) == name) {
                return unescape(paramparts[1]);
            }
        }
    }
    return null;
}

</script>

<!--
<script type="text/javascript">
  function youtubeFeedCallback( data )
  {
    document.writeln( '<img src="' + data.entry[ "media$group" ][ "media$thumbnail" ][ 0 ].url + '" width="' + data.entry[ "media$group" ][ "media$thumbnail" ][ 0 ].width + '" height="' + data.entry[ "media$group" ][ "media$thumbnail" ][ 0 ].height + '" alt="Default Thumbnail" align="right"/>' );
    document.writeln( '<b>Title:</b> ' + data.entry[ "title" ].$t + '<br/>' );
    document.writeln( '<b>Author:</b> ' + data.entry[ "author" ][ 0 ].name.$t + '<br/>' );
    document.writeln( '<b>Published:</b> ' + new Date( data.entry[ "published" ].$t.substr( 0, 4 ), data.entry[ "published" ].$t.substr( 5, 2 ) - 1, data.entry[ "published" ].$t.substr( 8, 2 ) ).toLocaleDateString( ) + '<br/>' );
    document.writeln( '<b>Duration:</b> ' + Math.floor( data.entry[ "media$group" ][ "yt$duration" ].seconds / 60 ) + ':' + ( data.entry[ "media$group" ][ "yt$duration" ].seconds % 60 ) + ' (' + data.entry[ "media$group" ][ "yt$duration" ].seconds + ' seconds)<br/>' );
    document.writeln( '<b>Rating:</b> ' + new Number( data.entry[ "gd$rating" ].average ).toFixed( 1 ) + ' out of ' + data.entry[ "gd$rating" ].max + '; ' + data.entry[ "gd$rating" ].numRaters + ' rating(s)' + '<br/>' );
    document.writeln( '<b>Statistics:</b> ' + data.entry[ "yt$statistics" ].favoriteCount + ' favorite(s); ' + data.entry[ "yt$statistics" ].viewCount + ' view(s)' + '<br/>' );
    document.writeln( '<br/>' + data.entry[ "media$group" ][ "media$description" ].$t.replace( /\n/g, '<br/>' ) + '<br/>' );
    document.writeln( '<br/><a href="' + data.entry[ "media$group" ][ "media$player" ].url + '" target="_blank">Watch on YouTube</a>' );
  }
</script>

<script type="text/javascript" src="http://gdata.youtube.com/feeds/api/videos/gzDS-Kfd5XQ?v=2&amp;alt=json-in-script&amp;callback=youtubeFeedCallback"></script>

-->
</head>

<body>
<div id="scripts">
</div>
<div>
    <input id="url_input" type="text" /><a href="#y" onclick="dummy(); return false;">try</a>
</div>

<div id="yid">
    
</div>

<div id="vid">

</div>

<div id="youtubeDataFetcherOutput">

</div>

</body>
</html>