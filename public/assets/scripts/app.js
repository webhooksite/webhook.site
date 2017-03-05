angular
    .module("app", [
        'ui.router'
    ])
    .config(['$urlMatcherFactoryProvider', function($urlMatcherFactoryProvider) {
        var GUID_REGEXP = /^[a-f\d]{8}-([a-f\d]{4}-){3}[a-f\d]{12}$/i;
        $urlMatcherFactoryProvider.type('guid', {
            encode: angular.identity,
            decode: angular.identity,
            is: function(item) {
                return GUID_REGEXP.test(item);
            }
        });
    }])
    .config(['$stateProvider', '$urlRouterProvider', '$locationProvider',
        function($stateProvider, $urlRouterProvider, $locationProvider) {
            // States
            $urlRouterProvider.otherwise('/');

            $stateProvider
                .state('home', {
                    url: "/",
                    controller: 'AppController'
                })
                .state('request', {
                    url: "/{id:guid}/{offset:int}",
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
        $scope.token = {};
        $scope.requests = {
            data: []
        };
        $scope.currentRequestIndex = 0;
        $scope.currentRequest = {};
        $scope.currentPage = 1;
        $scope.hasRequests = false;
        $scope.domain = window.location.hostname;
        $scope.pusher = null;
        $scope.pusherChannel = null;

        /**
         * App Initialization
         */


        // Initialize Clipboard copy button
        new Clipboard('#copyTokenUrl');

        // Initialize Pusher
        $scope.pusherChannel = null;
        $scope.pusher = new Pusher(AppConfig.PusherToken, {
            cluster: 'eu',
            encrypted: true
        });

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

        /**
         * Controller actions
         */

        $scope.setCurrentRequest = (function(value) {
            $scope.currentRequestIndex = value;
            $scope.currentRequest = $scope.requests.data[value];

            // Change the state url so it may be copied from address bar
            // and linked somewhere else
            $state.go('request', {id: $scope.token.uuid, offset: value}, {notify: false});
        });

        $scope.getRequests = (function(token, offset) {
            $http.get('/token/' + token + '/requests')
                .then(function(response) {
                    $scope.requests = response.data;

                    if (response.data.data.length > 0) {
                        $scope.hasRequests = true;
                    } else {
                        $scope.hasRequests = false;
                    }

                    // Circuit breaker: don't keep loading after 10 pages (50*10 items)
                    if (offset && offset < 500) {
                        $scope.findOffset(token, offset);
                    } else {
                        $scope.setCurrentRequest($scope.currentRequestIndex);
                    }
                }, function(response) {
                    $.notify('Requests not found - invalid ID');
                });

            $scope.pusherChannel = $scope.pusher.subscribe(token);
            $scope.pusherChannel.bind('request.new', function(data) {
                $scope.requests.data.push(data.request);
                if (!$scope.hasRequests) {
                    $scope.setCurrentRequest(0);
                }
                $scope.hasRequests = true;
                $scope.$apply();
                $.notify('Request received');
            });
        });

        $scope.findOffset = (function(token, offset) {
            $scope.$watch('requests', function() {
                if (offset < $scope.requests.data.length) {
                    $scope.setCurrentRequest(offset);
                } else if ($scope.requests.next_page_url) {
                    // Keep loading the next page until we find our offset
                    $scope.getNextPage(token);
                    $timeout(function() { $scope.findOffset(token, offset); }, 500);
                }
            });
        });

        $scope.getNextPage = (function(token) {
            // Increment page count
            $scope.currentPage += 1;

            $http({
                url: '/token/' + token + '/requests',
                params: {page: $scope.currentPage}
            }).success(function(data, status, headers, config) {
                // We use next_page_url to keep track of whether we should load more pages.
                $scope.requests.next_page_url = data.next_page_url;
                $scope.requests.data = $scope.requests.data.concat(data.data);
            });
        });

        $scope.getToken = (function(tokenId, offset) {
            if (tokenId == undefined) {
                $http.post('token')
                    .then(function(response) {
                        $state.go('token', {id: response.data.uuid});
                    });
            } else {
                $http.get('token/' + tokenId)
                    .then(function(response) {
                        $scope.token = response.data;
                        $scope.getRequests(response.data.uuid, offset);
                    }, function(response) {
                        $.notify('Requests not found - invalid ID');
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

        $scope.formatContent = function (content) {
            var json = JSON.parse(content);
            return $scope.highlightJSON(json);
        };

        // Thanks to http://stackoverflow.com/a/7220510
        $scope.highlightJSON = function (json) {
            if (typeof json != 'string') {
                json = JSON.stringify(json, undefined, 2);
            }
            json = json.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
            return json.replace(/("(\\u[a-zA-Z0-9]{4}|\\[^u]|[^\\"])*"(\s*:)?|\b(true|false|null)\b|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?)/g, function (match) {
                var cls = 'number';
                if (/^"/.test(match)) {
                    if (/:$/.test(match)) {
                        cls = 'key';
                    } else {
                        cls = 'string';
                    }
                } else if (/false/.test(match)) {
                    cls = 'boolean boolean-false';
                } else if (/true/.test(match)) {
                    cls = 'boolean boolean-true';
                } else if (/null/.test(match)) {
                    cls = 'null';
                }
                return '<span class="' + cls + '">' + match + '</span>';
            });
        };

        // Initialize app. Check whether we need to load a token.
        if ($state.current.name) {
            $scope.getToken($stateParams.id, $stateParams.offset);
        }
    }])
    .run(['$rootScope', '$state', '$stateParams',
        function($rootScope, $state, $stateParams) {
            $rootScope.$state = $state;
            $rootScope.$stateParams = $stateParams;
        }]
    );