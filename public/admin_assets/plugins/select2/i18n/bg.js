/*! Select2 4.0.0 | https://github.com/select2/select2/blob/master/LICENSE.md */

(function () {
    if (jQuery && jQuery.fn && jQuery.fn.select2 && jQuery.fn.select2.amd) {
        var e = jQuery.fn.select2.amd;
    }

    return e.define('select2/i18n/bg', [], function () {
        return {
    inputTooLong: function (args) {
      var overChars = args.input.length - args.maximum;

      var message = 'ÐœÐ¾Ð»Ñ Ð²ÑŠÐ²ÐµÐ´ÐµÑ‚Ðµ Ñ ' + overChars + ' Ð¿Ð¾-Ð¼Ð°Ð»ÐºÐ¾ ÑÐ¸Ð¼Ð²Ð¾Ð»';

      if (overChars > 1) {
        message += 'a';
      }

      return message;
    },
    inputTooShort: function (args) {
      var remainingChars = args.minimum - args.input.length;

      var message = 'ÐœÐ¾Ð»Ñ Ð²ÑŠÐ²ÐµÐ´ÐµÑ‚Ðµ Ð¾Ñ‰Ðµ ' + remainingChars + ' ÑÐ¸Ð¼Ð²Ð¾Ð»';

      if (remainingChars > 1) {
        message += 'a';
      }

      return message;
    },
    loadingMore: function () {
      return 'Ð—Ð°Ñ€ÐµÐ¶Ð´Ð°Ñ‚ ÑÐµ Ð¾Ñ‰Ðµâ€¦';
    },
    maximumSelected: function (args) {
      var message = 'ÐœÐ¾Ð¶ÐµÑ‚Ðµ Ð´Ð° Ð½Ð°Ð¿Ñ€Ð°Ð²Ð¸Ñ‚Ðµ Ð´Ð¾ ' + args.maximum + ' ';

      if (args.maximum > 1) {
        message += 'Ð¸Ð·Ð±Ð¾Ñ€Ð°';
      } else {
        message += 'Ð¸Ð·Ð±Ð¾Ñ€';
      }

      return message;
    },
    noResults: function () {
      return 'ÐÑÐ¼Ð° Ð½Ð°Ð¼ÐµÑ€ÐµÐ½Ð¸ ÑÑŠÐ²Ð¿Ð°Ð´ÐµÐ½Ð¸Ñ';
    },
    searching: function () {
      return 'Ð¢ÑŠÑ€ÑÐµÐ½Ðµâ€¦';
    }
  }
    }, { define: e.define, require: e.require });
})();

