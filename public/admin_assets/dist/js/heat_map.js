;(function ($, window) {
  'use strict';

  var payload = null;
  var overviewMap = null;
  var compareMaps = [];
  var polygons = [];
  var searchBox = null;
  var compareChart = null;
  var heatMapBooted = false;

  function getPayload() {
    payload = window.heatMapPayload || payload;
    return payload;
  }

  function heatLayerData(points) {
    return (points || []).map(function (point) {
      var lat = parseFloat(point.lat);
      var lng = parseFloat(point.lng);
      if (isNaN(lat) || isNaN(lng)) {
        return null;
      }
      var weight = parseInt(point.weight, 10);
      if (isNaN(weight)) {
        weight = 1;
      }
      if (weight > 1) {
        return {
          location: new google.maps.LatLng(lat, lng),
          weight: weight
        };
      }
      return new google.maps.LatLng(lat, lng);
    }).filter(function (p) { return p !== null; });
  }

  function clearPolygons() {
    polygons.forEach(function (polygon) {
      polygon.setMap(null);
    });
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

  function drawPolygons(map, polygonSets) {
    clearPolygons();

    (polygonSets || []).forEach(function (ringSet) {
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

  function renderHeatMapUnavailable(message) {
    payload = getPayload();
    var overview = document.getElementById('heatmap-overview');
    if (overview) {
      overview.innerHTML =
        '<div class="fleet-empty" style="height:100%;min-height:540px;display:flex;align-items:center;justify-content:center;padding:24px;">' +
          message +
        '</div>';
    }

    (payload && payload.compare && payload.compare.years || []).forEach(function (_, index) {
      var compareCanvas = document.getElementById('heatmap-compare-map-' + index);
      if (compareCanvas) {
        compareCanvas.innerHTML =
          '<div class="fleet-empty" style="height:100%;min-height:220px;display:flex;align-items:center;justify-content:center;padding:24px;">' +
            message +
          '</div>';
      }
    });
  }

  function renderOverview() {
    payload = getPayload();
    var canvas = document.getElementById('heatmap-overview');
    if (!canvas || !payload || !payload.overview || !window.google || !google.maps) {
      return;
    }

    overviewMap = new google.maps.Map(canvas, {
      zoom: 2,
      minZoom: 2,
      center: payload.overview.map_center || { lat: 0, lng: 0 },
      mapTypeId: google.maps.MapTypeId.ROADMAP,
      streetViewControl: false,
      mapTypeControl: true,
      fullscreenControl: false
    });

    var heatmap = new google.maps.visualization.HeatmapLayer({
      data: heatLayerData(payload.overview.points || []),
      radius: 28,
      opacity: 0.75
    });
    heatmap.setMap(overviewMap);
    drawPolygons(overviewMap, payload.polygons || []);

    // Auto-fit overview to its points if any
    if (payload.overview.points && payload.overview.points.length) {
      var bounds = new google.maps.LatLngBounds();
      payload.overview.points.forEach(function (p) {
        var lat = parseFloat(p.lat);
        var lng = parseFloat(p.lng);
        if (!isNaN(lat) && !isNaN(lng)) {
          bounds.extend(new google.maps.LatLng(lat, lng));
        }
      });
      if (!bounds.isEmpty()) {
        overviewMap.fitBounds(bounds);
      }
    }

    initOverviewSearch();
  }

  function initOverviewSearch() {
    var input = document.getElementById('heatmap-search-input');
    if (!input || !overviewMap || !google.maps.places) {
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
        overviewMap.panTo(place.geometry.location);
        overviewMap.setZoom(12);
      }
    });
  }

  function fitOverview() {
    payload = getPayload();
    if (!overviewMap || !payload || !payload.overview || !payload.overview.points || !payload.overview.points.length) {
      return;
    }

    var bounds = new google.maps.LatLngBounds();
    payload.overview.points.forEach(function (point) {
      bounds.extend(new google.maps.LatLng(parseFloat(point.lat), parseFloat(point.lng)));
    });
    overviewMap.fitBounds(bounds);
  }

  function renderCompare() {
    payload = getPayload();
    if (!payload || !payload.compare || !payload.compare.years || !window.google || !google.maps) {
      return;
    }

    compareMaps = [];
    var deferredResize = [];
    (payload.compare.years || []).forEach(function (yearItem, index) {
      var canvas = document.getElementById('heatmap-compare-map-' + index);
      if (!canvas) {
        return;
      }
      // Ensure the canvas has explicit dimensions before init
      if (!canvas.style.height) {
        canvas.style.height = '360px';
      }
      if (!canvas.style.width) {
        canvas.style.width = '100%';
      }

      var map = new google.maps.Map(canvas, {
        zoom: 2,
        minZoom: 2,
        center: { lat: 0, lng: 0 },
        mapTypeId: google.maps.MapTypeId.ROADMAP,
        streetViewControl: false,
        mapTypeControl: true,
        fullscreenControl: false
      });

      var heatmap = new google.maps.visualization.HeatmapLayer({
        data: heatLayerData(yearItem.points || []),
        radius: 24,
        opacity: 0.7
      });
      heatmap.setMap(map);

      compareMaps.push(map);
      deferredResize.push(map);

      // Auto-fit each compare map to its points if any
      if (yearItem.points && yearItem.points.length) {
        var bounds = new google.maps.LatLngBounds();
        yearItem.points.forEach(function (p) {
          var lat = parseFloat(p.lat);
          var lng = parseFloat(p.lng);
          if (!isNaN(lat) && !isNaN(lng)) {
            bounds.extend(new google.maps.LatLng(lat, lng));
          }
        });
        if (!bounds.isEmpty()) {
          map.fitBounds(bounds);
        }
      }
    });

    // Trigger a resize after the cards have painted so all 3 maps render correctly
    window.setTimeout(function () {
      deferredResize.forEach(function (m) {
        google.maps.event.trigger(m, 'resize');
      });
    }, 250);

    renderCompareChart();
  }

  function renderCompareChart() {
    payload = getPayload();
    var chartElement = document.getElementById('heatmap-compare-chart');
    if (!chartElement) {
      return;
    }

    if (compareChart) {
      try { compareChart.destroy(); } catch (e) { /* noop */ }
    }
    chartElement.innerHTML = '';

    if (!window.ApexCharts) {
      chartElement.innerHTML = '<div class="text-muted p-4">Chart library is still loading…</div>';
      return;
    }

    if (!payload || !payload.compare || !payload.compare.chart) {
      chartElement.innerHTML = '<div class="text-muted p-4">No trip statistics available for the current filter.</div>';
      return;
    }

    compareChart = new ApexCharts(chartElement, {
      chart: {
        type: 'line',
        height: 320,
        toolbar: { show: false },
        animations: { enabled: true }
      },
      stroke: {
        curve: 'smooth',
        width: 3
      },
      series: [
        {
          name: 'Ride Trips',
          data: payload.compare.chart.rides || []
        },
        {
          name: 'Parcel Trips',
          data: payload.compare.chart.parcels || []
        }
      ],
      xaxis: {
        categories: payload.compare.chart.categories || []
      },
      colors: ['#27b7a8', '#4f8ef7'],
      grid: {
        borderColor: 'rgba(148, 163, 184, 0.18)'
      },
      dataLabels: {
        enabled: false
      },
      legend: {
        position: 'top'
      }
    });

    compareChart.render();
    // Re-render once after the parent flex/grid layout settles
    window.setTimeout(function () {
      if (compareChart) {
        try { compareChart.render(); } catch (e) { /* noop */ }
      }
    }, 300);
  }

  function submitZoneSelection(id) {
    payload = getPayload();
    var base = payload && payload.mode === 'compare'
      ? COMPANY_ADMIN_URL + '/heat-map/compare'
      : COMPANY_ADMIN_URL + '/heat-map';

    var target = new URL(base, window.location.origin);
    var params = new URLSearchParams(window.location.search);

    if (parseInt(id, 10) > 0) {
      params.set('location_id', id);
    } else {
      params.delete('location_id');
    }

    target.search = params.toString();
    window.location.href = target.toString();
  }

  function toggleRangePanel() {
    $('#heatmap-range-panel').toggleClass('active');
  }

  function applyPreset(range) {
    $('#heatmap-range-input').val(range);
    $('.heatmap-range-preset').removeClass('active');
    $('.heatmap-range-preset[data-range="' + range + '"]').addClass('active');

    if (range === 'all') {
      $('#heatmap-from-date').val('');
      $('#heatmap-to-date').val('');
    }
  }

  function clearRange() {
    $('#heatmap-from-date').val('');
    $('#heatmap-to-date').val('');
    applyPreset('all');
  }

  function waitForGoogleMaps() {
    if (heatMapBooted) {
      return;
    }

    if (!window.google || !google.maps) {
      window.setTimeout(function () {
        waitForGoogleMaps();
      }, 250);
      return;
    }

    payload = getPayload();

    if (payload && payload.mode === 'compare') {
      renderCompare();
    } else {
      renderOverview();
    }
    heatMapBooted = true;
  }

  window.initHeatMap = waitForGoogleMaps;

  $(document).on('click', '.heatmap-zone-item', function () {
    submitZoneSelection($(this).data('id'));
  });

  $(document).on('click', '#heatmap-range-display', toggleRangePanel);
  $(document).on('click', '.heatmap-range-preset', function () {
    applyPreset($(this).data('range'));
  });
  $(document).on('click', '#heatmap-range-clear', clearRange);
  $(document).on('click', '#heatmap-fit-btn', fitOverview);

  $(function () {
    window.setTimeout(function () {
      if (!window.google || !google.maps) {
        renderHeatMapUnavailable('Google Maps could not be loaded for this page.');
        return;
      }

      waitForGoogleMaps();
    }, 5000);

    waitForGoogleMaps();
  });
})(jQuery, window);
