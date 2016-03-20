angular.module("app", [])

    .controller("AppController", function ($scope, $http) {
        $scope.token = {};

        $http.post('token')
            .then(function(response, status) {
                $scope.token = response.data;
                console.log($scope.token.uuid);
                console.log(status);
            }, function(response) {
                console.log('Error: ' + response.data);
            });
    });