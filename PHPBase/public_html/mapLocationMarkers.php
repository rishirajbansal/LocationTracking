<?php

include_once $_SERVER['DOCUMENT_ROOT'] .DIRECTORY_SEPARATOR. 'LocationTracking'.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'Config.php';

$centerLatitude =  Config::$centerLatitude;
$centerLongitude =  Config::$centerLongitude;
//$zoomLevel = Config::$zoomLevel;
$zoomLevel = 15;
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
     old: {
        icon: 'http://maps.gstatic.com/mapfiles/ms/micons/green-dot.png'
      },
      new: {
        icon: 'http://maps.gstatic.com/mapfiles/ms/micons/yellow-dot.png'
      }
    };
    

    function load() {
      var map = new google.maps.Map(document.getElementById("map"), {
        center: new google.maps.LatLng(<?php echo $centerLatitude; ?>, <?php echo $centerLongitude; ?>),
        zoom: <?php echo $zoomLevel; ?>,
        mapTypeId: 'roadmap'
      });
      var infoWindow = new google.maps.InfoWindow({ maxWidth: 250 });
      

      downloadUrl('<?php echo $filename; ?>', function(data) {
        var xml = data.responseXML;
        var markers = xml.documentElement.getElementsByTagName("marker");
        var lineSymbol = {
                        path: google.maps.SymbolPath.FORWARD_CLOSED_ARROW,
                        strokeColor: '#3867CC',
                        fillColor: '#3867CC',
                        fillOpacity: 1
                      };
        var markersPath = [];
        markersPath.length = markers.length; 
        
        for (var i = 0; i < markers.length; i++) {
          var name = markers[i].getAttribute("name");
          var location = markers[i].getAttribute("location");
          var type = markers[i].getAttribute("type");
          var time = markers[i].getAttribute("time");
          var point = new google.maps.LatLng(
              parseFloat(markers[i].getAttribute("lat")),
              parseFloat(markers[i].getAttribute("lng")));
          var html = "<b>" + name + "</b> <br/> <br/><u>Time</u> : " + time + "<br/>  <br/><u>Location</u> :  " + location;
          var icon = customIcons[type] || {};
          var marker = new google.maps.Marker({
            map: map,
            position: point,
            icon: icon.icon
          });
          bindInfoWindow(marker, map, infoWindow, html);
          
          markersPath[i] = [];
          markersPath[i]["lat"] = parseFloat(markers[i].getAttribute("lat"));
          markersPath[i]["lng"] = parseFloat(markers[i].getAttribute("lng"));  
          
          
        }//End of For
        
        var line = new google.maps.Polyline({
                path: markersPath,
                geodesic: true,
                icons: [{
                  icon: lineSymbol,
                  offset: '100%',
                  repeat: '20%'
                }],
                strokeColor: '#03a9f4',
                strokeOpacity: 1.0,
                strokeWeight: 3.0,
                map: map
              });
        
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
    <div id="map" style="width: auto; height: 1000px;border: 2px solid rgb(58, 135, 173);border-radius: 4px;"></div>
  </body>

</html>