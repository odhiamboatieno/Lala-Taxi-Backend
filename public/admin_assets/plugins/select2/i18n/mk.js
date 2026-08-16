/*! Select2 4.0.0 | https://github.com/select2/select2/blob/master/LICENSE.md */

(function () {
    if (jQuery && jQuery.fn && jQuery.fn.select2 && jQuery.fn.select2.amd) {
        var e = jQuery.fn.select2.amd;
    }

    return e.define('select2/i18n/mk', [], function () {
        return {
    inputTooLong: function (args) {
      var overChars = args.input.length - args.maximum;

      var message = 'Ð’Ðµ Ð¼Ð¾Ð»Ð¸Ð¼Ðµ Ð²Ð½ÐµÑÐµÑ‚Ðµ ' + args.maximum + ' Ð¿Ð¾Ð¼Ð°Ð»ÐºÑƒ ÐºÐ°Ñ€Ð°ÐºÑ‚ÐµÑ€';

      if (args.maximum !== 1) {
        message += 'Ð¸';
      }

      return message;
    },
    inputTooShort: function (args) {
      var remainingChars = args.minimum - args.input.length;

      var message = 'Ð’Ðµ Ð¼Ð¾Ð»Ð¸Ð¼Ðµ Ð²Ð½ÐµÑÐµÑ‚Ðµ ÑƒÑˆÑ‚Ðµ ' + args.maximum + ' ÐºÐ°Ñ€Ð°ÐºÑ‚ÐµÑ€';

      if (args.maximum !== 1) {
        message += 'Ð¸';
      }

      return message;
    },
    loadingMore: function () {
      return 'Ð’Ñ‡Ð¸Ñ‚ÑƒÐ²Ð°ÑšÐµ Ñ€ÐµÐ·ÑƒÐ»Ñ‚Ð°Ñ‚Ð¸â€¦';
    },
    maximumSelected: function (args) {
      var message = 'ÐœÐ¾Ð¶ÐµÑ‚Ðµ Ð´Ð° Ð¸Ð·Ð±ÐµÑ€ÐµÑ‚Ðµ ÑÐ°Ð¼Ð¾ ' + args.maximum + ' ÑÑ‚Ð°Ð²Ðº';

      if (args.maximum === 1) {
        message += 'Ð°';
      } else {
        message += 'Ð¸';
      }

      return message;
    },
    noResults: function () {
      return 'ÐÐµÐ¼Ð° Ð¿Ñ€Ð¾Ð½Ð°Ñ˜Ð´ÐµÐ½Ð¾ ÑÐ¾Ð²Ð¿Ð°Ñ“Ð°ÑšÐ°';
    },
    searching: function () {
      return 'ÐŸÑ€ÐµÐ±Ð°Ñ€ÑƒÐ²Ð°ÑšÐµâ€¦';
    }
  }
    }, { define: e.define, require: e.require });
})();

