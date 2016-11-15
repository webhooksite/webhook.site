angular
    .module("app", [])
    .config(function($logProvider) {
        $logProvider.debugEnabled(true);
    })
    .controller("AppController", function ($scope, $http, $location) {
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

        /**
         * Controller actions
         */

        $scope.setCurrentRequest = (function(value) {
            $scope.currentRequestIndex = value;
            $scope.currentRequest = $scope.requests.data[value];
        });

        $scope.getRequests = (function (token) {
            window.location.hash = token;
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
            });
        });

        $scope.getToken = (function (tokenId) {
            if (tokenId == undefined) {
                $http.post('token')
                    .then(function(response) {
                        $scope.token = response.data;
                        $scope.getRequests(response.data.uuid);
                    });
            } else {
                $http.get('token/' + tokenId)
                    .then(function(response) {
                        $scope.token = response.data;
                        $scope.getRequests(response.data.uuid);
                    });
            }
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
        if (window.location.hash) {
            var uuid = window.location.hash
                .match('[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}');

            if (!uuid) {
                $scope.getToken();
            } else {
                $scope.getToken(uuid[0]);
            }
        } else {
            $scope.getToken();
        }
    });