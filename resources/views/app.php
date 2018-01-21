<!DOCTYPE html>
<html ng-app="app" ng-controller="AppController">
<head>
    <title>Webhook Tester</title>

    <!-- Libraries -->
    <link href="assets/css/libs/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">
    <script src="assets/scripts/libs/jquery-2.2.2.min.js"></script>
    <script src="assets/scripts/libs/angular.min.js"></script>
    <script src="assets/scripts/libs/angular-ui-router.js"></script>
    <script src="assets/scripts/libs/bootstrap.min.js"
            integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS"
            crossorigin="anonymous" async defer></script>
    <script src="assets/scripts/libs/clipboard.min.js"></script>
    <script src="assets/scripts/libs/bootstrap-notify.min.js"></script>

    <!-- App -->
    <script src="assets/scripts/app.js"></script>
    <link href="css/app.css" rel="stylesheet">
    <script>
        AppConfig = {
            PusherToken: "<?=config('broadcasting.connections.pusher.key')?>",
            MaxRequests: <?=config('app.max_requests')?>
        };
    </script>

    <!-- Pusher -->
    <script src="https://js.pusher.com/3.2/pusher.min.js"></script>

    <meta name="description"
          content="Easily test webhooks and HTTP requests with this handy tool that displays requests in realtime.">
