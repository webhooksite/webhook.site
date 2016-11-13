angular
    .module("app", [])
    .config(function($logProvider) {
        $logProvider.debugEnabled(true);
    })
    .controller("AppController", function ($scope, $http, $location) {
        $scope.token = {};
        $scope.requests = [];
        $scope.currentRequestIndex = 0;
        $scope.currentRequest = {};
        $scope.hasRequests = false;
        $scope.domain = window.location.hostname;

        // Initialize Clipboard copy button
        new Clipboard('#copyTokenUrl');

        // Initialize Pusher
        var channel = null;
        var pusher = new Pusher('6bfb8bce49f8d53fbc4a', {
            cluster: 'eu',
            encrypted: true
        });
        Pusher.logToConsole = true;


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
                    $scope.hasRequests = true;
                }, function (response) {
                    alert('requests not found');
                });

            channel = pusher.subscribe(token);
            channel.bind('request.new', function(data) {
                $scope.requests.data.push(data.request);
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