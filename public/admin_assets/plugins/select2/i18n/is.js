/*! Select2 4.0.0 | https://github.com/select2/select2/blob/master/LICENSE.md */

(function () {
    if (jQuery && jQuery.fn && jQuery.fn.select2 && jQuery.fn.select2.amd) {
        var e = jQuery.fn.select2.amd;
    }

    return e.define('select2/i18n/is', [], function () {
        return {
    inputTooLong: function (args) {
      var overChars = args.input.length - args.maximum;

      var message = 'Vinsamlegast styttiÃ° texta um ' + overChars + ' staf';

      if (overChars <= 1) {
        return message;
      }

      return message + 'i';
    },
    inputTooShort: function (args) {
      var remainingChars = args.minimum - args.input.length;

      var message = 'Vinsamlegast skrifiÃ° ' + remainingChars + ' staf';

      if (remainingChars > 1) {
        message += 'i';
      }

      message += ' Ã­ viÃ°bÃ³t';

      return message;
    },
    loadingMore: function () {
      return 'SÃ¦ki fleiri niÃ°urstÃ¶Ã°urâ€¦';
    },
    maximumSelected: function (args) {
      return 'ÃžÃº getur aÃ°eins valiÃ° ' + args.maximum + ' atriÃ°i';
    },
    noResults: function () {
      return 'Ekkert fannst';
    },
    searching: function () {
      return 'Leitaâ€¦';
    }
  }
    }, { define: e.define, require: e.require });
})();

