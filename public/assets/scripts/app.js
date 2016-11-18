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
            window.location.hash = "/" + token;
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