/*! Select2 4.0.0 | https://github.com/select2/select2/blob/master/LICENSE.md */

(function () {
    if (jQuery && jQuery.fn && jQuery.fn.select2 && jQuery.fn.select2.amd) {
        var e = jQuery.fn.select2.amd;
    }

    return e.define('select2/i18n/de', [], function () {
        return {
    inputTooLong: function (args) {
      var overChars = args.input.length - args.maximum;

      return 'Bitte ' + overChars + ' Zeichen weniger eingeben';
    },
    inputTooShort: function (args) {
      var remainingChars = args.minimum - args.input.length;

      return 'Bitte ' + remainingChars + ' Zeichen mehr eingeben';
    },
    loadingMore: function () {
      return 'Lade mehr Ergebnisseâ€¦';
    },
    maximumSelected: function (args) {
      var message = 'Sie kÃ¶nnen nur ' + args.maximum + ' Eintr';

      if (args.maximum === 1) {
        message += 'ag';
      } else {
        message += 'Ã¤ge';
      }

      message += ' auswÃ¤hlen';

      return message;
    },
    noResults: function () {
      return 'Keine Ãœbereinstimmungen gefunden';
    },
    searching: function () {
      return 'Sucheâ€¦';
    }
  }
    }, { define: e.define, require: e.require });
})();

