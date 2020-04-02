/**
 * Created by jacoborrje on 2019-05-10.
 */

function disableMapInteractions(map){
    map.boxZoom.disable();
    map.scrollZoom.disable();
    map.dragPan.disable();
    map.dragRotate.disable();
    map.keyboard.disable();
    map.doubleClickZoom.disable();
    map.touchZoomRotate.disable();
    map.flyTo({center: [0,60], zoom:3});

}

function enableMapInteractions(map){
    map.boxZoom.enable();
    map.scrollZoom.enable();
    map.dragPan.enable();
    map.dragRotate.enable();
    map.keyboard.enable();
    map.doubleClickZoom.enable();
    map.touchZoomRotate.enable();
}

function addDraggableMarker(e){
    $('#place_lng').val(e.lngLat.lng);
    $('#place_lat').val(e.lngLat.lat);

    var lng_input = "institution_new_place_lng";
    var lat_input = "institution_new_place_lat";

    var el = document.createElement('div');
    el.className = 'marker selected';
    el.id = 'new_place_marker';

    var marker = new mapboxgl.Marker(el)
        .setLngLat(e.lngLat)
        .setDraggable(true);

    $('#'+lng_input).val(marker.getLngLat().lng);
    $('#'+lat_input).val(marker.getLngLat().lat);

    if($('#new_place_marker').length) {
        $('#new_place_marker').remove();
        var el = document.createElement('div');
        el.className = 'marker selected';
        el.id = 'new_place_marker';

        var marker = new mapboxgl.Marker(el)
            .setLngLat(e.lngLat)
            .setDraggable(true);
        marker.on('drag', function () {
            $('#'+lng_input).val(marker.getLngLat().lng);
            $('#'+lat_input).val(marker.getLngLat().lat);
        });
        marker.addTo(map);
    }
    else if($('#edit_place_marker').length) {
        if (confirmEdit('location')) {
            $('#edit_place_marker').remove();

            var el = document.createElement('div');
            el.className = 'marker selected';
            el.id = 'new_place_marker';

            var marker = new mapboxgl.Marker(el)
                .setLngLat(e.lngLat)
                .setDraggable(true);
            marker.on('drag', function () {
                $('#'+lng_input).val(marker.getLngLat().lng);
                $('#'+lat_input).val(marker.getLngLat().lat);
            });
            marker.addTo(map);
        }
    }
    else{
        var el = document.createElement('div');
        el.className = 'marker selected';
        el.id = 'new_place_marker';

        var marker = new mapboxgl.Marker(el)
            .setLngLat(e.lngLat)
            .setDraggable(true);
        marker.on('drag', function () {
            $('#'+lng_input).val(marker.getLngLat().lng);
            $('#'+lat_input).val(marker.getLngLat().lat);
        });
        marker.addTo(map);
    }
}

function removeDraggableMarker(){
    if($('#edit_place_marker').length)
        $('#edit_place_marker').remove();
    if($('#new_place_marker').length)
        $('#new_place_marker').remove();
    $('#institution_new_place_lng').val("");
    $('#institution_new_place_lat').val("");
    map.off('click', addDraggableMarker);
}

function updatePlaceParent(parent_id){
    if(parent_id){
        getOverlayMap(parent_id);
    }
}

function getGeojsonAllPlaces(){
    var json = null;
    var url = Routing.generate('place_geojson_index');
    $.ajax({
        'async': false,
        'global': false,
        'url': url,
        'dataType': "json",
        'success': function (data) {
            json = data;
        }
    });
    return json;
}

function getGeojsonChildPlaces(place_id){
    var json = null;
    var url = Routing.generate('place_geojson_children', {place_id: place_id});
    $.ajax({
        'async': false,
        'global': false,
        'url': url,
        'dataType': "json",
        'success': function (data) {
            json = data;
        }
    });
    return json;
}

