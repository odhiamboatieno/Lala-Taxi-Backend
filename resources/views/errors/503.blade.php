<!DOCTYPE html>
<html>
    <head>
        <title>{{trans('messages.erroes.be_right_back')}}</title>

        <link href="https://fonts.googleapis.com/css?family=Lato:100" rel="stylesheet" type="text/css">

        <style>
            html, body {
                height: 100%;
            }

            body {
                margin: 0;
                padding: 0;
                width: 100%;
                color: var(--color-as-b0bec5);
                display: table;
                font-weight: 100;
                font-family: 'Lato';
            }

            .container {
                text-align: center;
                display: table-cell;
                vertical-align: middle;
            }

            .content {
                text-align: center;
                display: inline-block;
            }

            .title {
                font-size: 72px;
                margin-bottom: 40px;
            }
        </style>
      <!-- lalatokens -->
  <link rel="stylesheet" href="{{ url('css/theme.css?v='.filemtime(public_path('css/theme.css'))) }}">
  <script src="{{ url('js/colors.js?v='.filemtime(public_path('js/colors.js'))) }}"></script>
  <!-- /lalatokens -->
</head>
    <body>
        <div class="container">
            <div class="content">
                <div class="title">{{trans('messages.errors.be_right_back')}}</div>
            </div>
        </div>
    </body>
</html>
