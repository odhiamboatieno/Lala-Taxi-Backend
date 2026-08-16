/*! Select2 4.0.0 | https://github.com/select2/select2/blob/master/LICENSE.md */

(function () {
    if (jQuery && jQuery.fn && jQuery.fn.select2 && jQuery.fn.select2.amd) {
        var e = jQuery.fn.select2.amd;
    }

    return e.define('select2/i18n/lv', [], function () {
        return {
    inputTooLong: function (args) {
      var overChars = args.input.length - args.maximum;

      var message = 'LÅ«dzu ievadiet par  ' + overChars;

      message += ' simbol' + ending(overChars, 'iem', 'u', 'iem');

      return message + ' mazÄk';
    },
    inputTooShort: function (args) {
      var remainingChars = args.minimum - args.input.length;

      var message = 'LÅ«dzu ievadiet vÄ“l ' + remainingChars;

      message += ' simbol' + ending(remainingChars, 'us', 'u', 'us');

      return message;
    },
    loadingMore: function () {
      return 'Datu ielÄdeâ€¦';
    },
    maximumSelected: function (args) {
      var message = 'JÅ«s varat izvÄ“lÄ“ties ne vairÄk kÄ ' + args.maximum;

      message += ' element' + ending(args.maximum, 'us', 'u', 'us');

      return message;
    },
    noResults: function () {
      return 'SakritÄ«bu nav';
    },
    searching: function () {
      return 'MeklÄ“Å¡anaâ€¦';
    }
  }
    }, { define: e.define, require: e.require });
})();

