'use strict';


// Declare app level module which depends on filters, and services
angular.module('stockists', [
    'ngRoute',
    'ngTouch',
    'angularFileUpload',
    'myApp.filters',
    'myApp.services',
    'myApp.directives',
    'myApp.controllers'
  ]).
  config(['accessLevels', '$routeProvider', '$locationProvider', '$httpProvider', function (access, $routeProvider, $locationProvider, $httpProvider) {
    $routeProvider.when('/404',
      {
        templateUrl: '404',
        access: access.public
      });
    $routeProvider.when('/login',
      {
        templateUrl: 'partials/login-form.html',
        controller: 'LoginCtrl',
        access: access.public
      });

    $routeProvider.when('', {
      redirectTo: '/',
      access: access.user
    });
    $routeProvider.when('/', {
      redirectTo: '/stockists',
      access: access.user
    });

    $routeProvider.when('/import', {
      templateUrl: 'partials/import.html',
      controller: 'ImportCtrl',
      access: access.user
    });
    $routeProvider.when('/stockists', {
      templateUrl: 'partials/stockists.html',
      controller: 'StockistsCtrl',
      access: access.user
    });
    $routeProvider.when('/stockist/:name', {
      templateUrl: 'partials/stockist.html',
      controller: 'StockistCtrl',
      access: access.user
    });

    $routeProvider.when('/admin',
      {
        templateUrl: 'partials/admin',
        controller: 'AdminCtrl',
        access: access.admin
      });

    $routeProvider.otherwise({redirectTo: '/404'});

    //$locationProvider.html5Mode(true);

    $httpProvider.interceptors.push('authInterceptor');
  }])
  .run(['$rootScope', '$location', '$http', 'Auth', function ($rootScope, $location, $http, Auth) {

    $rootScope.$on("$routeChangeStart", function (event, next, current) {
      if ('access' in next && !Auth.authorize(next.access)) {
        $rootScope.error = "Seems like you tried accessing a route you don't have access to...";
        event.preventDefault();

        if (Auth.isLoggedIn()) {
          $location.path('/');
        } else {
          $rootScope.error = null;
          $location.path('/login');
        }
      }
    });

  }]);
