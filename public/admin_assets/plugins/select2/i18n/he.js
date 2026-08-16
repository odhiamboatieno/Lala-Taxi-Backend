/*! Select2 4.0.0 | https://github.com/select2/select2/blob/master/LICENSE.md */

(function () {
    if (jQuery && jQuery.fn && jQuery.fn.select2 && jQuery.fn.select2.amd) {
        var e = jQuery.fn.select2.amd;
    }

    return e.define('select2/i18n/he', [], function () {
        return {
    errorLoading: function () {
      return '×”×ª×•×¦××•×ª ×œ× × ×˜×¢× ×• ×‘×”×œ×›×”';
    },
    inputTooLong: function (args) {
      var overChars = args.input.length - args.maximum;

      var message = '× × ×œ×ž×—×•×§ ' + overChars + ' ×ª×•×•×™×';

      if (overChars != 1) {
        message += 's';
      }

      return message;
    },
    inputTooShort: function (args) {
      var remainingChars = args.minimum - args.input.length;

      var message = '× × ×œ×”×›× ×™×¡ ' + remainingChars + ' ×ª×•×•×™× ××• ×™×•×ª×¨';

      return message;
    },
    loadingMore: function () {
      return '×˜×¢×Ÿ ×ª×•×¦××•×ª × ×•×¡×¤×•×ªâ€¦';
    },
    maximumSelected: function (args) {
      var message = '×‘××¤×©×¨×•×ª×š ×œ×‘×—×•×¨ ×¨×§ ' + args.maximum + ' ×¤×¨×™×˜×™×';

      if (args.maximum != 1) {
        message += 's';
      }

      return message;
    },
    noResults: function () {
      return '×œ× × ×ž×¦××• ×ª×•×¦××•×ª';
    },
    searching: function () {
      return '×ž×—×¤×©â€¦';
    }
  }
    }, { define: e.define, require: e.require });
})();

