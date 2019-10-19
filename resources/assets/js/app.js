let prettyData = require('pretty-data').pd;
let moment = require('moment');

angular
    .module("app", [
        'ui.router',
        'hljs'
    ])
    .config(['$stateProvider', '$urlRouterProvider', '$urlMatcherFactoryProvider',
        function ($stateProvider, $urlRouterProvider, $urlMatcherFactoryProvider) {
            var GUID_REGEXP = /^[a-f\d]{8}-([a-f\d]{4}-){3}[a-f\d]{12}$/i;
            $urlMatcherFactoryProvider.type('guid', {
                encode: angular.identity,
                decode: angular.identity,
                is: function (item) {
                    return GUID_REGEXP.test(item);
                }
            });

            // States
            $urlRouterProvider.otherwise('/');

            $stateProvider
                .state('home', {
                    url: "/",
                    controller: 'AppController'
                })
                .state('request', {
                    url: "/{id:guid}/{offset:guid}/{page:int}",
                    controller: 'AppController'
                })
                .state('token', {
                    url: "/{id:guid}",
                    controller: 'AppController'
                })
                ;
        }
    ])
    .controller("AppController", ['$scope', '$http', '$stateParams', '$state', '$timeout', function ($scope, $http, $stateParams, $state, $timeout) {
        /**
         * Settings handling
         */

        // Array of scope variables to automatically save
        var settings = [
            'redirectEnable',
            'redirectUrl',
            'redirectContentType',
            'redirectHeaders',
            'redirectMethod',
            'token',
            'formatJsonEnable',
            'autoNavEnable',
            'hideTutorial',
        ];

        $scope.saveSettings = (function () {
            for (var setting in settings) {
                window.localStorage.setItem(
                    settings[setting],
                    JSON.stringify($scope[settings[setting]])
                );
            }
        });

        $scope.getSetting = (function (name, defaultValue) {
            var value = window.localStorage.getItem(name);

            if (!value || typeof (value) === 'undefined' || value === 'undefined') {
                if (typeof (defaultValue) === 'undefined') {
                    return null;
                }
                return defaultValue;
            }

            return JSON.parse(value);
        });

        /**
         * App Initialization
         */

        $scope.token = $scope.getSetting('token');
        $scope.requests = {
            total: 0,
            data: []
        };
        $scope.currentRequestIndex = 0;
        $scope.currentRequest = {};
        $scope.currentPage = 1;
        $scope.hasRequests = false;
        $scope.protocol = window.location.protocol;
        $scope.domain = window.location.host;
        $scope.appConfig = window.AppConfig;

        // Load settings
        $scope.formatJsonEnable = $scope.getSetting('formatJsonEnable', false);
        $scope.autoNavEnable = $scope.getSetting('autoNavEnable', false);
        $scope.redirectEnable = $scope.getSetting('redirectEnable', false);
        $scope.redirectMethod = $scope.getSetting('redirectMethod', '');
        $scope.redirectUrl = $scope.getSetting('redirectUrl', null);
        $scope.redirectContentType = $scope.getSetting('redirectContentType', 'text/plain');
        $scope.redirectHeaders = $scope.getSetting('redirectHeaders', null);
        $scope.unread = $scope.getSetting('unread', []);
        $scope.hideTutorial = $scope.getSetting('hideTutorial', false);

        // Initialize Clipboard copy button
        new Clipboard('.copyTokenUrl');

        // Initialize notify.js
        $.notifyDefaults({
            placement: {
                from: "bottom"
            },
            animate: {
                enter: "animated fadeInUp",
                exit: "animated fadeOutDown"
            },
            delay: 1000
        });

        // Hack to open modals inside that are nested inside divs
        // Since the modals need to be placed inside the ui-view div
        $('.openModal').click(function (e) {
            $($(this).data('modal')).modal();
            $('.modal-backdrop').appendTo('.mainView');
            $('body').removeClass();
        });

        // Automatically save settings
        $scope.$watchGroup(settings, function (newVal, oldVal) {
            if (newVal === oldVal) {
                return;
            }

            $scope.saveSettings();
        });

        /**
         * Tutorial
         */

        $scope.toggleTutorial = (function () {
            if ($scope.hideTutorial === true) {
                $scope.hideTutorial = false;
            } else {
                $scope.hideTutorial = true;
            }
        });

        /**
         * Unread Count
         */

        // Automatically update unread count in title tag
        $scope.$watchCollection('unread', function (newVal, oldVal) {
            if (newVal === oldVal) {
                return;
            }

            $scope.updateUnreadCount();
        });

        $scope.resetUnread = (function () {
            $scope.unread = [];
            $scope.updateUnreadCount();
        });

        $scope.updateUnreadCount = (function () {
            if ($scope.unread.length > 0) {
                document.title = '(' + $scope.unread.length + ') Webhook.site';
            } else {
                document.title = 'Webhook.site';
            }

            window.localStorage.setItem(
                'unread',
                JSON.stringify($scope.unread)
            );
        });

        $scope.markAsRead = (function (requestId) {
            if ($scope.unread.indexOf(requestId) !== -1) {
                $scope.unread.splice($scope.unread.indexOf(requestId), 1);
            }
        });

        $scope.updateUnreadCount();

        /**
         * Push
         */

        $scope.pushSubscribe = (function (token) {
            Echo.leave(token); // Make sure we're not subscribed twice.

            Echo.channel(token)
                .listen('.request.created', function (data) {
                    if (data.truncated) {
                        $scope.getRequest(data.request.token_id, data.request.uuid).then(function (response) {
                            $scope.appendRequest(response);
                        });
                    } else {
                        $scope.appendRequest(data.request);
                    }
                    $scope.requests.total = data.total;
                    $scope.$apply();
                });
        });

        /**
         * Controller actions
         */

        // Requests

        $scope.setCurrentRequest = (function (request) {
            $scope.currentRequestIndex = request.uuid;
            $scope.currentRequest = request;

            $scope.markAsRead(request.uuid);

            // Change the state url so it may be copied from address bar
            // and linked somewhere else
            $state.go('request', { id: $scope.token.uuid, offset: request.uuid, page: $scope.requests.current_page }, { notify: false });
        });

        $scope.deleteRequest = (function (request, requestIndex) {
            $http.delete('/token/' + request.token_id + '/request/' + request.uuid);

            // Remove from view
            $scope.requests.data.splice(requestIndex, 1);
            $scope.requests.total -= 1;
            $scope.markAsRead(request.uuid);
        });

        $scope.deleteAllRequests = (function (request) {
            $http.delete('/token/' + request.token_id + '/request');

            // Remove from view
            $scope.requests = {
                total: 0,
                is_last_page: true,
                data: []
            };
            $scope.currentRequestIndex = 0;
            $scope.currentRequest = {};
            $scope.currentPage = 1;
            $scope.hasRequests = false;
            $scope.resetUnread();
        });

        $scope.getRequest = (function (tokenId, requestId) {
            return $http.get('/token/' + tokenId + '/request/' + requestId)
                .then(function (response) {
                    return response.data;
                });
        });

        $scope.getRequests = (function (token, offset, page) {
            if (!page) {
                page = 1;
            }

            $http.get('/token/' + token + '/requests?page=' + page)
                .then(function (response) {
                    $scope.requests = response.data;

                    if (response.data.data.length > 0) {
                        $scope.hasRequests = true;

                        var activeRequest = 0;

                        for (var requestOffset in $scope.requests.data) {
                            if ($scope.requests.data[requestOffset].uuid == offset) {
                                activeRequest = requestOffset;
                            }
                        }

                        $scope.setCurrentRequest($scope.requests.data[activeRequest]);
                    } else {
                        $scope.hasRequests = false;
                    }
                }, function (response) {
                    $.notify('Requests not found - invalid ID');
                });
        });

        $scope.appendRequest = (function (request) {
            $scope.requests.data.push(request);
            $scope.unread.push(request.uuid);

            if ($scope.currentRequestIndex === 0) {
                $scope.setCurrentRequest($scope.requests.data[0]);
            }
            if ($scope.autoNavEnable) {
                if (!('hidden' in document) || !document.hidden) {
                    $scope.setCurrentRequest($scope.requests.data[$scope.requests.data.length - 1]);
                }
            }
            if ($scope.redirectEnable) {
                $scope.redirect(request, $scope.redirectUrl, $scope.redirectMethod, $scope.redirectContentType, $scope.redirectHeaders);
            }

            $scope.hasRequests = true;
            $scope.$apply();
            $.notify('Request received');
        });

        $scope.convertTypes = ['curl', 'HAR'];

        $scope.convertRequest = (function (request, as) {
            switch (as) {
                case 'curl':
                    let curl = `curl -X '${request.method}' '${request.url}'`;

                    // Headers
                    for (let header in request.headers) {
                        if (!request.headers.hasOwnProperty(header)) {
                            continue;
                        }
                        curl += ` -H '${header}: ${request.headers[header]}'`;
                    }

                    // Body
                    if (request.content !== null && request.content !== '') {
                        curl += ` -d $'${request.content}'`;
                    }

                    return curl;

                case 'HAR':
                    const headers2har = function(headers) {
                        let convHeaders = [];
                        for (let header in headers) {
                            if (!headers.hasOwnProperty(header)) {
                                continue;
                            }
                            convHeaders.push({
                                'name': header,
                                'value': headers[header][0]
                            });
                        }
                        return convHeaders;
                    };
                    return JSON.stringify({
                        'log': {
                            'version': '1.2',
                            'creator': {
                                'name': 'Webhook.site',
                                'version': '1.0',
                            },
                            'entries': [{
                                // TODO: Add requests/responses from custom actions?
                                'startedDateTime': request.created_at,
                                'request': {
                                    'method': request.method,
                                    'url': request.url,
                                    'headers': headers2har(request.headers),
                                    'bodySize': !request.content ? 0 : request.content.length,
                                    'postData': {
                                        'mimeType': !request.headers['content-type']
                                            ? request.headers['content-type'][0]
                                            : 'application/json',
                                        'text': !request.content ? '' : request.content,
                                    }
                                },
                                'response': {
                                    'status': $scope.token.default_status,
                                    'httpVersion': 'HTTP/1.1',
                                    'headers': [
                                        {'name': 'Content-Type', 'value': $scope.token.default_content_type}
                                    ],
                                    'content': {
                                        'size': $scope.token.default_content.length,
                                        'text': $scope.token.default_content,
                                        'mimeType': $scope.token.default_content_type,
                                    }
                                }
                            }]
                        }
                    });

                default:
                    return 'Invalid format';
            }
        });

        $scope.copyRequestAs = (function (request, as) {
            const conv = $scope.convertRequest(request, as);
            copyToClipboard(conv);
            $.notify('Copied request as ' + as);
        });

        // Tokens

        $scope.getToken = (function (tokenId, offset, page) {
            if (!tokenId) {
                $http.post('token')
                    .then(function (response) {
                        $state.go('token', { id: response.data.uuid });
                    });
                $scope.resetUnread();
            } else {
                $http.get('token/' + tokenId)
                    .then(function (response) {
                        $scope.token = response.data;
                        $scope.getRequests(response.data.uuid, offset, page);
                        $scope.pushSubscribe(tokenId);
                        if (page) {
                            $scope.currentPage = page;
                        }
                    }, function (response) {
                        $scope.token = null;
                        $scope.getToken();
                        if (response.status === 404 || response.status === 410) {
                            $scope.token = null;
                            $scope.getToken();
                            $.notify('<b>URL not found</b><br>Invalid ID, created new URL', { delay: 10000 });
                        }
                    });
            }
        });

        $scope.getCustomToken = (function () {
            var formData = {};
            $('#createTokenForm')
                .serializeArray()
                .map(function (value) {
                    if (value.value != '') {
                        formData[value.name] = value.value;
                    }
                });

            $http.post('token', formData)
                .then(function (response) {
                    $state.go('token', { id: response.data.uuid });
                    $scope.resetUnread();
                    $.notify('New URL created');
                }, function (response) {
                    if (response.status === 422) {
                        let errors = [];
                        for (let error in response.data) {
                            if (response.data.hasOwnProperty(error)) {
                                errors.push(response.data[error]);
                            }
                        }
                        $.notify('Error creating token:<br>' + errors.join(', '), { delay: 10000 });
                        return;
                    }

                    $.notify('Error creating token (' + response.status + ')');
                });
        });

        $scope.editToken = (function (tokenId) {
            var formData = {};

            $('#editTokenForm')
                .serializeArray()
                .map(function (value) {
                    if (value.value !== '') {
                        formData[value.name] = value.value;
                    }
                });

            $http.put('token/' + tokenId, formData)
                .then(function (response) {
                    $scope.token = response.data;
                    $.notify('URL updated!');
                });
        });

        $scope.toggleCors = (function (token) {
            $http.put('token/' + token.uuid + '/cors/toggle')
                .then(function (response) {
                    if (response.status === 200) {
                        $scope.token.actions = response.data.enabled;
                        $scope.token.actions
                            ? $.notify('CORS enabled.')
                            : $.notify('CORS disabled.');

                    } else {
                        $.notify('Could not toggle CORS: ' + response.data.error.message);
                    }
                }).catch(function (response) {
                    $.notify('Could not toggle CORS: ' + response.data.error.message);
                });
        });

        // Pagination

        $scope.getPreviousPage = (function (token) {
            $http({
                url: '/token/' + token + '/requests',
                params: { page: $scope.requests.current_page - 1 }
            }).success(function (data, status, headers, config) {
                // We use is_last_page to keep track of whether we should load more pages.
                $scope.requests.is_last_page = data.is_last_page;
                $scope.requests.current_page = data.current_page;
                $scope.requests.data = data.data.concat($scope.requests.data);
            });
        });

        $scope.getNextPage = (function (token) {
            $http.get('/token/' + token + '/requests', {
                params: { page: $scope.requests.current_page + 1 }
            }).then(function (response) {
                // We use is_last_page to keep track of whether we should load more pages.
                $scope.requests.is_last_page = response.data.is_last_page;
                $scope.requests.current_page = response.data.current_page;
                $scope.currentPage = response.data.current_page;
                $scope.requests.data = $scope.requests.data.concat(response.data.data);
            });
        });

        $scope.goToNextRequest = (function () {
            $scope.setCurrentRequest(
                $scope.requests.data[$scope.requests.data.indexOf($scope.currentRequest) + 1]
            );

            if ($scope.requests.data.indexOf($scope.currentRequest) === $scope.requests.data.length - 1) {
                $scope.getNextPage($scope.token.uuid);
            }
        });

        $scope.parseUrl = (function (url) {
            var parser = document.createElement('a');
            parser.href = url;
            return parser;
        })

        $scope.redirect = (function (request, url, method, contentType, headers) {
            let parser = $scope.parseUrl(request.url);
            let headersList = [];
            let path = parser.pathname.match('\/[A-Za-z0-9-]+(/.*)');
            if (path === null) {
                path = '';
            } else {
                path = path[1];
            }

            if (headers !== null) {
                headersList = headers.split(",").filter(val => val !== "")
            }

            let headersDict = {
                'Content-Type': (!contentType ? 'text/plain' : contentType)
            }

            headersList.forEach(header => {
                if (header in request.headers) {
                    headersDict[header] = request.headers[header];
                }
            });


            var redirectUrl = url + path + parser.search;

            $http({
                'method': (!method ? request.method : method),
                'url': redirectUrl,
                'data': request.content,
                'headers': headersDict
            }).then(
                function ok(response) {
                    $.notify('Redirected request to ' + redirectUrl + '<br>Status: ' + response.statusText);
                },
                function error(response) {
                    $.notify(
                        'Error redirecting request to ' + redirectUrl + '<br>Status: ' + response.statusText,
                        {
                            delay: 5000,
                            type: 'danger'
                        }
                    );
                }
            );
        });

        $scope.getLabel = function (method) {
            switch (method) {
                case 'POST':
                    return 'info';
                case 'GET':
                    return 'success';
                case 'DELETE':
                    return 'danger';
                case 'HEAD':
                    return 'primary';
                case 'PATCH':
                    return 'warning';
                default:
                    return 'default';
            }
        };

        /**
         * JSON formatting
         */

        $scope.isValidJSON = function (text) {
            try {
                JSON.parse(text);
            } catch (e) {
                return false;
            }
            return true;
        };

        $scope.formatContentJson = function (content) {
            if (!content) {
                return '';
            }

            try {
                var json = JSONbig.parse(content);
                if (typeof json != 'string') {
                    json = JSONbig.stringify(json, undefined, 2);
                }
            } catch (e) {
                return content;
            }
            return json;
        };

        $scope.formatContent = function (content) {
            if (!content) {
                return '';
            }
            
            let hloutput = hljs.highlightAuto(content);

            if (hloutput.language === "json") {
                content = $scope.formatContentJson(content)
            }
            if (hloutput.language === "xml") {
                content = prettyData.xml(content);
            }

            return content;
        };

        $scope.localDate = (function (dateTimeString) {
            return moment.utc(dateTimeString).local().format('lll');
        });

        $scope.relativeDate = (function (dateTimeString) {
            return moment.utc(dateTimeString).fromNow();
        });

        // Initialize app. Check whether we need to load a token.
        if ($state.current.name) {
            if ($scope.getSetting('token') && !$stateParams.id) {
                $state.go('token', { id: $scope.getSetting('token').uuid });
            } else {
                $scope.getToken($stateParams.id, $stateParams.offset, $stateParams.page);
            }
        }
    }])
    .run(['$rootScope', '$state', '$stateParams',
        function ($rootScope, $state, $stateParams) {
            $rootScope.$state = $state;
            $rootScope.$stateParams = $stateParams;
        }]
    );
