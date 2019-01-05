angular
    .module("app", [
        'ui.router'
    ])
    .config(['$stateProvider', '$urlRouterProvider', '$urlMatcherFactoryProvider',
        function($stateProvider, $urlRouterProvider, $urlMatcherFactoryProvider) {
            var GUID_REGEXP = /^[a-f\d]{8}-([a-f\d]{4}-){3}[a-f\d]{12}$/i;
            $urlMatcherFactoryProvider.type('guid', {
                encode: angular.identity,
                decode: angular.identity,
                is: function(item) {
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
    .controller("AppController", ['$scope', '$http', '$stateParams', '$state', '$timeout', function($scope, $http, $stateParams, $state, $timeout) {
        /**
         * Settings handling
         */

        // Array of scope variables to automatically save
        var settings = [
            'redirectEnable',
            'redirectUrl',
            'redirectContentType',
            'redirectMethod',
            'token',
            'formatJsonEnable',
            'autoNavEnable'
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

            if (!value || typeof(value) === 'undefined' || value === 'undefined') {
                if (typeof(defaultValue) === 'undefined') {
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
        $scope.domain = window.location.hostname;
        $scope.appConfig = window.AppConfig;

        // Load settings
        $scope.formatJsonEnable = $scope.getSetting('formatJsonEnable', false);
        $scope.autoNavEnable = $scope.getSetting('autoNavEnable', false);
        $scope.redirectEnable = $scope.getSetting('redirectEnable', false);
        $scope.redirectMethod = $scope.getSetting('redirectMethod', '');
        $scope.redirectUrl = $scope.getSetting('redirectUrl', null);
        $scope.redirectContentType = $scope.getSetting('redirectContentType', 'text/plain');

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
        $scope.$watch($scope.saveSettings);

        /**
         * Controller actions
         */

        $scope.setCurrentRequest = (function(request) {
            $scope.currentRequestIndex = request.uuid;
            $scope.currentRequest = request;

            // Change the state url so it may be copied from address bar
            // and linked somewhere else
            $state.go('request', {id: $scope.token.uuid, offset: request.uuid, page: $scope.requests.current_page}, {notify: false});
        });

        $scope.deleteRequest = (function (request, requestIndex) {
            $http.delete('/token/' + request.token_id + '/request/' + request.uuid);

            // Remove from view
            $scope.requests.data.splice(requestIndex, 1);
            $scope.requests.total -= 1;
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
        });

        $scope.getRequest = (function (tokenId, requestId) {
            return $http.get('/token/' + tokenId + '/request/' + requestId)
                .then(function (response) {
                    return response.data;
                });
        });

        $scope.getRequests = (function(token, offset, page) {
            if (!page) {
                page = 1;
            }

            $http.get('/token/' + token + '/requests?page=' + page)
                .then(function(response) {
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
                }, function(response) {
                    $.notify('Requests not found - invalid ID');
                });
        });

        $scope.appendRequest = (function (request) {
            $scope.requests.data.push(request);

            if ($scope.currentRequestIndex === 0) {
                $scope.setCurrentRequest($scope.requests.data[0]);
            }
            if ($scope.autoNavEnable) {
                $scope.setCurrentRequest($scope.requests.data[$scope.requests.data.length - 1]);
            }
            if ($scope.redirectEnable) {
                $scope.redirect(request, $scope.redirectUrl, $scope.redirectMethod, $scope.redirectContentType);
            }

            $scope.hasRequests = true;
            $scope.$apply();
            $.notify('Request received');
        });

        $scope.pushSubscribe = (function (token) {
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
                });
        });

        $scope.getToken = (function(tokenId, offset, page) {
            if (!tokenId) {
                $http.post('token')
                    .then(function(response) {
                        $state.go('token', {id: response.data.uuid});
                    });
            } else {
                $http.get('token/' + tokenId)
                    .then(function(response) {
                        $scope.token = response.data;
                        $scope.getRequests(response.data.uuid, offset, page);
                        $scope.pushSubscribe(tokenId);
                    }, function(response) {
                        $scope.token = null;
                        $.notify('Requests not found - invalid ID, creating new URL', { delay: 5000 });
                        $scope.getToken();
                    });
            }
        });

        $scope.getCustomToken = (function() {
            var formData = {};
            $('#createTokenForm')
                .serializeArray()
                .map(function(value) {
                    if (value.value != '') {
                        formData[value.name] = value.value;
                    }
                });

            $http.post('token', formData)
                .then(function(response) {
                    $state.go('token', {id: response.data.uuid});
                    $.notify('New URL created');
                });
        });

        $scope.getNextPage = (function(token) {
            $http({
                url: '/token/' + token + '/requests',
                params: {page: $scope.requests.current_page + 1}
            }).success(function(data, status, headers, config) {
                // We use is_last_page to keep track of whether we should load more pages.
                $scope.requests.is_last_page = data.is_last_page;
                $scope.requests.current_page = data.current_page;
                $scope.requests.data = $scope.requests.data.concat(data.data);
            });
        });

        $scope.parseUrl = (function (url) {
            var parser = document.createElement('a');
            parser.href = url;
            return parser;
        })

        $scope.redirect = (function (request, url, method, contentType) {
            let parser = $scope.parseUrl(request.url);
            let path = parser.pathname.match('\/[A-Za-z0-9-]+(/.*)');
            if (path === null) {
                path = '';
            } else {
                path = path[1];
            }

            var redirectUrl = url + path + parser.search;

            $http({
                'method': (!method ? request.method : method),
                'url': redirectUrl,
                'data': request.content,
                'headers': {
                    'Content-Type': (!contentType ? 'text/plain' : contentType)
                }
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

        $scope.getLabel = function(method) {
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
                var json = JSON.parse(content);
                if (typeof json != 'string') {
                    json = JSON.stringify(json, undefined, 2);
                }
            } catch (e) {
                return content;
            }
            return json;
        };

        // Initialize app. Check whether we need to load a token.
        if ($state.current.name) {
            if ($scope.getSetting('token') && !$stateParams.id) {
                $state.go('token', {id: $scope.getSetting('token').uuid});
            } else {
                $scope.getToken($stateParams.id, $stateParams.offset, $stateParams.page);
            }
        }
    }])
    .run(['$rootScope', '$state', '$stateParams',
        function($rootScope, $state, $stateParams) {
            $rootScope.$state = $state;
            $rootScope.$stateParams = $stateParams;
        }]
    );
