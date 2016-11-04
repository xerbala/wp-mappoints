/*
 * Client-side script to work with plugin ctc-startpoints
 * Author: Al Neal
 * Date: 14-06-2016
 * Version: 0.1
 * License: GPL2
 *
 * Thanks to all forum users for posting useful bits of code,
 *  to Google for their maps
 *  and to Wordpress for their framework
 *
 *
 */ 

var map;

function initialize() {

    // initialise to a centre in Leicestershire, then move once AJAX data available
    var mapOptions = {
        center: new google.maps.LatLng(52.50,-0.941),
        zoom: 12,
        mapTypeId: google.maps.MapTypeId.ROADMAP
    };

    var element = document.getElementById('map-canvas');
    if( !element) {
        return;
    } else {
    map = new google.maps.Map(element, mapOptions);
//    var bikeLayer = new google.maps.BicyclingLayer();
//    bikeLayer.setMap( map );
    }

    findMarkers();
}

google.maps.event.addDomListener(window, 'load', initialize);


var findMarkers = function() {

    var infowindow = new google.maps.InfoWindow({
        content: '',
        maxWidth: 400
    });

    jQuery.ajax({
        url      : rctcajax.ajax_url,
        dataType : 'json',
        type     : 'POST',
        data     :  {
            'action': 'rctcsp_handler',
            'postID' : rctcajax.postID,
            'spgroup': rctcajax.sp_grouping,
            'spzoom' : rctcajax.sp_zoom,
            'spurl'  : rctcajax.sp_plugin_dir
            },
        success : function(response) {
                var reCentre = new google.maps.LatLng( response.centre.lat,response.centre.lon );
                map.setCenter(reCentre);
                map.setZoom(parseInt(response.zoom));
                places = response.start;
                for (p in places) {
                    tmpLatLng = new google.maps.LatLng(places[p].lat,places[p].lon);
                    var marker = new google.maps.Marker({
                        map: map,
                        position: tmpLatLng,
                        title: places[p].title 
                    });
                    bindInfoWindow(marker, map, infowindow,
                            '<span class="sp-info-box-title">' + 
                            places[p].title + '</span><br />' + 
                            '<span class="sp-info-box-content">' + 
                            places[p].description + '</span>'
                            );
                }
        }

    });

}

var bindInfoWindow = function(marker, map, infowindow, html){
        google.maps.event.addListener(marker, 'click', function() {
            infowindow.setContent(html);
            infowindow.open(map, marker);
        });

}

