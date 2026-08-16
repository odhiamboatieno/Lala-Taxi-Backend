/*! Select2 4.0.0 | https://github.com/select2/select2/blob/master/LICENSE.md */

(function () {
    if (jQuery && jQuery.fn && jQuery.fn.select2 && jQuery.fn.select2.amd) {
        var e = jQuery.fn.select2.amd;
    }

    return e.define('select2/i18n/zh-CN', [], function () {
        return {
    errorLoading: function () {
      return 'æ— æ³•è½½å…¥ç»“æžœã€‚';
    },
    inputTooLong: function (args) {
      var overChars = args.input.length - args.maximum;

      var message = 'è¯·åˆ é™¤' + overChars + 'ä¸ªå­—ç¬¦';

      return message;
    },
    inputTooShort: function (args) {
      var remainingChars = args.minimum - args.input.length;

      var message = 'è¯·å†è¾“å…¥è‡³å°‘' + remainingChars + 'ä¸ªå­—ç¬¦';

      return message;
    },
    loadingMore: function () {
      return 'è½½å…¥æ›´å¤šç»“æžœâ€¦';
    },
    maximumSelected: function (args) {
      var message = 'æœ€å¤šåªèƒ½é€‰æ‹©' + args.maximum + 'ä¸ªé¡¹ç›®';

      return message;
    },
    noResults: function () {
      return 'æœªæ‰¾åˆ°ç»“æžœ';
    },
    searching: function () {
      return 'æœç´¢ä¸­â€¦';
    }
  }
    }, { define: e.define, require: e.require });
})();

