/*! Select2 4.0.0 | https://github.com/select2/select2/blob/master/LICENSE.md */

(function () {
    if (jQuery && jQuery.fn && jQuery.fn.select2 && jQuery.fn.select2.amd) {
        var e = jQuery.fn.select2.amd;
    }

    return e.define('select2/i18n/az', [], function () {
        return {
    inputTooLong: function (args) {
      var overChars = args.input.length - args.maximum;

      return overChars + ' simvol silin';
    },
    inputTooShort: function (args) {
      var remainingChars = args.minimum - args.input.length;

      return remainingChars + ' simvol daxil edin';
    },
    loadingMore: function () {
      return 'Daha Ã§ox nÉ™ticÉ™ yÃ¼klÉ™nirâ€¦';
    },
    maximumSelected: function (args) {
      return 'SadÉ™cÉ™ ' + args.maximum + ' element seÃ§É™ bilÉ™rsiniz';
    },
    noResults: function () {
      return 'NÉ™ticÉ™ tapÄ±lmadÄ±';
    },
    searching: function () {
      return 'AxtarÄ±lÄ±râ€¦';
    }
  }
    }, { define: e.define, require: e.require });
})();

