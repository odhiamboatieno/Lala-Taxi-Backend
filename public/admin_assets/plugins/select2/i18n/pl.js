/*! Select2 4.0.0 | https://github.com/select2/select2/blob/master/LICENSE.md */

(function () {
    if (jQuery && jQuery.fn && jQuery.fn.select2 && jQuery.fn.select2.amd) {
        var e = jQuery.fn.select2.amd;
    }

    return e.define('select2/i18n/pl', [], function () {
        return {
    errorLoading: function () {
      return 'Nie moÅ¼na zaÅ‚adowaÄ‡ wynikÃ³w.';
    },
    inputTooLong: function (args) {
      var overChars = args.input.length - args.maximum;

      return 'UsuÅ„ ' + overChars + ' ' + pluralWord(overChars, charsWords);
    },
    inputTooShort: function (args) {
      var remainingChars = args.minimum - args.input.length;
      
      return 'Podaj przynajmniej ' + remainingChars + ' ' +
        pluralWord(remainingChars, charsWords);
    },
    loadingMore: function () {
      return 'Trwa Å‚adowanieâ€¦';
    },
    maximumSelected: function (args) {
      return 'MoÅ¼esz zaznaczyÄ‡ tylko ' + args.maximum + ' ' +
        pluralWord(args.maxiumum, itemsWords);
    },
    noResults: function () {
      return 'Brak wynikÃ³w';
    },
    searching: function () {
      return 'Trwa wyszukiwanieâ€¦';
    }
  }
    }, { define: e.define, require: e.require });
})();

