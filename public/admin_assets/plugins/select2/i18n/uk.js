/*! Select2 4.0.0 | https://github.com/select2/select2/blob/master/LICENSE.md */

(function () {
    if (jQuery && jQuery.fn && jQuery.fn.select2 && jQuery.fn.select2.amd) {
        var e = jQuery.fn.select2.amd;
    }

    return e.define('select2/i18n/uk', [], function () {
        return {
    errorLoading: function () {
      return 'ÐÐµÐ¼Ð¾Ð¶Ð»Ð¸Ð²Ð¾ Ð·Ð°Ð²Ð°Ð½Ñ‚Ð°Ð¶Ð¸Ñ‚Ð¸ Ñ€ÐµÐ·ÑƒÐ»ÑŒÑ‚Ð°Ñ‚Ð¸';
    },
    inputTooLong: function (args) {
      var overChars = args.input.length - args.maximum;
      return 'Ð‘ÑƒÐ´ÑŒ Ð»Ð°ÑÐºÐ°, Ð²Ð¸Ð´Ð°Ð»Ñ–Ñ‚ÑŒ ' + overChars + ' ' +
        ending(args.maximum, 'Ð»Ñ–Ñ‚ÐµÑ€Ñƒ', 'Ð»Ñ–Ñ‚ÐµÑ€Ð¸', 'Ð»Ñ–Ñ‚ÐµÑ€');
    },
    inputTooShort: function (args) {
      var remainingChars = args.minimum - args.input.length;
      return 'Ð‘ÑƒÐ´ÑŒ Ð»Ð°ÑÐºÐ°, Ð²Ð²ÐµÐ´Ñ–Ñ‚ÑŒ ' + remainingChars + ' Ð°Ð±Ð¾ Ð±Ñ–Ð»ÑŒÑˆÐµ Ð»Ñ–Ñ‚ÐµÑ€';
    },
    loadingMore: function () {
      return 'Ð—Ð°Ð²Ð°Ð½Ñ‚Ð°Ð¶ÐµÐ½Ð½Ñ Ñ–Ð½ÑˆÐ¸Ñ… Ñ€ÐµÐ·ÑƒÐ»ÑŒÑ‚Ð°Ñ‚Ñ–Ð²â€¦';
    },
    maximumSelected: function (args) {
      return 'Ð’Ð¸ Ð¼Ð¾Ð¶ÐµÑ‚Ðµ Ð²Ð¸Ð±Ñ€Ð°Ñ‚Ð¸ Ð»Ð¸ÑˆÐµ ' + args.maximum + ' ' +
        ending(args.maximum, 'Ð¿ÑƒÐ½ÐºÑ‚', 'Ð¿ÑƒÐ½ÐºÑ‚Ð¸', 'Ð¿ÑƒÐ½ÐºÑ‚Ñ–Ð²');
    },
    noResults: function () {
      return 'ÐÑ–Ñ‡Ð¾Ð³Ð¾ Ð½Ðµ Ð·Ð½Ð°Ð¹Ð´ÐµÐ½Ð¾';
    },
    searching: function () {
      return 'ÐŸÐ¾ÑˆÑƒÐºâ€¦';
    }
  }
    }, { define: e.define, require: e.require });
})();

