/*! Select2 4.0.0 | https://github.com/select2/select2/blob/master/LICENSE.md */

(function () {
    if (jQuery && jQuery.fn && jQuery.fn.select2 && jQuery.fn.select2.amd) {
        var e = jQuery.fn.select2.amd;
    }

    return e.define('select2/i18n/tr', [], function () {
        return {
    inputTooLong: function (args) {
      var overChars = args.input.length - args.maximum;

      var message = overChars + ' karakter daha girmelisiniz';

      return message;
    },
    inputTooShort: function (args) {
      var remainingChars = args.minimum - args.input.length;

      var message = 'En az ' + remainingChars + ' karakter daha girmelisiniz';

      return message;
    },
    loadingMore: function () {
      return 'Daha fazlaâ€¦';
    },
    maximumSelected: function (args) {
      var message = 'Sadece ' + args.maximum + ' seÃ§im yapabilirsiniz';

      return message;
    },
    noResults: function () {
      return 'SonuÃ§ bulunamadÄ±';
    },
    searching: function () {
      return 'AranÄ±yorâ€¦';
    }
  }
    }, { define: e.define, require: e.require });
})();

