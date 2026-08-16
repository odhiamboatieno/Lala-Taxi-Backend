/*! Select2 4.0.0 | https://github.com/select2/select2/blob/master/LICENSE.md */

(function () {
    if (jQuery && jQuery.fn && jQuery.fn.select2 && jQuery.fn.select2.amd) {
        var e = jQuery.fn.select2.amd;
    }

    return e.define('select2/i18n/gl', [], function () {
        return {
    inputTooLong: function (args) {
      var overChars = args.input.length - args.maximum;

      var message = 'Engada ';

      if (overChars === 1) {
        message += 'un carÃ¡cter';
      } else {
        message += overChars + ' caracteres';
      }

      return message;
    },
    inputTooShort: function (args) {
      var remainingChars = args.minimum - args.input.length;

      var message = 'Elimine ';

      if (remainingChars === 1) {
        message += 'un carÃ¡cter';
      } else {
        message += remainingChars + ' caracteres';
      }

      return message;
    },
    loadingMore: function () {
      return 'Cargando mÃ¡is resultadosâ€¦';
    },
    maximumSelected: function (args) {
      var message = 'SÃ³ pode ';

      if (args.maximum === 1) {
        message += 'un elemento';
      } else {
        message += args.maximum + ' elementos';
      }

      return message;
    },
    noResults: function () {
      return 'Non se atoparon resultados';
    },
    searching: function () {
      return 'Buscandoâ€¦';
    }
  }
    }, { define: e.define, require: e.require });
})();