function getGeojsonDecendantPlaces(place_id, ){
    var json = null;
    var url = Routing.generate('place_geojson_decendants', {place_id: place_id});
    console.log(url);
    $.ajax({
        'async': false,
        'global': false,
        'url': url,
        'dataType': "json",
        'success': function (data) {
            json = data;
        }
    });
    return json;
}

function getGeojsonView(place_id) {
    var json = null;
    var url = Routing.generate('place_geojson_view', {place_id: place_id});
    console.log(url);
    $.ajax({
        'async': false,
        'global': false,
        'url': url,
        'dataType': "json",
        'success': function (data) {
            json = data;
        }
    });
    return json;
}

function getOverlayMap(place_id) {
    var json = null;
    var url = Routing.generate('place_json_mapoverlay', {place_id: place_id});
    console.log(url);
    $.ajax({
        'async': false,
        'global': false,
        'url': url,
        'dataType': "json",
        'success': function (data) {
            json = data;
        }
    });
    var mapLayer = map.getLayer('place_overlay');
    if(typeof  json[0] === 'object') {
        if(typeof mapLayer !== 'undefined') {
            map.removeLayer('place_overlay').removeSource('place_overlay');
        }
        map.addLayer({
            'id': 'place_overlay',
            'type': 'raster',
            'source': {
                'id': 'place_overlay',
                'type': 'raster',
                'tiles': [
                    json[0].url
                ],
                'tileSize': 256
            },
            'paint': {}
        });
        return json[0].url;
    }
    else{
        if(typeof mapLayer !== 'undefined') {
            map.removeLayer('place_overlay').removeSource('place_overlay');
        }
        return null;
    }
}

function placeMapMarker(selected = false, description, lnglat){
    var el = document.createElement('div');
    if(selected) {
        el.className = 'marker selected';
    }
    else {
        el.className = 'marker';
    }
    el.id = 'edit_place_marker';
    var popup = new mapboxgl.Popup({ offset: 25 })
        .setHTML(description);
    var marker = new mapboxgl.Marker(el)
        .setLngLat(lnglat)
        .setPopup(popup) // sets a popup on this marker
        .addTo(map);
}

function placeMapMarkers(geojson, selected = false){
    geojson[0].features.forEach(function (marker) {
        var el = document.createElement('div');
        if(selected) {
            el.className = 'marker selected';
        }
        else {
            el.className = 'marker';
        }
        el.id = 'edit_place_marker';
        var description = marker.properties.description;
        var popup = new mapboxgl.Popup({ offset: 25 })
            .setHTML(description);
        var marker = new mapboxgl.Marker(el)
            .setLngLat(marker.geometry.coordinates)
            .setPopup(popup) // sets a popup on this marker
            .addTo(map);
    });
}

function initiateMap(container){
    mapboxgl.accessToken = 'pk.eyJ1IjoiamFjb2JvcnJqZSIsImEiOiJjanFtOWtwbmg2a2YwNDNwcGg2cG1sZzN1In0.c11tDOxEs5T5YHgbO0TAKg';
    return new mapboxgl.Map({
        container: container,
        style: 'mapbox://styles/jacoborrje/cjslfs6640gc61gk4nrw1hagf',
        center: [0,60],
        zoom: 3});
}

function changeOverlay(url){
    var mapLayer = map.getLayer('custom_overlay');
    if(url === 0){
        if(typeof mapLayer !== 'undefined') {
            map.removeLayer('custom_overlay').removeSource('custom_overlay');
        }
    }
    else{
        if(typeof mapLayer !== 'undefined') {
            map.removeLayer('custom_overlay').removeSource('custom_overlay');
        }
        map.addLayer({
            'id': 'custom_overlay',
            'type': 'raster',
            'source': {
                'id': 'custom_overlay',
                'type': 'raster',
                'tiles': [
                    url
                ],
                'tileSize': 256
            },
            'paint': {}
        }, 'route');
        return 1;
    }
}