<?php

include_once $_SERVER['DOCUMENT_ROOT'] .DIRECTORY_SEPARATOR. 'LocationTracking'.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'Config.php';
include_once $_SERVER['DOCUMENT_ROOT'] .DIRECTORY_SEPARATOR. 'LocationTracking'.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.'Inputs.php';

$centerLatitude =  $_GET['centerLatitude'];
$centerLongitude =  $_GET['centerLongitude'];
$zoomLevel = Config::$zoomLevelProjects;
//$filename = Config::$xmlFile;

?>

<!DOCTYPE html >
  <head>
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
    <meta http-equiv="content-type" content="text/html; charset=UTF-8"/>
    <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js"></script>
    <script type="text/javascript">
    //<![CDATA[
    
    var customIcons = {
      worker: {
        icon: 'http://maps.gstatic.com/mapfiles/kml/paddle/W.png'
      },
      project: {
        icon: 'http://maps.google.com/mapfiles/kml/pal2/icon5.png'
      }
    };

    function load() {
      var map = new google.maps.Map(document.getElementById("map"), {
        center: new google.maps.LatLng(<?php echo $centerLatitude; ?>, <?php echo $centerLongitude; ?>),
        zoom: <?php echo $zoomLevel; ?>,
        mapTypeId: 'roadmap'
      });
      var infoWindow = new google.maps.InfoWindow({ maxWidth: 250 });
      

      downloadUrl('<?php echo $_GET['filename']; ?>', function(data) {
        var xml = data.responseXML;
        var markers = xml.documentElement.getElementsByTagName("marker");
        for (var i = 0; i < markers.length; i++) {
          var name = markers[i].getAttribute("name");
          var location = markers[i].getAttribute("location");
          var type = markers[i].getAttribute("type");
          var time = markers[i].getAttribute("time");
          var point = new google.maps.LatLng(
              parseFloat(markers[i].getAttribute("lat")),
              parseFloat(markers[i].getAttribute("lng")));
          var html ="";
          if (type == 'project'){
              html = "<b>" + name + "</b> <br/> <br/><u>Last Updated</u> : " + time + "<br/>  <br/><u>Location</u> :  " + location;
          }
          else{
              html = "<b>" + name + "</b> <br/> <br/><u>Last Recorded</u> : " + time + "<br/>  <br/><u>Location</u> :  " + location;
          }
          var icon = customIcons[type] || {};
          var marker = new google.maps.Marker({
            map: map,
            position: point,
            icon: icon.icon
          });
          bindInfoWindow(marker, map, infoWindow, html);
        }
        
        var circle = new google.maps.Circle({
            map: map,
            radius: <?php echo $_GET['radius']; ?>,    //in metres
            strokeColor: '#FF0000',
            strokeOpacity: 0.8,
            strokeWeight: .5,
            fillColor: '#FF0000',
            fillOpacity: 0.05,
          });
          circle.bindTo('center', marker, 'position');
      });
    }

    function bindInfoWindow(marker, map, infoWindow, html) {
      google.maps.event.addListener(marker, 'click', function() {
        infoWindow.setContent(html);
        infoWindow.open(map, marker);
      });
    }

    function downloadUrl(url, callback) {
      var request = window.ActiveXObject ?
          new ActiveXObject('Microsoft.XMLHTTP') :
          new XMLHttpRequest;

      request.onreadystatechange = function() {
        if (request.readyState == 4) {
          request.onreadystatechange = doNothing;
          callback(request, request.status);
        }
      };

      request.open('GET', url, true);
      request.send(null);
    }

    function doNothing() {}

    //]]>

  </script>

  </head>

  <body onload="load()">
    <div id="map" style="width: auto; height: 980px;border: 2px solid rgb(58, 135, 173);border-radius: 4px;"></div>
  </body>

</html>