/*! Select2 4.0.0 | https://github.com/select2/select2/blob/master/LICENSE.md */

(function () {
    if (jQuery && jQuery.fn && jQuery.fn.select2 && jQuery.fn.select2.amd) {
        var e = jQuery.fn.select2.amd;
    }

    return e.define('select2/i18n/ko', [], function () {
        return {
    errorLoading: function () {
      return 'ê²°ê³¼ë¥¼ ë¶ˆëŸ¬ì˜¬ ìˆ˜ ì—†ìŠµë‹ˆë‹¤.';
    },
    inputTooLong: function (args) {
      var overChars = args.input.length - args.maximum;

      var message = 'ë„ˆë¬´ ê¹ë‹ˆë‹¤. ' + overChars + ' ê¸€ìž ì§€ì›Œì£¼ì„¸ìš”.';

      return message;
    },
    inputTooShort: function (args) {
      var remainingChars = args.minimum - args.input.length;

      var message = 'ë„ˆë¬´ ì§§ìŠµë‹ˆë‹¤. ' + remainingChars + ' ê¸€ìž ë” ìž…ë ¥í•´ì£¼ì„¸ìš”.';

      return message;
    },
    loadingMore: function () {
      return 'ë¶ˆëŸ¬ì˜¤ëŠ” ì¤‘â€¦';
    },
    maximumSelected: function (args) {
      var message = 'ìµœëŒ€ ' + args.maximum + 'ê°œê¹Œì§€ë§Œ ì„ íƒ ê°€ëŠ¥í•©ë‹ˆë‹¤.';

      return message;
    },
    noResults: function () {
      return 'ê²°ê³¼ê°€ ì—†ìŠµë‹ˆë‹¤.';
    },
    searching: function () {
      return 'ê²€ìƒ‰ ì¤‘â€¦';
    }
  }
    }, { define: e.define, require: e.require });
})();

