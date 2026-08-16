/*! Select2 4.0.0 | https://github.com/select2/select2/blob/master/LICENSE.md */

(function () {
    if (jQuery && jQuery.fn && jQuery.fn.select2 && jQuery.fn.select2.amd) {
        var e = jQuery.fn.select2.amd;
    }

    return e.define('select2/i18n/ca', [], function () {
        return {
    errorLoading: function () {
      return 'La cÃ rrega ha fallat';
    },
    inputTooLong: function (args) {
      var overChars = args.input.length - args.maximum;

      var message = 'Si us plau, elimina ' + overChars + ' car';

      if (overChars == 1) {
        message += 'Ã cter';
      } else {
        message += 'Ã cters';
      }

      return message;
    },
    inputTooShort: function (args) {
      var remainingChars = args.minimum - args.input.length;

      var message = 'Si us plau, introdueix ' + remainingChars + ' car';

      if (remainingChars == 1) {
        message += 'Ã cter';
      } else {
        message += 'Ã cters';
      }

      return message;
    },
    loadingMore: function () {
      return 'Carregant mÃ©s resultatsâ€¦';
    },
    maximumSelected: function (args) {
      var message = 'NomÃ©s es pot seleccionar ' + args.maximum + ' element';

      if (args.maximum != 1) {
        message += 's';
      }

      return message;
    },
    noResults: function () {
      return 'No s\'han trobat resultats';
    },
    searching: function () {
      return 'Cercantâ€¦';
    }
  }
    }, { define: e.define, require: e.require });
})();

