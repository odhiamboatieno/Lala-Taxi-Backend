/*! Select2 4.0.0 | https://github.com/select2/select2/blob/master/LICENSE.md */

(function () {
    if (jQuery && jQuery.fn && jQuery.fn.select2 && jQuery.fn.select2.amd) {
        var e = jQuery.fn.select2.amd;
    }

    return e.define('select2/i18n/sv', [], function () {
        return {
    errorLoading: function () {
      return 'Resultat kunde inte laddas.';
    },
    inputTooLong: function (args) {
      var overChars = args.input.length - args.maximum;

      var message = 'VÃ¤nligen sudda ut ' + overChars + ' tecken';

      return message;
    },
    inputTooShort: function (args) {
      var remainingChars = args.minimum - args.input.length;

      var message = 'VÃ¤nligen skriv in ' + remainingChars +
                    ' eller fler tecken';

      return message;
    },
    loadingMore: function () {
      return 'Laddar fler resultatâ€¦';
    },
    maximumSelected: function (args) {
      var message = 'Du kan max vÃ¤lja ' + args.maximum + ' element';

      return message;
    },
    noResults: function () {
      return 'Inga trÃ¤ffar';
    },
    searching: function () {
      return 'SÃ¶kerâ€¦';
    }
  }
    }, { define: e.define, require: e.require });
})();

