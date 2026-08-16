/*! Select2 4.0.0 | https://github.com/select2/select2/blob/master/LICENSE.md */

(function () {
    if (jQuery && jQuery.fn && jQuery.fn.select2 && jQuery.fn.select2.amd) {
        var e = jQuery.fn.select2.amd;
    }

    return e.define('select2/i18n/hu', [], function () {
        return {
    inputTooLong: function (args) {
      var overChars = args.input.length - args.maximum;

      return 'TÃºl hosszÃº. ' + overChars + ' karakterrel tÃ¶bb, mint kellene.';
    },
    inputTooShort: function (args) {
      var remainingChars = args.minimum - args.input.length;

      return 'TÃºl rÃ¶vid. MÃ©g ' + remainingChars + ' karakter hiÃ¡nyzik.';
    },
    loadingMore: function () {
      return 'TÃ¶ltÃ©sâ€¦';
    },
    maximumSelected: function (args) {
      return 'Csak ' + args.maximum + ' elemet lehet kivÃ¡lasztani.';
    },
    noResults: function () {
      return 'Nincs talÃ¡lat.';
    },
    searching: function () {
      return 'KeresÃ©sâ€¦';
    }
  }
    }, { define: e.define, require: e.require });
})();

