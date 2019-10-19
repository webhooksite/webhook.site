<!DOCTYPE html>
<html ng-app="app" ng-controller="AppController">
<head>
    <title>Webhook Tester</title>
    
    <script>
        AppConfig = {
            Broadcaster: "<?=config('broadcasting.default') == 'redis' ? 'socket.io' : 'pusher' ?>",
            EchoHostMode: "<?=config('broadcasting.echo_host_mode')?>",
            PusherToken: "<?=config('broadcasting.connections.pusher.key')?>",
            MaxRequests: <?=config('app.max_requests')?>,
        };
    </script>

    <link href="css/app.css" rel="stylesheet">
    <script src="js/socket.io.js"></script>
    <script src="js/libs.js"></script>
    <script src="js/bundle.js"></script>
    <script async defer src="https://buttons.github.io/buttons.js"></script>

    <meta name="description"
          content="Instantly test, bin and log webhooks and HTTP requests with this handy tool that shows requests to a unique URL in realtime.">
</head>
<body ng-cloak>
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
                <a class="navbar-brand" href="/" ui-sref="home()">&#x2693;&#xFE0F; Webhook Tester</a>
            </div>
            <div id="navbar" class="navbar-collapse collapse">
                <div class="nav navbar-left navbar-form">
                    <a href="https://github.com/fredsted/webhook.site" target="_blank"
                       style="margin-top: 7px"
                       class="btn btn-xs btn-link">
                        Github Page</a>
                    <a href="https://github.com/fredsted/webhook.site#donate" target="_blank"
                       style="margin-top: 7px"
                       class="btn btn-xs btn-link">
                        Donate</a>
                    <a href="https://twitter.com/fredsted" target="_blank"
                       style="margin-top: 7px"
                       class="btn btn-xs btn-link">
                        @fredsted</a>
                </div>
                <div class="nav navbar-right navbar-form hidden-sm">&nbsp;
                    <button type="button" class="btn btn-link openModal" data-modal="#editUrlModal"
                            ga-on="click" ga-event-category="Request" ga-event-action="click-newurl">
                    <span class="glyphicon glyphicon-edit"></span> Edit
                    </button> &nbsp;
                    <div class="form-group">
                        <input id="tokenUrl" type="text" class="form-control click-select"
                               style="width: 200px;"
                               value="{{ protocol }}//{{ domain }}/{{ token.uuid }}">
                    </div>
                    <button class="btn btn-success copyTokenUrl" data-clipboard-target="#tokenUrl"
                            ga-on="click" ga-event-category="URLCopy" ga-event-action="copy-nav">
                        <span class="glyphicon glyphicon-copy"></span> Copy
                    </button> &nbsp;
                    <button type="button" class="btn btn-primary openModal" data-modal="#newUrlModal"
                            ga-on="click" ga-event-category="Request" ga-event-action="click-editurl">
                        <span class="glyphicon glyphicon-plus"></span> New
                    </button>

                </div>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-3 col-md-2 sidebar" style="bottom: 40px">
                <p class="sidebar-header">Requests ({{ requests.total || 0 }})</p>
                <p ng-show="!hasRequests" class="small" style="padding-top: 20px">
                    <img src="assets/images/loader.gif"/>
                    &nbsp; Waiting for first request...
                </p>

                <div>
                    <ul class="nav nav-sidebar">
                        <li ng-show="hasRequests && currentPage > 1 && requests.total > requests.data.length && requests.current_page != 1">
                            <a ng-click="getPreviousPage(token.uuid)" class="prevent-default">Previous Page</a>
                        </li>
                        <li ng-repeat="(key, request) in requests.data"
                            ng-class="{'active': currentRequestIndex === request.uuid, 'unread': unread.indexOf(request.uuid) !== -1}">
                            <a ng-click="setCurrentRequest(request)" class="select">
                                <span class="label label-{{ getLabel(request.method) }}">{{ request.method }}</span>
                                #{{ request.uuid.substring(0,5) }} {{ request.ip }} <br/>
                                <small>{{ localDate(request.created_at) }}</small>
                            </a>
                            <a ng-click="deleteRequest(request, key)" class="btn btn-danger delete">
                                X
                            </a>
                        </li>
                        <li ng-show="hasRequests && !requests.is_last_page">
                            <a ng-click="getNextPage(token.uuid)" class="prevent-default">Next page</a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="col-sm-3 col-md-2 sidebar"
                 style="margin-top: 10px; height: 40px; bottom: 0px; top: auto; padding: 10px 0 0 0">
                <div class="text-center" ng-show="hasRequests">
                    <button
                            class="btn btn-xs btn-danger"
                            ng-click="deleteAllRequests(currentRequest)">Delete all requests
                    </button>
                </div>
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
                <div id="tutorial" ng-show="!hasRequests || !hideTutorial">
                    <button type="button" class="close" data-dismiss="tutorial" aria-label="Close"
                            ng-click="toggleTutorial()">
                        <span aria-hidden="true">&times;</span></button>
                    <p><strong>Webhook.site</strong>
                        allows you to easily test webhooks and other types of HTTP requests.
                        <a href="https://simonfredsted.com/1583" target="_blank">What is a webhook?</a></p>
                    <p>Any requests sent to that URL are logged here instantly
                        &mdash; you don't even have to refresh!</p>
                    <hr>
                    <p>Here's your unique URL that was created just now:</p>
                    <p>
                        <code>{{ protocol }}//{{ domain }}/{{ token.uuid }}</code>
                        <a class="btn btn-xs btn-link copyTokenUrl" data-clipboard-target="#tokenUrl"
                           ga-on="click" ga-event-category="URLCopy" ga-event-action="copy-welcome">
                            <span class="glyphicon glyphicon-copy"></span> Copy to clipboard</a>
                        <a class="btn btn-xs btn-link"
                           href="{{ protocol }}//{{ domain }}/{{ token.uuid }}"
                           target="_blank">
                            <span class="glyphicon glyphicon-new-window"></span> Open in new tab</a>
                    </p>
                    <hr>
                    <p>Bookmark this page to go back to the requests at any time.</p>
                    <p></p>Click <b>New URL</b> to create a new url with the ability to
                        customize status code, response body, etc.</p>
                    <p>
                        <a class="github-button" href="https://github.com/fredsted/webhook.site"
                           data-icon="octicon-star" data-show-count="true"
                           aria-label="Star fredsted/webhook.site on GitHub">Star on GitHub</a>
                        <a href="https://github.com/fredsted/webhook.site"
                    </p>
                </div>
                <div ng-show="hasRequests">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-md-3">
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
                                   ng-click="goToNextRequest()">
                                    Next &rightarrow;</a>
                                <a class="btn btn-xs btn-link"
                                   ng-class="requests.data.indexOf(currentRequest) !== requests.data.length-1 ? '' : 'disabled'"
                                   ng-click="setCurrentRequest(requests.data[requests.data.length-1])">
                                    Last</a>
                            </div>
                            <div class="col-md-9" style="padding-bottom: 10px">
                                <span class="pull-right">
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
                                    <a ng-click="redirect(currentRequest, redirectUrl, redirectMethod, redirectContentType, redirectHeaders)"
                                       class="btn btn-xs" ng-class="redirectUrl ? '' : 'disabled'"
                                       ga-on="click" ga-event-category="AutoRedirect" ga-event-action="redir-now">Redirect
                                        Now</a>&emsp;&emsp;

                                    <!-- CORS -->
                                    <label class="small" title="Enable automatic CORS headers">
                                        <input type="checkbox" ng-model="token.cors"
                                               ga-on="click" ga-event-category="CustomActions" ga-event-action="toggleEnable"
                                               ng-change="toggleCors(token)"
                                        />
                                        Enable CORS <sup class="muted">BETA</sup> &emsp;
                                    </label>

                                    <!-- Auto-JSON -->
                                    <label class="small"
                                           title="Automatically applies easy to read JSON and XML formatting on valid requests">
                                        <input type="checkbox" ng-model="formatJsonEnable"
                                               ga-on="click" ga-event-category="JSONFormat" ga-event-action="toggle"/> Format JSON/XML</label> &emsp;

                                    <!-- Auto Navigate -->
                                    <label class="small"
                                           title="Automatically select and go to the latest incoming webhook request">
                                        <input type="checkbox" ng-model="autoNavEnable"
                                               ga-on="click" ga-event-category="AutoNav" ga-event-action="toggle"/> Auto Navigate</label> &emsp;

                                    <label class="small"><input type="checkbox" ng-model="hideDetails">
                                        Hide Details</label>
                                </span>
                            </div>
                        </div>
                        <div class="row" id="requestDetails" ng-show="!hideDetails">
                            <div class="col-md-6">
                                <table class="table table-borderless table-striped">
                                    <thead>
                                    <tr>
                                        <th colspan="2">
                                            Request Details
                                            <span class="pull-right">
                                                <a class="btn btn-link btn-xxs"
                                                   href="{{ protocol }}//{{ domain }}/#/{{ token.uuid }}/{{ currentRequestIndex }}/{{ currentPage }}">
                                                    Permalink</a>
                                                <a class="btn btn-link btn-xxs" target="_blank"
                                                   href="{{ protocol }}//{{ domain }}/token/{{ token.uuid }}/request/{{ currentRequest.uuid }}/raw">
                                                    Raw content</a>
                                                <div class="btn-group btn-group-xxs form-inline dropdown">
                                                    <button type="button" class="btn btn-link"
                                                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                                                            ng-click="getBaseVariables(currentRequest)">
                                                        Copy As <span class="caret"></span>
                                                    </button>
                                                    <ul class="dropdown-menu scrollable-menu">
                                                        <li ng-repeat="type in convertTypes">
                                                            <a ng-click="copyRequestAs(currentRequest, type)">
                                                                {{ type }}</a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </span>
                                        </th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr>
                                        <td width="25%">URL</td>
                                        <td id="req-url">
                                            <span class="label label-{{ getLabel(currentRequest.method) }}">{{ currentRequest.method }}</span>
                                            <a href="{{ currentRequest.url }}">{{ currentRequest.url }}</a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Host</td>
                                        <td id="req-ip">
                                            {{ currentRequest.ip }}
                                            <a class="small" target="_blank"
                                               href="https://who.is/whois-ip/ip-address/{{ currentRequest.ip }}">whois</a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Date</td>
                                        <td id="req-date" title="{{ currentRequest.created_at }} UTC">{{ localDate(currentRequest.created_at) }} ({{ relativeDate(currentRequest.created_at) }})</td>
                                    </tr>
                                    <tr>
                                        <td>ID</td>
                                        <td id="req-date">{{ currentRequest.uuid }}</td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-borderless table-striped">
                                    <thead>
                                    <tr>
                                        <th colspan="2">Headers</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr ng-repeat="(headerName, values) in currentRequest.headers">
                                        <td width="25%">{{ headerName }}</td>
                                        <td><code ng-repeat="value in values">
                                                {{ value === '' ? '(empty)' : value }}{{ $last ? '' : ', ' }}</code></td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="row" id="requestData" ng-show="!hideDetails">
                            <div class="col-md-6">
                                <table class="table table-borderless table-striped">
                                    <thead>
                                    <tr>
                                        <th colspan="2">
                                            Query strings

                                        </th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr ng-show="!currentRequest.query">
                                        <td><span class="small">(empty)</span></td>
                                    </tr>
                                    <tr ng-repeat="(name, value) in currentRequest.query">
                                        <td width="25%">{{ name }}</td>
                                        <td><code>{{ value === '' ? '(empty)' : value }}</code></td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-borderless table-striped">
                                    <thead>
                                    <tr>
                                        <th colspan="2">
                                            Form values
                                        </th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr ng-show="!currentRequest.request">
                                        <td><span class="small">(empty)</span></td>
                                    </tr>
                                    <tr ng-repeat="(name, value) in currentRequest.request">
                                        <td width="25%">{{ name }}</td>
                                        <td><code>{{ value === '' ? '(empty)' : value }}</code></td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <p id="noContent" ng-show="hasRequests && currentRequest.content == ''">
                                    (no body content)</p>

                                <div id="req-content"
                                     ng-show="hasRequests && currentRequest.content != ''"
                                     hljs 
                                     hljs-source="formatJsonEnable ? formatContent(currentRequest.content) : currentRequest.content">
                                </div>
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
                                    <p>Headers to be passed along can be provided as a comma-separated list. Be sure
                                        to ensure these headers are allowed in any security settings (Cross-Domain)</p>
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
                                <label class="col-md-4 control-label" for="redirectHeaders">Redirect Headers</label>
                                <div class="col-md-7">
                                    <input id="redirectHeaders" ng-model="redirectHeaders"
                                           placeholder="e.g. x-token,referer"
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

    <div class="modal fade" tabindex="-1" role="dialog" id="editUrlModal">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Edit URL</h4>
                </div>
                <div class="modal-body">
                    <div id="rate-limit-warning"
                         class="alert alert-warning"
                         ng-show="token === null">
                        <p>This URL could not be found. It might have been automatically deleted.<br/>
                            Please create a new URL.</p>
                    </div>
                    <form class="form-horizontal" id="editTokenForm">
                        <fieldset>

                            <!-- Text input-->
                            <div class="form-group">
                                <label class="col-md-4 control-label" for="default_status">Default status code</label>
                                <div class="col-md-4">
                                    <input id="default_status" name="default_status" type="text" placeholder="200"
                                           class="form-control input-md" ng-model="token.default_status">

                                </div>
                            </div>

                            <!-- Text input-->
                            <div class="form-group">
                                <label class="col-md-4 control-label" for="default_content_type">Content Type</label>
                                <div class="col-md-4">
                                    <input id="default_content_type" name="default_content_type" type="text"
                                           placeholder="text/plain" class="form-control input-md"
                                           ng-model="token.default_content_type">
                                </div>
                            </div>

                            <!-- Text input-->
                            <div class="form-group">
                                <label class="col-md-4 control-label" for="timeout">Timeout before response</label>
                                <div class="col-md-4">
                                    <input id="timeout" name="timeout" type="number" max="10" min="0" placeholder="0"
                                           value="0" class="form-control input-md"
                                           ng-model="token.timeout">
                                </div>
                            </div>

                            <!-- Textarea -->
                            <div class="form-group">
                                <label class="col-md-4 control-label" for="default_content">Response body</label>
                                <div class="col-md-7">
                                    <textarea class="form-control" id="default_content" name="default_content"
                                              rows="5" ng-model="token.default_content"></textarea>
                                </div>
                            </div>

                        </fieldset>
                    </form>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-dismiss="modal"
                            ng-click="editToken(token.uuid)"
                            ga-on="click" ga-event-category="Request" ga-event-action="create">
                        Edit
                    </button>
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
                    <h4 class="modal-title">Create New URL</h4>
                </div>
                <div class="modal-body">
                    <div id="rate-limit-warning"
                         class="alert alert-warning"
                         ng-show="token === null">
                        <p>This URL could not be found. It might have been automatically deleted.<br/>
                            Please create a new URL.</p>
                    </div>
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
