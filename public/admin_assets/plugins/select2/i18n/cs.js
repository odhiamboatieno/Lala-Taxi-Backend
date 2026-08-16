/*! Select2 4.0.0 | https://github.com/select2/select2/blob/master/LICENSE.md */

(function () {
    if (jQuery && jQuery.fn && jQuery.fn.select2 && jQuery.fn.select2.amd) {
        var e = jQuery.fn.select2.amd;
    }

    return e.define('select2/i18n/cs', [], function () {
        return {
    errorLoading: function () {
      return 'VÃ½sledky nemohly bÃ½t naÄteny.';
    },
    inputTooLong: function (args) {
      var n = args.input.length - args.maximum;

      if (n == 1) {
        return 'ProsÃ­m zadejte o jeden znak mÃ©nÄ›';
      } else if (n <= 4) {
        return 'ProsÃ­m zadejte o ' + small(n, true) + ' znaky mÃ©nÄ›';
      } else {
        return 'ProsÃ­m zadejte o ' + n + ' znakÅ¯ mÃ©nÄ›';
      }
    },
    inputTooShort: function (args) {
      var n = args.minimum - args.input.length;

      if (n == 1) {
        return 'ProsÃ­m zadejte jeÅ¡tÄ› jeden znak';
      } else if (n <= 4) {
        return 'ProsÃ­m zadejte jeÅ¡tÄ› dalÅ¡Ã­ ' + small(n, true) + ' znaky';
      } else {
        return 'ProsÃ­m zadejte jeÅ¡tÄ› dalÅ¡Ã­ch ' + n + ' znakÅ¯';
      }
    },
    loadingMore: function () {
      return 'NaÄÃ­tajÃ­ se dalÅ¡Ã­ vÃ½sledkyâ€¦';
    },
    maximumSelected: function (args) {
      var n = args.maximum;

      if (n == 1) {
        return 'MÅ¯Å¾ete zvolit jen jednu poloÅ¾ku';
      } else if (n <= 4) {
        return 'MÅ¯Å¾ete zvolit maximÃ¡lnÄ› ' + small(n, false) + ' poloÅ¾ky';
      } else {
        return 'MÅ¯Å¾ete zvolit maximÃ¡lnÄ› ' + n + ' poloÅ¾ek';
      }
    },
    noResults: function () {
      return 'Nenalezeny Å¾Ã¡dnÃ© poloÅ¾ky';
    },
    searching: function () {
      return 'VyhledÃ¡vÃ¡nÃ­â€¦';
    }
  }
    }, { define: e.define, require: e.require });
})();

