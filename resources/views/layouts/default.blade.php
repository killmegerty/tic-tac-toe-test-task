<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link href="/css/app.css" rel="stylesheet">
        <title>Tic Tac Toe</title>
    </head>
    <body>

    <nav class="navbar navbar-default fixed-top">
      <div class="navbar-brand">Tic Tac Toe</div>
    </nav>

    <div class="container">
        @yield('content')
    </div>
    <script src="/js/app.js"></script>
</body>

</html>
