/*! Select2 4.0.0 | https://github.com/select2/select2/blob/master/LICENSE.md */

(function () {
    if (jQuery && jQuery.fn && jQuery.fn.select2 && jQuery.fn.select2.amd) {
        var e = jQuery.fn.select2.amd;
    }

    return e.define('select2/i18n/lt', [], function () {
        return {
    inputTooLong: function (args) {
      var overChars = args.input.length - args.maximum;

      var message = 'PaÅ¡alinkite ' + overChars + ' simbol';

      message += ending(overChars, 'iÅ³', 'ius', 'Ä¯');

      return message;
    },
    inputTooShort: function (args) {
      var remainingChars = args.minimum - args.input.length;

      var message = 'Ä®raÅ¡ykite dar ' + remainingChars + ' simbol';

      message += ending(remainingChars, 'iÅ³', 'ius', 'Ä¯');

      return message;
    },
    loadingMore: function () {
      return 'Kraunama daugiau rezultatÅ³â€¦';
    },
    maximumSelected: function (args) {
      var message = 'JÅ«s galite pasirinkti tik ' + args.maximum + ' element';

      message += ending(args.maximum, 'Å³', 'us', 'Ä…');

      return message;
    },
    noResults: function () {
      return 'AtitikmenÅ³ nerasta';
    },
    searching: function () {
      return 'IeÅ¡komaâ€¦';
    }
  }
    }, { define: e.define, require: e.require });
})();

