/*! Select2 4.0.0 | https://github.com/select2/select2/blob/master/LICENSE.md */

(function () {
    if (jQuery && jQuery.fn && jQuery.fn.select2 && jQuery.fn.select2.amd) {
        var e = jQuery.fn.select2.amd;
    }

    return e.define('select2/i18n/fi', [], function () {
        return {
    inputTooLong: function (args) {
      var overChars = args.input.length - args.maximum;

      return 'Ole hyvÃ¤ ja anna ' + overChars + ' merkkiÃ¤ vÃ¤hemmÃ¤n';
    },
    inputTooShort: function (args) {
      var remainingChars = args.minimum - args.input.length;

      return 'Ole hyvÃ¤ ja anna ' + remainingChars + ' merkkiÃ¤ lisÃ¤Ã¤';
    },
    loadingMore: function () {
      return 'Ladataan lisÃ¤Ã¤ tuloksiaâ€¦';
    },
    maximumSelected: function (args) {
      return 'Voit valita ainoastaan ' + args.maximum + ' kpl';
    },
    noResults: function () {
      return 'Ei tuloksia';
    },
    searching: function () {

    }
  }
    }, { define: e.define, require: e.require });
})();

