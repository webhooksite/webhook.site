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
    .config(['$stateProvider', '$urlRouterProvider','$locationProvider',
        function ($stateProvider, $urlRouterProvider, $locationProvider) {
            // States
            $stateProvider
                .state('home', {
                    url: "/",
                    controller: 'AppController'
                })
                .state('token', {
                    url: "/{id:guid}",
                    controller: 'AppController'
                })
            ;
            $urlRouterProvider.otherwise('/');
        }
    ])
    .controller("AppController", ['$scope', '$http', '$stateParams', '$state', function ($scope, $http, $stateParams, $state) {
        $scope.token = {};
        $scope.requests = {
            data: []
        };
        $scope.currentRequestIndex = 0;
        $scope.currentRequest = {};
        $scope.hasRequests = false;
        $scope.domain = window.location.hostname;

        // Initialize Clipboard copy button
        new Clipboard('#copyTokenUrl');

        // Initialize Pusher
        var channel = null;
        var pusher = new Pusher(AppConfig.PusherToken, {
            cluster: 'eu',
            encrypted: true
        });

        // Initialize notify.js
        $.notifyDefaults({
            placement: {
                from: "bottom"
            },
            animate:{
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
        });

        $scope.getRequests = (function (token) {
            $http.get('/token/'+ token +'/requests')
                .then(function (response) {
                    $scope.requests = response.data;
                    $scope.setCurrentRequest($scope.currentRequestIndex);

                    if (response.data.data.length > 0) {
                        $scope.hasRequests = true;
                    }
                }, function (response) {
                    alert('requests not found');
                });

            channel = pusher.subscribe(token);
            channel.bind('request.new', function(data) {
                $scope.requests.data.push(data.request);
                if (!$scope.hasRequests) {
                    $scope.setCurrentRequest(0);
                }
                $scope.hasRequests = true;
                $scope.$apply();
                $.notify('Request received');
            });
        });

        $scope.getToken = (function (tokenId) {
            if (tokenId == undefined) {
                $http.post('token')
                    .then(function(response) {
                        $state.go('token', {id: response.data.uuid});
                    });
            } else {
                $http.get('token/' + tokenId)
                    .then(function(response) {
                        $scope.token = response.data;
                        $scope.getRequests(response.data.uuid);
                    });
            }
        });

        $scope.getCustomToken = (function () {
            var formData = {};
            $('#createTokenForm')
                .serializeArray()
                .map(function(value){
                    if (value.value != '') {
                        formData[value.name] = value.value;
                    }
                });

            $http.post('token', formData)
                .then(function(response) {
                    $scope.hasRequests = false;
                    $scope.token = response.data;
                    $scope.getRequests(response.data.uuid);
                    $.notify('New token created');
                });
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

        // Initialize app. Check whether we need to load a token.
        if ($scope.$state.$current.name != '') {
            $scope.getToken($scope.$state.params.id);
        }
    }])
    .run(['$rootScope', '$state', '$stateParams',
        function ($rootScope, $state, $stateParams) {
            $rootScope.$state = $state;
            $rootScope.$stateParams = $stateParams;
        }]
    );