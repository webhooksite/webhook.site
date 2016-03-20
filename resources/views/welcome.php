<!DOCTYPE html>
<html>
<head>
    <title>Webhook Tester</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-2.2.2.min.js" async></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"
            integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS"
            crossorigin="anonymous" async></script>
    <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.4.5/angular.min.js"></script>
    <script src="assets/scripts/app.js"></script>


    <link href="assets/css/main.css" rel="stylesheet">
</head>
<body ng-app="app" ng-controller="AppController">

<script type="application/javascript">


</script>

<nav class="navbar navbar-inverse navbar-fixed-top">
    <div class="container-fluid">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="#">Webhook Tester</a>
        </div>
        <div id="navbar" class="navbar-collapse collapse">
            <div class="nav navbar-left navbar-form">
                <input type="text" class="form-control click-select"
                       style="width: auto;"
                       value="http://localhost/{{ token.uuid }}">

            </div>
        </div>
    </div>
</nav>

<div class="container-fluid">
    <div class="row">
        <div class="col-sm-3 col-md-2 sidebar">
            <p class="sidebar-header">Requests {{angulartest}}</p>
            <ul class="nav nav-sidebar">
                <li class="active">
                    <a href="#">
                       #3 POST 19:46:01 2.9kb
                    </a>
                </li>
                <li>
                    <a href="#">
                       #2 POST 19:45:40 0.9kb
                    </a>
                </li>
                <li>
                    <a href="#">
                       #1 POST 19:40:24 0.9kb
                    </a>
                </li>
            </ul>
        </div>
        <div id="request" class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
            <div class="table-responsive">
                <table class="table">
                    <tbody>
                    <tr>
                        <td><b>Date</b></td>
                        <td id="req-date">20 March 2016, 19:36:00</td>
                        <td><b>URL</b></td>
                        <td id="req-url">http://localhost:8000/9014f885-6ad5-4734-a1e7-ac9a164cff72</td>
                        <td><b>Method</b></td>
                        <td id="req-method">POST</td>
                        <td><b>IP/Host</b></td>
                        <td id="req-ip">127.0.0.1</td>
                    </tr>
                    <tr>
                        <td><b>Headers</b></td>
                        <td colspan="7" id="req-headers">
                            <b>Host</b>: localhost<br>
                            <b>Connection</b>: close<br>
                            <b>User-Agent</b>: Paw/2.3.2 (Macintosh; OS X/10.11.3) GCDHTTPRequest

                        </td>
                    </tr>
                    </tbody>
                </table>
<pre id="req-content">
{
  "lead_type_id": "2acb4738-dcd6-4e6e-aeb6-ed45ddeb9ab6",
  "team_id": 1,
  "lead_source": "Website",
  "b2c": true,
  "account": {
    "fixed_fields": [
      {
        "name": "name",
        "value": "Microsoft"
      }
    ]
  },
  "contact": {
    "fixed_fields": [
      {
        "name": "first_name",
        "value": "Bill"
      },
      {
        "name": "last_name",
        "value": "Gates"
      }
    ]
  }
}
</pre>

            </div>
        </div>
    </div>
</div>

<script src="assets/scripts/main.js"></script>



</body>
</html>