</head>
<body>
<div class="mainView" ui-view>
    <nav class="navbar navbar-inverse navbar-fixed-top">
        <div class="container-fluid">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar"
                        aria-expanded="false" aria-controls="navbar">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="/" ui-sref="home()">Webhook Tester</a>
            </div>
            <div id="navbar" class="navbar-collapse collapse">
                <div class="nav navbar-left navbar-form">
                    <a href="https://github.com/fredsted/webhook.site" target="_blank"
                       style="margin-top: 7px"
                       class="btn btn-xs btn-link">
                        Github Page</a>
                    <a href="https://twitter.com/fredsted" target="_blank"
                       style="margin-top: 7px"
                       class="btn btn-xs btn-link">
                        @fredsted</a>
                    <button style="margin-top: 7px"
                            class="openModal btn btn-xs btn-link"
                            data-modal="#helpModal">
                        Help
                    </button>
                </div>
                <div class="nav navbar-right navbar-form">&nbsp;
                    <div class="form-group">
                        <input id="tokenUrl" type="text" class="form-control click-select"
                               style="width: 200px;"
                               value="{{ protocol }}//{{ domain }}/{{ token.uuid }}">
                    </div>
                    <button class="btn btn-success copyTokenUrl" data-clipboard-target="#tokenUrl"
                            ga-on="click" ga-event-category="URLCopy" ga-event-action="copy-nav">
                        <span class="glyphicon glyphicon-copy"></span> Copy
                    </button> &nbsp;
                    <button type="button" class="btn btn-primary openModal" data-modal="#newUrlModal">
                        <span class="glyphicon glyphicon-plus-sign"
                              ga-on="click" ga-event-category="Request" ga-event-action="click-newurl"></span> New URL
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-3 col-md-2 sidebar">
                <p class="sidebar-header">Requests ({{ requests.total || 0 }})</p>

                <p ng-show="!hasRequests" class="small">
                    <img src="assets/images/loader.gif"/>
                    &nbsp; Waiting for first request...
                </p>

                <ul class="nav nav-sidebar">
                    <li ng-repeat="(key, request) in requests.data"
                        ng-class="currentRequestIndex === request.uuid ? 'active' : ''">
                        <a ng-click="setCurrentRequest(request)" class="select">
                            <span class="label label-{{ getLabel(request.method) }}">{{ request.method }}</span>
                            #{{ request.uuid.substring(0,5) }} {{ request.ip }} <br/>
                            <small>{{ request.created_at }}</small>
                        </a>
                        <a ng-click="deleteRequest(request, key)" class="btn btn-danger delete">
                            X
                        </a>
                    </li>
                    <li ng-show="requests.next_page_url">
                        <a ng-click="getNextPage(token.uuid)" class="prevent-default">Load more</a>
                    </li>
                </ul>
            </div>
            <div id="request" class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
                <div id="rate-limit-warning"
                     class="alert alert-warning"
                     ng-show="hasRequests && requests.total >= appConfig.MaxRequests">
                    <p><strong>This URL received over {{ appConfig.MaxRequests }} requests and can't accept more
                            webhooks.</strong></p>
                    <p>New requests sent to this URL will return HTTP status code 410 Gone and
                        won't be logged. Please create a new URL to continue.</p>
                </div>
                <div ng-show="!hasRequests">
                    <p><strong>Webhook Tester</strong>
                        allows you to easily test webhooks and other types of HTTP requests.</p>
                    <p>Any requests sent to that URL are logged here instantly
                        &mdash; you don't even have to refresh!</p>
                    <hr>
                    <p>Here's your unique URL that was created just now:</p>
                    <p>
                        <code>{{ protocol }}//{{ domain }}/{{ token.uuid }}</code>
                        <a class="btn btn-xs btn-link copyTokenUrl" data-clipboard-target="#tokenUrl"
                           ga-on="click" ga-event-category="URLCopy" ga-event-action="copy-welcome">Copy</a>
                        <a class="btn btn-xs btn-link"
                           href="{{ protocol }}//{{ domain }}/{{ token.uuid }}"
                           target="_blank">
                            <span class="glyphicon glyphicon-new-window"></span> Open in new tab</a>
                    </p>
                    <hr>
                    <p>Bookmark this page to go back to the requests at any time.
                        For more info, click <b>Help</b>.</p>
                    <p><a href="https://github.com/fredsted/webhook.site"
                          ga-on="click" ga-event-category="Nav" ga-event-action="click-github">Fork this on GitHub</a>
                    </p>
                </div>
                <div ng-show="hasRequests">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-md-4">
                                <a class="btn btn-xs btn-link"
                                   ng-click="setCurrentRequest(requests.data[0])"
                                   ng-class="requests.data.indexOf(currentRequest) !== 0 ? '' : 'disabled'">
                                    First</a>
                                <a class="btn btn-xs btn-link"
                                   ng-class="requests.data.indexOf(currentRequest) <= requests.data.length && requests.data.indexOf(currentRequest) !== 0 ? '' : 'disabled'"
                                   ng-click="setCurrentRequest(requests.data[requests.data.indexOf(currentRequest) - 1])">
                                    &leftarrow; Previous</a>
                                <a class="btn btn-xs btn-link"
                                   ng-class="requests.data.indexOf(currentRequest) !== requests.data.length-1 ? '' : 'disabled'"
                                   ng-click="setCurrentRequest(requests.data[requests.data.indexOf(currentRequest) + 1])">
                                    Next &rightarrow;</a>
                                <a class="btn btn-xs btn-link"
                                   ng-class="requests.data.indexOf(currentRequest) !== requests.data.length-1 ? '' : 'disabled'"
                                   ng-click="setCurrentRequest(requests.data[requests.data.length-1])">
                                    Last</a>
                            </div>
                            <div class="col-md-8" style="padding-bottom: 10px">
                                <!-- Redirection -->
                                <label class="small" title="Redirect incoming requests to another URL via XHR"
                                       ng-disabled="!redirectUrl">
                                    <input type="checkbox" ng-model="redirectEnable"
                                           ng-disabled="!redirectUrl"
                                           ga-on="click" ga-event-category="AutoRedirect" ga-event-action="toggle"/>
                                    Auto redirect
                                </label>
                                <a href class="openModal btn btn-xs" data-modal="#redirectModal"
                                   ga-on="click" ga-event-category="AutoRedirect"
                                   ga-event-action="settings">Settings...</a>
                                <a ng-click="redirect(currentRequest, redirectUrl, redirectMethod, redirectContentType)"
                                   class="btn btn-xs" ng-class="redirectUrl ? '' : 'disabled'"
                                   ga-on="click" ga-event-category="AutoRedirect" ga-event-action="redir-now">Redirect
                                    Now</a>&emsp;&emsp;

                                <!-- Auto-JSON -->
                                <label class="small"
                                       title="Automatically applies easy to read JSON formatting on valid requests">
                                    <input type="checkbox" ng-model="formatJsonEnable"
                                           ga-on="click" ga-event-category="JSONFormat" ga-event-action="toggle"/> Format JSON</label> &emsp;

                                <!-- Auto Navigate -->
                                <label class="small"
                                       title="Automatically select and go to the latest incoming webhook request">
                                    <input type="checkbox" ng-model="autoNavEnable"
                                           ga-on="click" ga-event-category="AutoNav" ga-event-action="toggle"/> Auto navigate</label>

                                <label class="small" style="float: right"><input type="checkbox" ng-model="hideDetails">
                                    Hide Details</label>
                            </div>
                        </div>
                        <div class="row" id="requestDetails" ng-show="!hideDetails">
                            <div class="col-md-4">
                                <table class="table table-borderless table-striped">
                                    <tbody>
                                    <tr>
                                        <th colspan="2">
                                            Request Details
                                            <a class="pull-right small"
                                               href="{{ protocol }}//{{ domain }}/#/{{ token.uuid }}/{{ currentRequestIndex }}/{{ currentPage }}">
                                                permalink</a>
                                        </th>
                                    </tr>
                                    <tr>
                                        <td width="25%">URL</td>
                                        <td id="req-url">
                                            <span class="label label-{{ getLabel(currentRequest.method) }}">{{ currentRequest.method }}</span>
                                            <a href="{{ currentRequest.url }}">{{ currentRequest.url }}</a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Host</td>
                                        <td id="req-ip">{{ currentRequest.ip }}</td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="col-md-8">
                                <table class="table table-borderless table-striped">
                                    <tbody>
                                    <tr>
                                        <th colspan="2">Headers</th>
                                    </tr>
                                    <tr ng-repeat="(headerName, values) in currentRequest.headers">
                                        <td width="25%">{{ headerName }}</td>
                                        <td><code ng-repeat="value in values">
                                                {{ value == '' ? '(empty)' : value }}{{ $last ? '' : ', ' }}</code></td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <p id="noContent" ng-show="hasRequests && currentRequest.content == ''">
                                    (no body content)</p>

                                <pre id="req-content"
                                     ng-show="hasRequests && currentRequest.content != ''"
                                     ng-bind="formatJsonEnable ? formatContentJson(currentRequest.content) : currentRequest.content"></pre>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" tabindex="-1" role="dialog" id="redirectModal">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Redirection Settings</h4>
                </div>
                <div class="modal-body">
                    <form class="form-horizontal" id="redirectForm">
                        <fieldset>
                            <div class="form-group">
                                <div class="container-fluid">
                                    <p>Redirection allows you to automatically, or with a click, send incoming
                                        requests to another URL via XHR. The content will be redirected, and you can
                                        choose
                                        a static method to use.</p>
                                    <p>Since XHR is used, there might be issues with Cross-Domain Requests.</p>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-md-4 control-label" for="redirectUrl">Redirect to</label>
                                <div class="col-md-7">
                                    <input id="redirectUrl" ng-model="redirectUrl"
                                           placeholder="http://localhost"
                                           class="form-control input-md">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-md-4 control-label" for="redirectContentType">Content Type</label>
                                <div class="col-md-7">
                                    <input id="redirectContentType" ng-model="redirectContentType"
                                           class="form-control input-md">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-md-4 control-label" for="redirectUrl">HTTP Method</label>
                                <div class="col-md-5">
                                    <select class="form-control input-md" ng-model="redirectMethod">
                                        <option value="">Default (use request method)</option>
                                        <option value="GET">GET</option>
                                        <option value="POST">POST</option>
                                        <option value="PUT">PUT</option>
                                        <option value="DELETE">DELETE</option>
                                        <option value="PATCH">PATCH</option>
                                    </select>
                                </div>
                            </div>
                        </fieldset>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" ng-click="saveSettings()" data-dismiss="modal">Close
                    </button>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->

    <div class="modal fade" tabindex="-1" role="dialog" id="helpModal">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">What is Webhook Tester?</h4>
                </div>
                <div class="modal-body">
                    <p><strong>Webhook Tester</strong>
                        allows you to easily test webhooks and other types of HTTP requests.</p>
                    <p>Here's your unique URL:</p>
                    <p>
                        <code>{{ protocol }}//{{ domain }}/{{ token.uuid }}</code>
                        <a href="{{ protocol }}//{{ domain }}/{{ token.uuid }}" target="_blank">(try it!)</a>
                    </p>
                    <p>Any requests sent to that URL are instantly logged here - you don't even have to refresh.</p>
                    <p>
                        Append a status code to the url, e.g.: <br/>
                        <code>{{ protocol }}//{{ domain }}/{{ token.uuid }}/404</code>, <br/>
                        so the URL will respond with a 404 Not Found.</p>
                    <p>You can bookmark this page to go back to the request contents at any time.</p>
                    <p><a href="https://github.com/fredsted/webhook.site">Fork this on GitHub</a></p>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->

    <div class="modal fade" tabindex="-1" role="dialog" id="newUrlModal">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Configure URL</h4>
                </div>
                <div class="modal-body">
                    <p>You have the ability to customize how your URL will respond by changing the
                        status code, content-type header and the content.</p>
                    <hr>
                    <form class="form-horizontal" id="createTokenForm">
                        <fieldset>

                            <!-- Text input-->
                            <div class="form-group">
                                <label class="col-md-4 control-label" for="default_status">Default status code</label>
                                <div class="col-md-4">
                                    <input id="default_status" name="default_status" type="text" placeholder="200"
                                           class="form-control input-md">

                                </div>
                            </div>

                            <!-- Text input-->
                            <div class="form-group">
                                <label class="col-md-4 control-label" for="default_content_type">Content Type</label>
                                <div class="col-md-4">
                                    <input id="default_content_type" name="default_content_type" type="text"
                                           placeholder="text/plain" class="form-control input-md">
                                </div>
                            </div>

                            <!-- Text input-->
                            <div class="form-group">
                                <label class="col-md-4 control-label" for="timeout">Timeout before response</label>
                                <div class="col-md-4">
                                    <input id="timeout" name="timeout" type="number" max="10" min="0" placeholder="0"
                                           value="0" class="form-control input-md">
                                </div>
                            </div>

                            <!-- Textarea -->
                            <div class="form-group">
                                <label class="col-md-4 control-label" for="default_content">Response body</label>
                                <div class="col-md-7">
                                    <textarea class="form-control" id="default_content" name="default_content"
                                              rows="5"></textarea>
                                </div>
                            </div>

                        </fieldset>
                    </form>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" data-dismiss="modal" ng-click="getCustomToken()"
                            ga-on="click" ga-event-category="Request" ga-event-action="create">
                        Create
                    </button>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->

    <script>
        window.ga=window.ga||function(){(ga.q=ga.q||[]).push(arguments)};ga.l=+new Date;
        ga('create', 'UA-5230636-9', 'auto');
        ga('require', 'eventTracker');
        ga('require', 'outboundLinkTracker');
        ga('require', 'urlChangeTracker');
        ga('require', 'pageVisibilityTracker');
        ga('send', 'pageview');
    </script>
    <script async src="https://www.google-analytics.com/analytics.js"></script>
    <script async src="/assets/scripts/libs/autotrack.js"></script>
</div>
</body>
</html>
