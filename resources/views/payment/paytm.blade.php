<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ __('messages.blade_static.merchant_check_out_page_c9032b7c') }}</title>
  <!-- lalatokens -->
  <link rel="stylesheet" href="{{ url('css/theme.css') }}">
  <script src="{{ url('js/colors.js') }}"></script>
  <!-- /lalatokens -->
</head>
<body>
<center><h1>{{ __('messages.blade_static.please_do_not_refresh_this_page_412b4eb8') }}</h1></center>
<form method="post" action="{{ $PAYTM_TXN_URL }}" name="f1">
    {{ csrf_field()  }}
    <table border="1">
        <tbody>
		<?php
		foreach($paramList as $name => $value) {
			echo '<input type="hidden" name="' . $name .'" value="' . $value . '">';
		}
		?>
        <input type="hidden" name="CHECKSUMHASH" value="<?php echo $checkSum ?>">
        </tbody>
    </table>
    <script type="text/javascript">
		{{ __('messages.blade_static.document_f1_submit_da1a0793') }}
    </script>
</form>
</body>
</html>