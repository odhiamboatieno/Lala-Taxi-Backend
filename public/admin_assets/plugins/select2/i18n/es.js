/*! Select2 4.0.0 | https://github.com/select2/select2/blob/master/LICENSE.md */

(function () {
    if (jQuery && jQuery.fn && jQuery.fn.select2 && jQuery.fn.select2.amd) {
        var e = jQuery.fn.select2.amd;
    }

    return e.define('select2/i18n/es', [], function () {
        return {
    errorLoading: function () {
      return 'La carga fallÃ³';
    },
    inputTooLong: function (args) {
      var remainingChars = args.input.length - args.maximum;

      var message = 'Por favor, elimine ' + remainingChars + ' car';

      if (remainingChars == 1) {
        message += 'Ã¡cter';
      } else {
        message += 'acteres';
      }

      return message;
    },
    inputTooShort: function (args) {
      var remainingChars = args.minimum - args.input.length;

      var message = 'Por favor, introduzca ' + remainingChars + ' car';

      if (remainingChars == 1) {
        message += 'Ã¡cter';
      } else {
        message += 'acteres';
      }

      return message;
    },
    loadingMore: function () {
      return 'Cargando mÃ¡s resultadosâ€¦';
    },
    maximumSelected: function (args) {
      var message = 'SÃ³lo puede seleccionar ' + args.maximum + ' elemento';

      if (args.maximum != 1) {
        message += 's';
      }

      return message;
    },
    noResults: function () {
      return 'No se encontraron resultados';
    },
    searching: function () {
      return 'Buscandoâ€¦';
    }
  }
    }, { define: e.define, require: e.require });
})();

