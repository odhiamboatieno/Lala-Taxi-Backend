;(function ($, window) {
  'use strict';

  var payload = null;
  var map;
  var markers = [];
  var polygons = [];
  var infoWindow;
  var searchBox;
  var listSearchTerm = '';
  var selectedItemId = null;
  var mapBooted = false;

  var iconMap = {
    driver: APP_URL + '/images/marker_dgreen.png',
    on_trip: APP_URL + '/images/marker_green.png',
    idle: APP_URL + '/images/marker_dgreen.png',
    offline: APP_URL + '/images/marker_pink.png',
    customer: APP_URL + '/images/man_marker.png',
    alert: APP_URL + '/images/icon/map-pin-red.png'
  };

  function escapeHtml(value) {
    return $('<div/>').text(value == null ? '' : value).html();
  }

  function getPayload() {
    payload = window.fleetMapPayload || payload;
    return payload;
  }

  function isCustomersMode() {
    var currentPayload = getPayload();
    return currentPayload && currentPayload.mode === 'customers';
  }

  function isAlertsMode() {
    var currentPayload = getPayload();
    return currentPayload && currentPayload.mode === 'alerts';
  }

  function itemIcon(item) {
    if (item.type === 'alert') {
      return iconMap.alert;
    }

    if (item.type === 'customer') {
      return iconMap.customer;
    }

    if (item.status_key === 'on_trip') {
      return iconMap.on_trip;
    }

    if (item.status_key === 'idle') {
      return iconMap.idle;
    }

    return iconMap.offline;
  }

  function itemMarkerLabel(item) {
    if (item.type === 'alert') {
      return '!';
    }

    return null;
  }

  function getItems() {
    var currentPayload = getPayload();
    var items = currentPayload && currentPayload.items ? currentPayload.items.slice() : [];

    if (!listSearchTerm) {
      return items;
    }

    return items.filter(function (item) {
      var haystack = [
        item.name,
        item.title,
        item.phone,
        item.vehicle_number,
        item.driver_name,
        item.customer_name
      ].join(' ').toLowerCase();

      return haystack.indexOf(listSearchTerm.toLowerCase()) !== -1;
    });
  }

  function currentSelectedItem() {
    var currentPayload = getPayload();
    var items = currentPayload && currentPayload.items ? currentPayload.items : [];

    if (selectedItemId) {
      for (var i = 0; i < items.length; i++) {
        if (parseInt(items[i].id, 10) === parseInt(selectedItemId, 10)) {
          return items[i];
        }
      }
    }

    return currentPayload && currentPayload.selected_item ? currentPayload.selected_item : null;
  }

  function listItemDotClass(item) {
    if (item.type === 'alert') {
      return 'alert';
    }

    if (item.status_key === 'on_trip') {
      return 'on-trip';
    }

    if (item.status_key === 'idle') {
      return 'idle';
    }

    return '';
  }

  function renderList() {
    var items = getItems();
    var $list = $('#fleet-list');
    $list.empty();

    if (!items.length) {
      $list.html('<div class="fleet-empty">No map records found for this view.</div>');
      return;
    }

    items.forEach(function (item) {
      var isActive = selectedItemId && parseInt(selectedItemId, 10) === parseInt(item.id, 10);
      var meta1 = item.type === 'alert'
        ? escapeHtml(item.description || 'Open safety alert')
        : 'Phone : ' + escapeHtml(item.hidden_phone || item.phone || '-');
      var meta2 = '';

      if (item.type === 'driver') {
        meta2 = 'Vehicle No : ' + escapeHtml(item.vehicle_number || '-') + ' | Model : ' + escapeHtml(item.vehicle_model || '-');
      }
      else if (item.type === 'customer' && item.latest_trip) {
        meta2 = 'Latest Trip : ' + escapeHtml(item.latest_trip.status || '-') + ' | Total Trips : ' + escapeHtml(item.trip_count || 0);
      }
      else if (item.type === 'alert') {
        meta2 = item.trip_ref ? 'Trip : ' + escapeHtml(item.trip_ref) : 'Open safety event';
      }

      $list.append(
        '<div class="fleet-list-item ' + (isActive ? 'active' : '') + '" data-id="' + item.id + '">' +
          '<div class="fleet-list-name">' +
            '<span class="fleet-dot ' + listItemDotClass(item) + '"></span>' +
            '<span>' + escapeHtml(item.name || item.title) + '</span>' +
          '</div>' +
          '<div class="fleet-list-meta">' + meta1 + '</div>' +
          (meta2 ? '<div class="fleet-list-submeta">' + meta2 + '</div>' : '') +
        '</div>'
      );
    });
  }

  function renderMapUnavailable(message) {
    var canvas = document.getElementById('fleet-map');
    if (!canvas) {
      return;
    }

    canvas.innerHTML =
      '<div class="fleet-empty" style="height:100%;min-height:560px;display:flex;align-items:center;justify-content:center;padding:24px;">' +
        escapeHtml(message || 'Map is temporarily unavailable.') +
      '</div>';
  }

  function renderSelection() {
    var item = currentSelectedItem();
    var $selection = $('#fleet-selection-view');
    var $listView = $('#fleet-list-view');

    if (!item) {
      $selection.removeClass('active').empty();
      $listView.show();
      return;
    }

    var backLabel = isCustomersMode() ? 'Customer List' : (isAlertsMode() ? 'Safety Alerts' : 'Driver List');
    var profileTitle = item.name || item.title || '';
    var badge = '';

    if (item.type === 'driver' && item.level_name) {
      badge += '<span class="fleet-badge">Level - ' + escapeHtml(item.level_name) + '</span> ';
    }

    if (item.type === 'driver' && item.rating) {
      badge += '<span style="font-weight:700;color:#f59e0b;">&#9733; ' + escapeHtml(item.rating) + '</span>';
    }

    var detailRows = '';
    var alertBlock = '';

    if (item.type === 'driver') {
      detailRows =
        row('Joining Date', item.joined_date || '-') +
        row('Service', (item.service_labels || []).join(', ') || '-') +
        row('Vehicle Category', item.vehicle_category || '-') +
        row('Vehicle Number', item.vehicle_number || '-') +
        row('Vehicle Brand', item.vehicle_brand || '-') +
        row('Vehicle Model', item.vehicle_model || '-');
    }
    else if (item.type === 'customer') {
      detailRows =
        row('Joined', item.joined_date || '-') +
        row('Total Trip', item.trip_count || 0);

      if (item.latest_trip) {
        alertBlock +=
          '<div class="fleet-detail-card">' +
            '<h6>Completed Trip <span class="pull-right">' + escapeHtml(item.latest_trip.id ? ('ID #' + item.latest_trip.id) : '') + '</span></h6>' +
            '<div class="fleet-detail-grid">' +
              '<div>' + escapeHtml(item.latest_trip.pickup || '-') + '</div><div></div>' +
              '<div>' + escapeHtml(item.latest_trip.drop || '-') + '</div><div></div>' +
            '</div>' +
            '<div class="fleet-detail-grid" style="margin-top:16px;">' +
              '<div>Trip Type</div><div>' + escapeHtml(item.latest_trip.trip_type || '-') + '</div>' +
              '<div>Driver</div><div>' + escapeHtml(item.latest_trip.driver_name || '-') + '</div>' +
              '<div>Driver Phone</div><div>' + escapeHtml(item.latest_trip.driver_phone || '-') + '</div>' +
            '</div>' +
          '</div>';
      }

      if (item.related_alerts && item.related_alerts.length) {
        var alert = item.related_alerts[0];
        alertBlock +=
          '<div class="fleet-detail-card fleet-alert-card">' +
            '<h6>Safety Alert (' + item.related_alerts.length + ')</h6>' +
            '<p>' + escapeHtml(alert.description || alert.title || 'Open safety alert') + '</p>' +
            '<button type="button" class="btn btn-primary btn-block js-resolve-alert" data-id="' + alert.id + '">MARK AS SOLVED</button>' +
          '</div>';
      }
    }
    else if (item.type === 'alert') {
      detailRows =
        row('Driver', item.driver_name || '-') +
        row('Customer', item.customer_name || '-') +
        row('Trip', item.trip_ref || '-') +
        row('Trip Type', item.trip_type || '-') +
        row('Opened', item.created_at || '-');

      alertBlock =
        '<div class="fleet-detail-card fleet-alert-card">' +
          '<h6>Safety Alert</h6>' +
          '<p>' + escapeHtml(item.description || item.title || 'Open safety alert') + '</p>' +
          '<button type="button" class="btn btn-primary btn-block js-resolve-alert" data-id="' + item.id + '">MARK AS SOLVED</button>' +
        '</div>';
    }

    $selection.html(
      '<a href="javascript:void(0)" class="fleet-back-link" id="fleet-selection-back"><svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5"></path><path d="M12 19l-7-7 7-7"></path></svg> ' + backLabel + '</a>' +
      '<div class="fleet-profile">' +
        '<img src="' + escapeHtml(item.image || APP_URL + '/images/user.jpeg') + '" alt="' + escapeHtml(profileTitle) + '">' +
        '<div>' +
          '<div class="fleet-profile-name">' + escapeHtml(profileTitle) + '</div>' +
          (badge ? '<div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;margin-bottom:6px;">' + badge + '</div>' : '') +
          '<div style="color:#4a5568;font-size:14px;">' + escapeHtml(item.hidden_phone || item.phone || '') + '</div>' +
        '</div>' +
      '</div>' +
      '<div class="fleet-detail-card">' +
        '<h6>Details</h6>' +
        '<div class="fleet-detail-grid">' + detailRows + '</div>' +
      '</div>' +
      alertBlock
    );

    $selection.addClass('active');
    $listView.hide();
  }

  function row(label, value) {
    return '<div>' + escapeHtml(label) + '</div><div>' + escapeHtml(value) + '</div>';
  }

  function clearMap() {
    markers.forEach(function (marker) {
      marker.setMap(null);
    });
    polygons.forEach(function (polygon) {
      polygon.setMap(null);
    });
    markers = [];
    polygons = [];
  }

  function polygonPathFromRing(ringSet) {
    if (!Array.isArray(ringSet) || !ringSet.length) {
      return [];
    }

    if (Array.isArray(ringSet[0])) {
      return ringSet[0];
    }

    if (ringSet[0] && typeof ringSet[0] === 'object' && ringSet[0].lat !== undefined && ringSet[0].lng !== undefined) {
      return ringSet;
    }

    return [];
  }

  function drawPolygons() {
    payload = getPayload();
    if (!map || !payload || !payload.polygons) {
      return;
    }

    payload.polygons.forEach(function (ringSet) {
      var path = polygonPathFromRing(ringSet);
      if (!path.length) {
        return;
      }

      var polygon = new google.maps.Polygon({
        paths: path,
        strokeColor: '#38506f',
        strokeOpacity: 0.55,
        strokeWeight: 2,
        fillColor: '#2cb7a4',
        fillOpacity: 0.04,
        map: map
      });

      polygons.push(polygon);
    });
  }

  function markerPopup(item) {
    if (item.type === 'alert') {
      return (
        '<div class="fleet-map-popup">' +
          '<div class="fleet-map-popup__title">' + escapeHtml(item.title || item.name) + '</div>' +
          '<div class="fleet-map-popup__meta">' + escapeHtml(item.description || '') + '</div>' +
        '</div>'
      );
    }

    return (
      '<div class="fleet-map-popup">' +
        '<div class="fleet-map-popup__title">' + escapeHtml(item.name) + '</div>' +
        '<div class="fleet-map-popup__meta">' + escapeHtml(item.hidden_phone || item.phone || '') + '</div>' +
      '</div>'
    );
  }

  function renderMarkers() {
    payload = getPayload();
    if (!map) {
      return;
    }

    clearMap();
    drawPolygons();

    var bounds = new google.maps.LatLngBounds();
    var items = getItems();
    var selected = currentSelectedItem();

    items.forEach(function (item) {
      addMarker(item, bounds);
    });

    if (!isAlertsMode() && selected && selected.related_alerts && selected.related_alerts.length) {
      selected.related_alerts.forEach(function (alertItem) {
        addMarker(alertItem, bounds, true);
      });
    }

    if (currentSelectedItem()) {
      zoomToSelected();
    }
    else if (!bounds.isEmpty()) {
      map.fitBounds(bounds);
    }
  }

  function addMarker(item, bounds, relatedAlert) {
    if (item.lat === null || item.lat === undefined || item.lng === null || item.lng === undefined) {
      return;
    }

    var marker = new google.maps.Marker({
      position: { lat: parseFloat(item.lat), lng: parseFloat(item.lng) },
      map: map,
      title: item.name || item.title,
      icon: {
        url: relatedAlert ? iconMap.alert : itemIcon(item),
        scaledSize: new google.maps.Size((relatedAlert || item.type === 'alert') ? 34 : 28, (relatedAlert || item.type === 'alert') ? 34 : 38)
      },
      label: itemMarkerLabel(item) && !relatedAlert ? {
        text: itemMarkerLabel(item),
        color: '#ffffff',
        fontWeight: '700'
      } : null
    });

    marker.__itemId = item.id;
    markers.push(marker);
    bounds.extend(marker.getPosition());

    marker.addListener('click', function () {
      if (!relatedAlert) {
        selectedItemId = item.id;
        payload.selected_item = item;
        renderList();
        renderSelection();
      }
      if (!infoWindow) {
        infoWindow = new google.maps.InfoWindow();
      }
      infoWindow.setContent(markerPopup(item));
      infoWindow.open(map, marker);
    });
  }

  function zoomToSelected() {
    var item = currentSelectedItem();

    if (!map || !item || !item.lat || !item.lng) {
      return;
    }

    var position = new google.maps.LatLng(parseFloat(item.lat), parseFloat(item.lng));
    map.panTo(position);
    map.setZoom(item.type === 'alert' ? 17 : 16);

    if (!infoWindow) {
      infoWindow = new google.maps.InfoWindow();
    }

    infoWindow.setContent(markerPopup(item));
    infoWindow.setPosition(position);
    infoWindow.open(map);
    renderSelection();
  }

  function initSearchBox() {
    var input = document.getElementById('fleet-map-search');
    if (!input || !window.google || !google.maps || !google.maps.places) {
      return;
    }

    searchBox = new google.maps.places.SearchBox(input);
    searchBox.addListener('places_changed', function () {
      var places = searchBox.getPlaces();
      if (!places || !places.length) {
        return;
      }

      var place = places[0];
      if (place.geometry && place.geometry.location) {
        map.panTo(place.geometry.location);
        map.setZoom(14);
      }
    });
  }

  function resolveAlert(id) {
    payload = getPayload();
    if (!id) {
      return;
    }

    $.ajax({
      url: payload.resolve_alert_base_url + '/' + id + '/resolve',
      method: 'POST',
      dataType: 'json',
      data: {
        _token: $('meta[name="csrf-token"]').attr('content')
      }
    }).done(function () {
      payload.items = (payload.items || []).filter(function (item) {
        return parseInt(item.id, 10) !== parseInt(id, 10);
      });
      payload.alert_items = (payload.alert_items || []).filter(function (item) {
        return parseInt(item.id, 10) !== parseInt(id, 10);
      });
      payload.alerts_count = Math.max(0, parseInt(payload.alerts_count || 0, 10) - 1);
      selectedItemId = null;
      payload.selected_item = null;
      renderList();
      renderSelection();
      renderMarkers();
    });
  }

  function refreshData() {
    payload = getPayload();
    if (!payload || !payload.live_data_url) {
      return;
    }

    $.getJSON(payload.live_data_url, function (response) {
      payload = response;
      if (payload.selected_item_id) {
        selectedItemId = payload.selected_item_id;
      }
      renderList();
      renderSelection();
      renderMarkers();
      updateAlertFab();
      $('.fleet-map-updated').text('Last updated ' + (payload.updated_at || 'just now'));
    });
  }

  function hydrateInterface() {
    payload = getPayload();
    if (!payload) {
      return;
    }

    renderList();
    renderSelection();
    updateAlertFab();
  }

  function updateAlertFab() {
    payload = getPayload();
    var $fab = $('.fleet-alert-fab');
    $fab.find('.badge').remove();

    if (parseInt(payload.alerts_count || 0, 10) > 0) {
      $fab.append('<span class="badge">' + payload.alerts_count + '</span>');
    }
  }

  function handleLocationFilter() {
    var value = $('#fleet-location-filter').val() || 0;
    var target = new URL(window.location.href);

    if (parseInt(value, 10) > 0) {
      target.searchParams.set('location_id', value);
    } else {
      target.searchParams.delete('location_id');
    }

    if (selectedItemId) {
      target.searchParams.set('selected', selectedItemId);
    } else {
      target.searchParams.delete('selected');
    }

    window.location.href = target.toString();
  }

  function toggleFullscreen() {
    var element = document.querySelector('.fleet-map-shell');
    if (!element) {
      return;
    }

    if (!document.fullscreenElement) {
      if (element.requestFullscreen) {
        element.requestFullscreen();
      }
    } else if (document.exitFullscreen) {
      document.exitFullscreen();
    }
  }

  function fitAllMarkers() {
    var bounds = new google.maps.LatLngBounds();
    var hasMarkers = false;

    markers.forEach(function (marker) {
      bounds.extend(marker.getPosition());
      hasMarkers = true;
    });

    if (hasMarkers) {
      map.fitBounds(bounds);
    }
  }

  function bootFleetMap() {
    payload = getPayload();
    var canvas = document.getElementById('fleet-map');
    if (!canvas || !payload || !window.google || !google.maps || mapBooted) {
      return false;
    }

    if (payload.selected_item_id) {
      selectedItemId = payload.selected_item_id;
    }

    map = new google.maps.Map(canvas, {
      zoom: 2,
      minZoom: 2,
      center: { lat: 17.3850, lng: 78.4867 },
      mapTypeId: google.maps.MapTypeId.ROADMAP,
      streetViewControl: false,
      mapTypeControl: true,
      fullscreenControl: false
    });
    mapBooted = true;

    hydrateInterface();
    renderMarkers();
    initSearchBox();
    updateAlertFab();

    window.setInterval(refreshData, 90000);
    return true;
  }

  window.initFleetMap = bootFleetMap;
  window.initMap = bootFleetMap;

  function waitForGoogleMaps(attempts) {
    attempts = attempts || 0;
    if (window.google && google.maps) {
      bootFleetMap();
      return;
    }

    if (attempts >= 20) {
      renderMapUnavailable('Google Maps could not be loaded for this page. The driver and rider lists are still available.');
      return;
    }

    window.setTimeout(function () {
      waitForGoogleMaps(attempts + 1);
    }, 250);
  }

  $(document).on('click', '.fleet-list-item', function () {
    payload = getPayload();
    selectedItemId = $(this).data('id');
    payload.selected_item = currentSelectedItem();
    renderList();
    renderSelection();
    zoomToSelected();
  });

  $(document).on('click', '#fleet-selection-back', function () {
    selectedItemId = null;
    payload.selected_item = null;
    renderList();
    renderSelection();
    renderMarkers();
  });

  $(document).on('click', '.js-resolve-alert', function () {
    resolveAlert($(this).data('id'));
  });

  $('#fleet-list-search-btn').on('click', function () {
    listSearchTerm = $('#fleet-list-search').val() || '';
    renderList();
    renderMarkers();
  });

  $('#fleet-list-search').on('keyup', function (event) {
    if (event.key === 'Enter') {
      listSearchTerm = $(this).val() || '';
      renderList();
      renderMarkers();
    }
  });

  $('#fleet-location-filter').on('change', handleLocationFilter);
  $('#fleet-map-fullscreen').on('click', toggleFullscreen);
  $('#fleet-map-fit').on('click', fitAllMarkers);

  $(function () {
    hydrateInterface();
    waitForGoogleMaps();
  });
})(jQuery, window);
