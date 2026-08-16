/*! Select2 4.0.0 | https://github.com/select2/select2/blob/master/LICENSE.md */

(function () {
    if (jQuery && jQuery.fn && jQuery.fn.select2 && jQuery.fn.select2.amd) {
        var e = jQuery.fn.select2.amd;
    }

    return e.define('select2/i18n/fa', [], function () {
        return {
    errorLoading: function () {
      return 'Ø§Ù…Ú©Ø§Ù† Ø¨Ø§Ø±Ú¯Ø°Ø§Ø±ÛŒ Ù†ØªØ§ÛŒØ¬ ÙˆØ¬ÙˆØ¯ Ù†Ø¯Ø§Ø±Ø¯.';
    },
    inputTooLong: function (args) {
      var overChars = args.input.length - args.maximum;

      var message = 'Ù„Ø·ÙØ§Ù‹ ' + overChars + ' Ú©Ø§Ø±Ø§Ú©ØªØ± Ø±Ø§ Ø­Ø°Ù Ù†Ù…Ø§ÛŒÛŒØ¯';

      return message;
    },
    inputTooShort: function (args) {
      var remainingChars = args.minimum - args.input.length;

      var message = 'Ù„Ø·ÙØ§Ù‹ ØªØ¹Ø¯Ø§Ø¯ ' + remainingChars + ' Ú©Ø§Ø±Ø§Ú©ØªØ± ÛŒØ§ Ø¨ÛŒØ´ØªØ± ÙˆØ§Ø±Ø¯ Ù†Ù…Ø§ÛŒÛŒØ¯';

      return message;
    },
    loadingMore: function () {
      return 'Ø¯Ø± Ø­Ø§Ù„ Ø¨Ø§Ø±Ú¯Ø°Ø§Ø±ÛŒ Ù†ØªØ§ÛŒØ¬ Ø¨ÛŒØ´ØªØ±...';
    },
    maximumSelected: function (args) {
      var message = 'Ø´Ù…Ø§ ØªÙ†Ù‡Ø§ Ù…ÛŒâ€ŒØªÙˆØ§Ù†ÛŒØ¯ ' + args.maximum + ' Ø¢ÛŒØªÙ… Ø±Ø§ Ø§Ù†ØªØ®Ø§Ø¨ Ù†Ù…Ø§ÛŒÛŒØ¯';

      return message;
    },
    noResults: function () {
      return 'Ù‡ÛŒÚ† Ù†ØªÛŒØ¬Ù‡â€ŒØ§ÛŒ ÛŒØ§ÙØª Ù†Ø´Ø¯';
    },
    searching: function () {
      return 'Ø¯Ø± Ø­Ø§Ù„ Ø¬Ø³ØªØ¬Ùˆ...';
    }
  }
    }, { define: e.define, require: e.require });
})();

