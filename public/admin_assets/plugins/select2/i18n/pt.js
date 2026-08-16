/*! Select2 4.0.0 | https://github.com/select2/select2/blob/master/LICENSE.md */

(function () {
    if (jQuery && jQuery.fn && jQuery.fn.select2 && jQuery.fn.select2.amd) {
        var e = jQuery.fn.select2.amd;
    }

    return e.define('select2/i18n/pt', [], function () {
        return {
    errorLoading: function () {
      return 'Os resultados nÃ£o puderam ser carregados.';
    },
    inputTooLong: function (args) {
      var overChars = args.input.length - args.maximum;

      var message = 'Por favor apague ' + overChars + ' ';

      message += overChars != 1 ? 'caracteres' : 'carÃ¡cter';

      return message;
    },
    inputTooShort: function (args) {
      var remainingChars = args.minimum - args.input.length;

      var message = 'Introduza ' + remainingChars + ' ou mais caracteres';

      return message;
    },
    loadingMore: function () {
      return 'A carregar mais resultadosâ€¦';
    },
    maximumSelected: function (args) {
      var message = 'Apenas pode seleccionar ' + args.maximum + ' ';

      message += args.maximum != 1 ? 'itens' : 'item';

      return message;
    },
    noResults: function () {
      return 'Sem resultados';
    },
    searching: function () {
      return 'A procurarâ€¦';
    }
  }
    }, { define: e.define, require: e.require });
})();

