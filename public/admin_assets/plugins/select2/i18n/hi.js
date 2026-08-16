/*! Select2 4.0.0 | https://github.com/select2/select2/blob/master/LICENSE.md */

(function () {
    if (jQuery && jQuery.fn && jQuery.fn.select2 && jQuery.fn.select2.amd) {
        var e = jQuery.fn.select2.amd;
    }

    return e.define('select2/i18n/hi', [], function () {
        return {
    errorLoading: function () {
      return 'à¤ªà¤°à¤¿à¤£à¤¾à¤®à¥‹à¤‚ à¤•à¥‹ à¤²à¥‹à¤¡ à¤¨à¤¹à¥€à¤‚ à¤•à¤¿à¤¯à¤¾ à¤œà¤¾ à¤¸à¤•à¤¾à¥¤';
    },
    inputTooLong: function (args) {
      var overChars = args.input.length - args.maximum;

      var message =  overChars + ' à¤…à¤•à¥à¤·à¤° à¤•à¥‹ à¤¹à¤Ÿà¤¾ à¤¦à¥‡à¤‚';

      if (overChars > 1) {
        message = overChars + ' à¤…à¤•à¥à¤·à¤°à¥‹à¤‚ à¤•à¥‹ à¤¹à¤Ÿà¤¾ à¤¦à¥‡à¤‚ ';
      }

      return message;
    },
    inputTooShort: function (args) {
      var remainingChars = args.minimum - args.input.length;

      var message = 'à¤•à¥ƒà¤ªà¤¯à¤¾ ' + remainingChars + ' à¤¯à¤¾ à¤…à¤§à¤¿à¤• à¤…à¤•à¥à¤·à¤° à¤¦à¤°à¥à¤œ à¤•à¤°à¥‡à¤‚';

      return message;
    },
    loadingMore: function () {
      return 'à¤…à¤§à¤¿à¤• à¤ªà¤°à¤¿à¤£à¤¾à¤® à¤²à¥‹à¤¡ à¤¹à¥‹ à¤°à¤¹à¥‡ à¤¹à¥ˆ...';
    },
    maximumSelected: function (args) {
      var message = 'à¤†à¤ª à¤•à¥‡à¤µà¤² ' + args.maximum + ' à¤†à¤‡à¤Ÿà¤® à¤•à¤¾ à¤šà¤¯à¤¨ à¤•à¤° à¤¸à¤•à¤¤à¥‡ à¤¹à¥ˆà¤‚';
      return message;
    },
    noResults: function () {
      return 'à¤•à¥‹à¤ˆ à¤ªà¤°à¤¿à¤£à¤¾à¤® à¤¨à¤¹à¥€à¤‚ à¤®à¤¿à¤²à¤¾';
    },
    searching: function () {
      return 'à¤–à¥‹à¤œ à¤°à¤¹à¤¾ à¤¹à¥ˆ...';
    }
  }
    }, { define: e.define, require: e.require });
})();

