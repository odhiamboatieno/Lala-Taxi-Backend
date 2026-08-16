(function (global) {
  'use strict';

  if (global.__lucideLoaderInit) return;
  global.__lucideLoaderInit = true;

  var MAP = {
    /* Font Awesome -> Lucide */
    'fa-address-book': 'contact',
    'fa-align-center': 'align-center',
    'fa-align-justify': 'align-justify',
    'fa-align-left': 'align-left',
    'fa-align-right': 'align-right',
    'fa-angle-double-right': 'chevrons-right',
    'fa-angle-left': 'chevron-left',
    'fa-angle-right': 'chevron-right',
    'fa-apple': 'apple',
    'fa-area-chart': 'chart-area',
    'fa-arrows-alt': 'maximize',
    'fa-asterisk': 'asterisk',
    'fa-ban': 'ban',
    'fa-bar-chart': 'bar-chart',
    'fa-bars': 'menu',
    'fa-bell': 'bell',
    'fa-bolt': 'zap',
    'fa-briefcase': 'briefcase',
    'fa-bold': 'bold',
    'fa-building': 'building',
    'fa-bullhorn': 'megaphone',
    'fa-calendar': 'calendar',
    'fa-calendar-check-o': 'calendar-check',
    'fa-caret-down': 'chevron-down',
    'fa-chain-broken': 'unlink',
    'fa-check': 'check',
    'fa-check-circle': 'check-circle',
    'fa-check-square': 'check-square',
    'fa-circle': 'circle',
    'fa-circle-o': 'circle',
    'fa-close': 'x',
    'fa-code': 'code',
    'fa-cog': 'settings',
    'fa-cogs': 'settings',
    'fa-comment': 'message-square',
    'fa-comments': 'messages-square',
    'fa-credit-card': 'credit-card',
    'fa-cube': 'box',
    'fa-dashboard': 'gauge',
    'fa-database': 'database',
    'fa-dollar': 'dollar-sign',
    'fa-envelope': 'mail',
    'fa-envelope-o': 'mail',
    'fa-eraser': 'eraser',
    'fa-eur': 'euro',
    'fa-exclamation-triangle': 'alert-triangle',
    'fa-eye': 'eye',
    'fa-facebook': 'facebook',
    'fa-file-excel-o': 'file-spreadsheet',
    'fa-file-text': 'file-text',
    'fa-file-text-o': 'file-text',
    'fa-flag-checkered': 'flag',
    'fa-floppy-o': 'save',
    'fa-folder': 'folder',
    'fa-font': 'type',
    'fa-gear': 'settings',
    'fa-globe': 'globe',
    'fa-google': 'globe',
    'fa-google-wallet': 'wallet',
    'fa-home': 'home',
    'fa-headset': 'headset',
    'fa-id-card': 'id-card',
    'fa-indent': 'indent-increase',
    'fa-info-circle': 'info',
    'fa-italic': 'italic',
    'fa-key': 'key',
    'fa-language': 'languages',
    'fa-level-up': 'arrow-up',
    'fa-life-ring': 'life-buoy',
    'fa-line-chart': 'line-chart',
    'fa-link': 'link',
    'fa-list': 'list',
    'fa-list-alt': 'list',
    'fa-list-ol': 'list-ordered',
    'fa-list-ul': 'list',
    'fa-lock': 'lock',
    'fa-long-arrow-right': 'arrow-right',
    'fa-magic': 'wand',
    'fa-map-marker': 'map-pin',
    'fa-map-o': 'map',
    'fa-minus': 'minus',
    'fa-mobile': 'smartphone',
    'fa-money': 'banknote',
    'fa-newspaper-o': 'newspaper',
    'fa-outdent': 'indent-decrease',
    'fa-paper-plane-o': 'send',
    'fa-paypal': 'wallet',
    'fa-phone': 'phone',
    'fa-picture-o': 'image',
    'fa-plane-departure': 'plane-takeoff',
    'fa-plus': 'plus',
    'fa-print': 'printer',
    'fa-quote-right': 'quote',
    'fa-random': 'shuffle',
    'fa-refresh': 'refresh-cw',
    'fa-reorder': 'menu',
    'fa-repeat': 'repeat',
    'fa-server': 'server',
    'fa-share-alt': 'share',
    'fa-shield': 'shield',
    'fa-sitemap': 'network',
    'fa-sliders': 'sliders',
    'fa-star': 'star',
    'fa-strikethrough': 'strikethrough',
    'fa-support': 'headphones',
    'fa-table': 'table',
    'fa-taxi': 'car-taxi-front',
    'fa-times': 'x',
    'fa-trash': 'trash',
    'fa-trophy': 'trophy',
    'fa-underline': 'underline',
    'fa-undo': 'undo',
    'fa-unlink': 'unlink',
    'fa-usd': 'dollar-sign',
    'fa-user': 'user',
    'fa-user-plus': 'user-plus',
    'fa-users': 'users',
    'fa-wrench': 'wrench',

    /* Glyphicons -> Lucide */
    'glyphicon-calendar': 'calendar',
    'glyphicon-chevron-down': 'chevron-down',
    'glyphicon-chevron-left': 'chevron-left',
    'glyphicon-chevron-right': 'chevron-right',
    'glyphicon-chevron-up': 'chevron-up',
    'glyphicon-edit': 'pencil',
    'glyphicon-info-sign': 'info',
    'glyphicon-ok': 'check',
    'glyphicon-remove': 'x',
    'glyphicon-screenshot': 'camera',
    'glyphicon-thumbs-down': 'thumbs-down',
    'glyphicon-thumbs-up': 'thumbs-up',
    'glyphicon-time': 'clock',
    'glyphicon-trash': 'trash',

    /* Remixicon -> Lucide */
    'ri-add-line': 'plus',
    'ri-arrow-down-s-line': 'chevron-down',
    'ri-arrow-up-line': 'arrow-up',
    'ri-award-line': 'award',
    'ri-calendar-check-line': 'calendar-check',
    'ri-car-line': 'car',
    'ri-checkbox-circle-line': 'check-circle',
    'ri-delete-bin-line': 'trash',
    'ri-download-line': 'download',
    'ri-eye-line': 'eye',
    'ri-forbid-2-line': 'ban',
    'ri-group-line': 'users',
    'ri-menu-2-line': 'menu',
    'ri-money-dollar-circle-line': 'dollar-sign',
    'ri-motorbike-line': 'bike',
    'ri-pencil-line': 'pencil',
    'ri-reset-right-line': 'rotate-ccw',
    'ri-save-line': 'save',
    'ri-shopping-bag-3-line': 'shopping-bag',
    'ri-taxi-line': 'car-taxi-front',
    'ri-team-line': 'users',
    'ri-toggle-line': 'toggle-left',
    'ri-user-3-line': 'user',
    'ri-user-add-line': 'user-plus',
    'ri-user-follow-line': 'user-plus',
    'ri-user-unfollow-line': 'user-minus',

    /* Bootstrap Icons -> Lucide */
    'bi-activity': 'activity',
    'bi-arrow-bar-left': 'panel-left',
    'bi-arrow-clockwise': 'rotate-cw',
    'bi-arrow-down-right': 'arrow-down-right',
    'bi-arrow-left': 'arrow-left',
    'bi-arrow-left-right': 'arrow-left-right',
    'bi-arrow-repeat': 'repeat',
    'bi-arrow-up-right': 'arrow-up-right',
    'bi-arrow-up-right-circle': 'arrow-up-right',
    'bi-aspect-ratio': 'scaling',
    'bi-bar-chart-line': 'bar-chart',
    'bi-bezier2': 'spline',
    'bi-bounding-box-circles': 'box',
    'bi-box-arrow-right': 'log-out',
    'bi-box-arrow-up-right': 'external-link',
    'bi-box-seam': 'package',
    'bi-brightness-high': 'sun',
    'bi-broadcast-pin': 'radio',
    'bi-calendar-event': 'calendar',
    'bi-calendar2-plus': 'calendar-plus',
    'bi-car-front': 'car-front',
    'bi-car-front-fill': 'car-front',
    'bi-card-list': 'list',
    'bi-cash-coin': 'banknote',
    'bi-cash-stack': 'banknote',
    'bi-chat-dots': 'message-circle',
    'bi-check-lg': 'check',
    'bi-check2-circle': 'check-circle',
    'bi-chevron-down': 'chevron-down',
    'bi-chevron-left': 'chevron-left',
    'bi-chevron-right': 'chevron-right',
    'bi-cloud-arrow-up': 'cloud-upload',
    'bi-cloud-upload': 'cloud-upload',
    'bi-crosshair': 'crosshair',
    'bi-currency-dollar': 'dollar-sign',
    'bi-database': 'database',
    'bi-diagram-3': 'network',
    'bi-directional': 'arrow-left-right',
    'bi-download': 'download',
    'bi-envelope': 'mail',
    'bi-exclamation-triangle': 'alert-triangle',
    'bi-eye': 'eye',
    'bi-file-earmark': 'file',
    'bi-file-earmark-arrow-up': 'file-up',
    'bi-file-earmark-check': 'file-check',
    'bi-filetype-csv': 'file-text',
    'bi-filetype-json': 'braces',
    'bi-folder': 'folder',
    'bi-gear': 'settings',
    'bi-geo-alt': 'map-pin',
    'bi-graph-up': 'trending-up',
    'bi-grid': 'layout-grid',
    'bi-hand-index-thumb': 'hand',
    'bi-hash': 'hash',
    'bi-hourglass-split': 'hourglass',
    'bi-inboxes': 'inbox',
    'bi-info-circle': 'info',
    'bi-life-preserver': 'life-buoy',
    'bi-lightning-charge': 'zap',
    'bi-map': 'map',
    'bi-moon-stars': 'moon-star',
    'bi-palette': 'palette',
    'bi-patch-check-fill': 'badge-check',
    'bi-pause-circle': 'pause-circle',
    'bi-pencil': 'pencil',
    'bi-pencil-square': 'square-pen',
    'bi-people': 'users',
    'bi-person-plus': 'user-plus',
    'bi-play-circle': 'play-circle',
    'bi-plus-circle': 'plus-circle',
    'bi-plus-circle-dotted': 'plus-circle',
    'bi-plus-lg': 'plus',
    'bi-receipt-cutoff': 'receipt',
    'bi-record-circle': 'circle-dot',
    'bi-rocket-takeoff': 'rocket',
    'bi-search': 'search',
    'bi-shield-check': 'shield-check',
    'bi-shield-exclamation': 'shield-alert',
    'bi-signpost-split': 'signpost',
    'bi-sliders': 'sliders',
    'bi-speedometer2': 'gauge',
    'bi-telephone': 'phone',
    'bi-telephone-outbound': 'phone-outgoing',
    'bi-three-dots-vertical': 'more-vertical',
    'bi-toggle-off': 'toggle-left',
    'bi-toggle-on': 'toggle-right',
    'bi-trash': 'trash',
    'bi-trash3': 'trash',
    'bi-truck-flatbed': 'truck',
    'bi-truck-front': 'truck',
    'bi-wallet2': 'wallet',
    'bi-x-circle': 'x-circle',
    'bi-x-circle-fill': 'x-circle',
    'bi-x-lg': 'x'
  };

  var FALLBACK = 'circle';

  function isIconToken(tok) {
    return /^(?:fa|fas|far|fab|fal|fad|glyphicon|ri|bi|ion|pe-7s)-[a-z0-9-]+$/.test(tok);
  }

  function isFamilyToken(tok) {
    return /^(?:fa|fas|far|fab|fal|fad|glyphicon|ri|bi|ion|pe-7s)$/.test(tok);
  }

  function pickIconToken(tokens) {
    var fallback = null;
    for (var i = 0; i < tokens.length; i++) {
      var t = tokens[i];
      if (!isIconToken(t)) continue;
      if (t === 'fa-fw') continue;
      if (/^fa-(?:lg|2x|3x|4x|5x|spin)$/.test(t)) continue;
      if (MAP[t]) return t;
      if (fallback === null) fallback = t;
    }
    return fallback;
  }

  function stripIconTokens(tokens) {
    var keep = [];
    for (var i = 0; i < tokens.length; i++) {
      var t = tokens[i];
      if (isFamilyToken(t) || isIconToken(t)) continue;
      keep.push(t);
    }
    return keep.join(' ');
  }

  function convertElement(el) {
    if (el.getAttribute('data-lucide')) return false;
    var cls = el.getAttribute('class');
    if (!cls || cls.indexOf('-') < 0) return false;
    var tokens = cls.split(/\s+/).filter(Boolean);
    var tok = pickIconToken(tokens);
    if (!tok) return false;
    var rest = stripIconTokens(tokens);
    if (rest) el.setAttribute('class', rest);
    else el.removeAttribute('class');
    el.setAttribute('data-lucide', MAP[tok] || FALLBACK);
    return true;
  }

  function convertAll(root) {
    if (!root || root.nodeType !== 1) root = document;
    var els = root.querySelectorAll('[class*="fa-"], [class*="glyphicon"], [class*="ri-"], [class*="bi-"], [class*="ion-"], [class*="pe-7s-"]');
    var dirty = false;
    for (var i = 0; i < els.length; i++) {
      if (convertElement(els[i])) dirty = true;
    }
    return dirty;
  }

  function renderIcons() {
    if (global.lucide) {
      try { global.lucide.createIcons(); } catch (e) { /* noop */ }
    }
  }

  var pending = [];
  var scheduled = false;

  function schedule(root) {
    pending.push(root);
    if (scheduled) return;
    scheduled = true;
    setTimeout(function () {
      scheduled = false;
      var roots = pending;
      pending = [];
      var dirty = false;
      for (var i = 0; i < roots.length; i++) {
        if (convertAll(roots[i])) dirty = true;
      }
      if (dirty) renderIcons();
    }, 0);
  }

  function start() {
    schedule(document);
    if (typeof MutationObserver === 'function') {
      var observer = new MutationObserver(function (mutations) {
        var roots = [];
        for (var i = 0; i < mutations.length; i++) {
          var added = mutations[i].addedNodes;
          for (var j = 0; j < added.length; j++) {
            var node = added[j];
            if (node.nodeType !== 1) continue;
            if (node.nodeName === 'SCRIPT' || node.nodeName === 'STYLE') continue;
            roots.push(node);
          }
        }
        if (roots.length) schedule(roots.length === 1 ? roots[0] : document);
      });
      observer.observe(document.documentElement, { childList: true, subtree: true });
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', start);
  } else {
    start();
  }
})(window);