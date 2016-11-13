angular
    .module("app", [])
    .config(function($logProvider) {
        $logProvider.debugEnabled(true);
    })
    .controller("AppController", function ($scope, $http) {
        $scope.token = {};
        $scope.requests = [];
        $scope.currentRequestIndex = 0;
        $scope.currentRequest = {};
        $scope.hasRequests = false;
        $scope.domain = window.location.href;

        $scope.setCurrentRequest = (function(value) {
            $scope.currentRequestIndex = value;
            $scope.currentRequest = $scope.requests.data[value];
            console.log('Current:', value);
            console.log($scope.currentRequest);
        });

        // Initialize Clipboard copy button
        new Clipboard('#copyTokenUrl');

        // Let's start by grabbing a token
        $http.post('token')
            .then(function(response, status) {
                $scope.token = response.data;
                console.log($scope.token.uuid);
            }, function(response) {
                console.log('Could not get token: ' + response.data);
            });

        $http.get('/token/b874bafd-67e4-408c-a7b5-bb2c21ad91ba/requests')
            .then(function (response, status) {
                $scope.requests = response.data;
                $scope.setCurrentRequest($scope.currentRequestIndex);
                $scope.hasRequests = true;
            });
    });