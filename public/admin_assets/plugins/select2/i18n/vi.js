/*! Select2 4.0.0 | https://github.com/select2/select2/blob/master/LICENSE.md */

(function () {
    if (jQuery && jQuery.fn && jQuery.fn.select2 && jQuery.fn.select2.amd) {
        var e = jQuery.fn.select2.amd;
    }

    return e.define('select2/i18n/vi', [], function () {
        return {
    inputTooLong: function (args) {
      var overChars = args.input.length - args.maximum;

      var message = 'Vui lÃ²ng nháº­p Ã­t hÆ¡n ' + overChars + ' kÃ½ tá»±';

      if (overChars != 1) {
        message += 's';
      }

      return message;
    },
    inputTooShort: function (args) {
      var remainingChars = args.minimum - args.input.length;

      var message = 'Vui lÃ²ng nháº­p nhiá»u hÆ¡n ' + remainingChars + ' kÃ½ tá»±"';

      return message;
    },
    loadingMore: function () {
      return 'Äang láº¥y thÃªm káº¿t quáº£â€¦';
    },
    maximumSelected: function (args) {
      var message = 'Chá»‰ cÃ³ thá»ƒ chá»n Ä‘Æ°á»£c ' + args.maximum + ' lá»±a chá»n';

      return message;
    },
    noResults: function () {
      return 'KhÃ´ng tÃ¬m tháº¥y káº¿t quáº£';
    },
    searching: function () {
      return 'Äang tÃ¬mâ€¦';
    }
  }
    }, { define: e.define, require: e.require });
})();

