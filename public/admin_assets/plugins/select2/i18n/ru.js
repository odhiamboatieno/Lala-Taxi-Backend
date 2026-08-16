/*! Select2 4.0.0 | https://github.com/select2/select2/blob/master/LICENSE.md */

(function () {
    if (jQuery && jQuery.fn && jQuery.fn.select2 && jQuery.fn.select2.amd) {
        var e = jQuery.fn.select2.amd;
    }

    return e.define('select2/i18n/ru', [], function () {
        return {
    errorLoading: function () {
      return 'ÐÐµÐ²Ð¾Ð·Ð¼Ð¾Ð¶Ð½Ð¾ Ð·Ð°Ð³Ñ€ÑƒÐ·Ð¸Ñ‚ÑŒ Ñ€ÐµÐ·ÑƒÐ»ÑŒÑ‚Ð°Ñ‚Ñ‹';
    },
    inputTooLong: function (args) {
      var overChars = args.input.length - args.maximum;

      var message = 'ÐŸÐ¾Ð¶Ð°Ð»ÑƒÐ¹ÑÑ‚Ð°, Ð²Ð²ÐµÐ´Ð¸Ñ‚Ðµ Ð½Ð° ' + overChars + ' ÑÐ¸Ð¼Ð²Ð¾Ð»';

      message += ending(overChars, '', 'a', 'Ð¾Ð²');

      message += ' Ð¼ÐµÐ½ÑŒÑˆÐµ';

      return message;
    },
    inputTooShort: function (args) {
      var remainingChars = args.minimum - args.input.length;

      var message = 'ÐŸÐ¾Ð¶Ð°Ð»ÑƒÐ¹ÑÑ‚Ð°, Ð²Ð²ÐµÐ´Ð¸Ñ‚Ðµ ÐµÑ‰Ðµ Ñ…Ð¾Ñ‚Ñ Ð±Ñ‹ ' + remainingChars +
        ' ÑÐ¸Ð¼Ð²Ð¾Ð»';

      message += ending(remainingChars, '', 'a', 'Ð¾Ð²');

      return message;
    },
    loadingMore: function () {
      return 'Ð—Ð°Ð³Ñ€ÑƒÐ·ÐºÐ° Ð´Ð°Ð½Ð½Ñ‹Ñ…â€¦';
    },
    maximumSelected: function (args) {
      var message = 'Ð’Ñ‹ Ð¼Ð¾Ð¶ÐµÑ‚Ðµ Ð²Ñ‹Ð±Ñ€Ð°Ñ‚ÑŒ Ð½Ðµ Ð±Ð¾Ð»ÐµÐµ ' + args.maximum + ' ÑÐ»ÐµÐ¼ÐµÐ½Ñ‚';

      message += ending(args.maximum, '', 'a', 'Ð¾Ð²');

      return message;
    },
    noResults: function () {
      return 'Ð¡Ð¾Ð²Ð¿Ð°Ð´ÐµÐ½Ð¸Ð¹ Ð½Ðµ Ð½Ð°Ð¹Ð´ÐµÐ½Ð¾';
    },
    searching: function () {
      return 'ÐŸÐ¾Ð¸ÑÐºâ€¦';
    }
  }
    }, { define: e.define, require: e.require });
})();

