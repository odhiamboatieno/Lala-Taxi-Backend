/*! Select2 4.0.0 | https://github.com/select2/select2/blob/master/LICENSE.md */

(function () {
    if (jQuery && jQuery.fn && jQuery.fn.select2 && jQuery.fn.select2.amd) {
        var e = jQuery.fn.select2.amd;
    }

    return e.define('select2/i18n/th', [], function () {
        return {
    inputTooLong: function (args) {
      var overChars = args.input.length - args.maximum;

      var message = 'à¹‚à¸›à¸£à¸”à¸¥à¸šà¸­à¸­à¸ ' + overChars + ' à¸•à¸±à¸§à¸­à¸±à¸à¸©à¸£';

      return message;
    },
    inputTooShort: function (args) {
      var remainingChars = args.minimum - args.input.length;

      var message = 'à¹‚à¸›à¸£à¸”à¸žà¸´à¸¡à¸žà¹Œà¹€à¸žà¸´à¹ˆà¸¡à¸­à¸µà¸ ' + remainingChars + ' à¸•à¸±à¸§à¸­à¸±à¸à¸©à¸£';

      return message;
    },
    loadingMore: function () {
      return 'à¸à¸³à¸¥à¸±à¸‡à¸„à¹‰à¸™à¸‚à¹‰à¸­à¸¡à¸¹à¸¥à¹€à¸žà¸´à¹ˆà¸¡â€¦';
    },
    maximumSelected: function (args) {
      var message = 'à¸„à¸¸à¸“à¸ªà¸²à¸¡à¸²à¸£à¸–à¹€à¸¥à¸·à¸­à¸à¹„à¸”à¹‰à¹„à¸¡à¹ˆà¹€à¸à¸´à¸™ ' + args.maximum + ' à¸£à¸²à¸¢à¸à¸²à¸£';

      return message;
    },
    noResults: function () {
      return 'à¸¡à¹ˆà¸žà¸šà¸‚à¹‰à¸­à¸¡à¸¹à¸¥';
    },
    searching: function () {
      return 'à¸à¸³à¸¥à¸±à¸‡à¸„à¹‰à¸™à¸‚à¹‰à¸­à¸¡à¸¹à¸¥â€¦';
    }
  }
    }, { define: e.define, require: e.require });
})();

