<!DOCTYPE html>
<html>
<head>
    <title>Webhook Tester</title>
    <!-- Libraries -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-2.2.2.min.js" async></script>
    <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.4.5/angular.min.js"></script>


    <!-- App -->
    <script src="assets/scripts/app.js"></script>
    <link href="assets/css/main.css" rel="stylesheet">
</head>
<body ng-app="app" ng-controller="AppController">

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
                <div class="form-group">
                    <label for="tokenUrl" style="color: #999">
                        Send webhooks to: &nbsp;
                    </label>

                    <input id="tokenUrl" type="text" class="form-control click-select"
                           style="width: 400px;"
                           value="{{ domain }}{{ token.uuid }}">
                </div>
                <button class="btn btn-default" id="copyTokenUrl" data-clipboard-target="#tokenUrl">Copy</button>
            </div>
        </div>
    </div>
</nav>

<div class="container-fluid">
    <div class="row">
        <div class="col-sm-3 col-md-2 sidebar">
            <p class="sidebar-header">Requests</p>
            <ul class="nav nav-sidebar">
                <li ng-repeat="(key, value) in requests.data"
                    ng-class="currentRequestIndex === key ? 'active' : ''"
                    ng-click="setCurrentRequest(key)">
                    <a href="#">
                       #{{ key }} {{ value.method }} {{ value.created_at }} {{ value.ip }}
                    </a>
                </li>
            </ul>
        </div>
        <div id="request" class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
            <div class="table-responsive" ng-show="hasRequests">
                <table class="table">
                    <tbody>
                    <tr>
                        <td><b>Date</b></td>
                        <td id="req-date">{{ currentRequest.created_at }}</td>
                        <td><b>URL</b></td>
                        <td id="req-url">{{ currentRequest.url }}</td>
                        <td><b>Method</b></td>
                        <td id="req-method">{{ currentRequest.method }}</td>
                        <td><b>IP/Host</b></td>
                        <td id="req-ip">{{ currentRequest.hostname }} ({{ currentRequest.ip }})</td>
                    </tr>
                    <tr>
                        <td><b>Headers</b></td>
                        <td colspan="7" id="req-headers">
                            <p ng-repeat="(headerName, values) in currentRequest.headers">
                                <strong>{{ headerName }}:</strong>
                                <span ng-repeat="value in values">{{value}}{{$last ? '' : ', '}}</span>
                            </p>

                        </td>
                    </tr>
                    </tbody>
                </table>
<pre id="req-content">
{{ (currentRequest.content == '' ? '(no content)' : currentRequest.content) }}
</pre>

            </div>
        </div>
    </div>
</div>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"
        integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS"
        crossorigin="anonymous" async defer></script>
<script src="https://cdn.jsdelivr.net/clipboard.js/1.5.13/clipboard.min.js"></script>

</body>
</html>
