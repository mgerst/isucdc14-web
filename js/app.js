var app = angular.module("someLegalSite", ['ngRoute']);

app.config(['$routeProvider',
    function($routeProvider) {
        $routeProvider.
            when('/', {
                templateUrl: 'partials/home.html',
                controller: 'LawyerCtrl'
            }).
            when('/cases', {
                templateUrl: 'partials/cases.html',
                controller: 'CaseCtrl'
            }).
            when('/login', {
                templateUrl: 'partials/login.html',
                controller: 'LoginController'
            }).
            when('/logout', {
                template: '',
                controller: 'LogoutController'
            }).
            when('/casefiles', {
                templateUrl: 'partials/casefiles.html',
                controller: 'CaseFilesCtrl'
            }).
            when('/addcase', {
                templateUrl: 'partials/create.html',
                controller: 'CaseAddCtrl'
            }).
            otherwise({
                redirectTo: '/'
            });
    }
]);

app.constant('AUTH_EVENTS', {
    loginSuccess: 'auth-login-success',
    loginFailed: 'auth-login-failed',
    logoutSuccess: 'auth-logut-success',
    sessionTimeout: 'auth-session-timeout',
    notAuthenticated: 'auth-not-authenticated',
    notAuthorized: 'auth-not-authorized'
});

app.constant('USER_ROLES', {
    all: '*',
    admin: 'ITTeam',
    negligence: 'Negligence',
    divorce: 'Divorce',
    taxevasion: 'TaxEvasion',
    lawyer: 'Lawyer'
});

app.controller("LawyerCtrl", function($scope,$http) {
    $scope.lawyers = [];

	$http.get("/lawyers.php").success(function(response) {
        if (response.error) {
            alert("Could not connect to database");
            $scope.lawyers = [];
        } else {
            $scope.lawyers = response;
        }
    });
});

app.controller("CaseFilesCtrl", function($scope,$location,AuthService) {
    if (!AuthService.isAuthenticated()) {
        $location.url('/login');
    }
});

app.controller("CaseCtrl", function($scope,$http,AuthService,$location) {
    if (!AuthService.isAuthenticated()) {
        $location.url('/login');
    }

    $scope.cases = [];
	$http.get("/clients.php").success(function(response) {
        if (response.error) {
            alert("Could not connect to database");
            $scope.cases = []
        } else {
            $scope.cases = response;
        }
    });
});

app.controller('LoginController', function ($scope, $rootScope,
    AUTH_EVENTS, AuthService, $location) {
    $scope.credentials = {
        username: '',
        password: ''
    };
    $scope.login = function (credentials) {
        AuthService.login(credentials).then(function (user) {
            $rootScope.$broadcast(AUTH_EVENTS.loginSuccess);
            $scope.setCurrentUser(user);
            $location.url('/cases');
        }, function () {
            $rootScope.$broadcast(AUTH_EVENTS.loginFailed);
            alert("Invalid Credentials");1
        });
    };
});

function LogoutController($scope, $rootScope, AUTH_EVENTS, AuthService, $location) {
    $rootScope.$broadcast(AUTH_EVENTS.logoutSuccess);
    AuthService.logout();
    $scope.setCurrentUser(null);
    $location.url("/");
};

app.controller("CaseAddCtrl", function($scope, $location, $http, AuthService) {
    if (!AuthService.isAuthenticated()) {
        $location.url('/');
    }

    $scope.create = function (data) {
        $http.post('/create.php', data)
             .then(function (res) {
                $location.url('/cases');
             });
    }
});

app.factory('AuthService', function ($http, Session) {
    var authService = {};

    authService.login = function (credentials) {
        return $http
            .post('/login.php', credentials)
            .then(function (res) {
                Session.create(res.data.username, res.data.display,
                               res.data.role);
                return {username: res.data.username, display: res.data.display, role: res.data.role};
            });
    };

    authService.logout = function () {
        // return $http
        //     .post('/logout.php')
        //     .then(function (res) {
                Session.destroy();
        //         return null;
        //     });
    };

    authService.isAuthenticated = function() {
        return !!Session.username;
    };

    authService.isAuthorized = function (authorizedRoles)
    {
        if (!angular.isArray(authorizedRoles)) {
            authorizedRoles = [authorizedRoles];
        }
        return (authService.isAuthenticated() &&
            authorizedRoles.indexOf(Session.userRole) !== -1);
    };

    return authService;
});

app.service('Session', function() {
    this.create = function (sessionId, display, userRoles) {
        this.id = sessionId;
        this.username = username;
        this.userRoles = userRoles;
    };
    this.destroy = function() {
        this.id = null;
        this.username = null;
        this.userRoles = null;
    };
    return this;
});

app.controller('ApplicationController', function ($scope,
    USER_ROLES, AuthService, $location) {
    $scope.currentUser = null;
    $scope.userRoles = USER_ROLES;
    $scope.isAuthorized = AuthService.isAuthorized;

    $scope.setCurrentUser = function (user) {
        $scope.currentUser = user;
    };

    $scope.isActive = function(route) {
        return route === $location.path();
    };

    $scope.isAuthorized = function(role) {
        return AuthService.isAuthorized(role);
    };
});
