<!DOCTYPE html>
<html>
<head>
    <title>Error: {{ $exception->getMessage() }}</title>
    <link href="/assets/css/libs/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">
    <link href="/css/app.css" rel="stylesheet">
</head>
<body>
<div class="container">
    <div class="jumbotron">
        <h1>Error</h1>
        <p class="lead">{{ $exception->getMessage() }}</p>
        <p><a class="btn btn-lg btn-success" href="/" role="button">Back to webhook.site &rarr;</a></p>
    </div>
</div>
</body>
</html>
