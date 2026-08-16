/*! Select2 4.0.0 | https://github.com/select2/select2/blob/master/LICENSE.md */

(function () {
    if (jQuery && jQuery.fn && jQuery.fn.select2 && jQuery.fn.select2.amd) {
        var e = jQuery.fn.select2.amd;
    }

    return e.define('select2/i18n/zh-TW', [], function () {
        return {
    inputTooLong: function (args) {
      var overChars = args.input.length - args.maximum;

      var message = 'è«‹åˆªæŽ‰' + overChars + 'å€‹å­—å…ƒ';

      return message;
    },
    inputTooShort: function (args) {
      var remainingChars = args.minimum - args.input.length;

      var message = 'è«‹å†è¼¸å…¥' + remainingChars + 'å€‹å­—å…ƒ';

      return message;
    },
    loadingMore: function () {
      return 'è¼‰å…¥ä¸­â€¦';
    },
    maximumSelected: function (args) {
      var message = 'ä½ åªèƒ½é¸æ“‡æœ€å¤š' + args.maximum + 'é …';

      return message;
    },
    noResults: function () {
      return 'æ²’æœ‰æ‰¾åˆ°ç›¸ç¬¦çš„é …ç›®';
    },
    searching: function () {
      return 'æœå°‹ä¸­â€¦';
    }
  }
    }, { define: e.define, require: e.require });
})();

