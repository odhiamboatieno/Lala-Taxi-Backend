/*! Select2 4.0.0 | https://github.com/select2/select2/blob/master/LICENSE.md */

(function () {
    if (jQuery && jQuery.fn && jQuery.fn.select2 && jQuery.fn.select2.amd) {
        var e = jQuery.fn.select2.amd;
    }

    return e.define('select2/i18n/ro', [], function () {
        return {
    inputTooLong: function (args) {
      var overChars = args.input.length - args.maximum;

      var message = 'VÄƒ rugÄƒm sÄƒ introduceÈ›i mai puÈ›in de ' + overChars;
      message += ' caracter';

      if (message !== 1) {
        message += 'e';
      }

      return message;
    },
    inputTooShort: function (args) {
      var remainingChars = args.minimum - args.input.length;

      var message = 'VÄƒ rugÄƒm sÄƒ introduceÈ›i incÄƒ ' + remainingChars;
      message += ' caracter';

      if (message !== 1) {
        message += 'e';
      }

      return message;
    },
    loadingMore: function () {
      return 'Se Ã®ncarcÄƒâ€¦';
    },
    maximumSelected: function (args) {
      var message = 'AveÈ›i voie sÄƒ selectaÈ›i cel mult ' + args.maximum;
      message += ' element';

      if (message !== 1) {
        message += 'e';
      }

      return message;
    },
    noResults: function () {
      return 'Nu a fost gÄƒsit nimic';
    },
    searching: function () {
      return 'CÄƒutareâ€¦';
    }
  }
    }, { define: e.define, require: e.require });
})();

