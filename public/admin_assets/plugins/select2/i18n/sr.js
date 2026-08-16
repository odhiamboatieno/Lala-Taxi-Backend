/*! Select2 4.0.0 | https://github.com/select2/select2/blob/master/LICENSE.md */

(function () {
    if (jQuery && jQuery.fn && jQuery.fn.select2 && jQuery.fn.select2.amd) {
        var e = jQuery.fn.select2.amd;
    }

    return e.define('select2/i18n/sr', [], function () {
        return {
    inputTooLong: function (args) {
      var overChars = args.input.length - args.maximum;

      var message = 'ObriÅ¡ite ' + overChars + ' simbol';

      message += ending(overChars, '', 'a', 'a');

      return message;
    },
    inputTooShort: function (args) {
      var remainingChars = args.minimum - args.input.length;

      var message = 'Ukucajte bar joÅ¡ ' + remainingChars + ' simbol';

      message += ending(remainingChars, '', 'a', 'a');

      return message;
    },
    loadingMore: function () {
      return 'Preuzimanje joÅ¡ rezultataâ€¦';
    },
    maximumSelected: function (args) {
      var message = 'MoÅ¾ete izabrati samo ' + args.maximum + ' stavk';

      message += ending(args.maximum, 'u', 'e', 'i');

      return message;
    },
    noResults: function () {
      return 'NiÅ¡ta nije pronaÄ‘eno';
    },
    searching: function () {
      return 'Pretragaâ€¦';
    }
  }
    }, { define: e.define, require: e.require });
})();

