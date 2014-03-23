'use strict';

/* Controllers */

angular.module('myApp.controllers', []).
  controller('LoginCtrl', ['$rootScope', '$scope', '$location', 'Auth', function ($rootScope, $scope, $location, Auth) {
    $scope.user = {
      username: "info@dg37.com.au",
      password: ""
    };
    $scope.login = function () {
      Auth.login($scope.user,
        function (res) {
          $location.path('/');
        },
        function (err) {
          $rootScope.error = "Failed to login. Check your credentials.";
        });
    };
  }])
  .controller('StockistsCtrl', ['$scope', '$rootScope', '$location', 'Stockists', function ($scope, $rootScope, $location, Stockists) {
    $scope.stockists = [];
    $scope.loading = true;

    Stockists.getAll(function (res) {
      $scope.stockists = res.Stockists;
      $scope.loading = false;
    }, function (err) {
      $rootScope.error = "Failed to fetch stockists.";
      $scope.loading = false;
    });

    $scope.goToDetails = function (id) {
      $location.path('/stockist/1' . id);
    };
  }])
  .controller('StockistCtrl', ['$scope', '$rootScope', '$location', '$routeParams', 'Stockists', function ($scope, $rootScope, $location, $routeParams, Stockists) {
    $scope.stockist = {};
    $scope.loading = true;

    Stockists.getOneByName($routeParams.name, function (res) {
      $scope.stockist = res.Stockist;
      $scope.loading = false;
    }, function (err) {
      $rootScope.error = "Failed to fetch stockist.";
      $scope.loading = false;
    });

    $scope.states = {
      'ACT': {
        'name': 'Australian Capital Territory',
        'postcode': '^(26\\d\\d)$|^(29\\d\\d)$|^(02\\d\\d+)$'
      },
      'NSW': {
        'name': 'New South Wales',
        'postcode': '^(2\\d\\d\\d)$|^(1\\d\\d\\d+)$'
      },
      'NT': {
        'name': 'Northern Territory',
        'postcode': '^08\\d\\d$'
      },
      'QLD': {
        'name': 'Queensland',
        'postcode': '^(4\\d\\d\\d)$|^(9\\d\\d\\d+)$'
      },
      'SA': {
        'name': 'South Australia',
        'postcode': '^5\\d\\d\\d$'
      },
      'TAS': {
        'name': 'Tasmania',
        'postcode': '^7\\d\\d\\d$'
      },
      'VIC': {
        'name': 'Victoria',
        'postcode': '^(3\\d\\d\\d)$|^(8\\d\\d\\d+)$'
      },
      'WA': {
        'name': 'Western Australia',
        'postcode': '^6\\d\\d\\d$'
      }
    };

    $scope.$watch('stockist.postcode', function(newValue, oldValue){
      var x;
      for (x in $scope.states) {
        if ((new RegExp($scope.states[x].postcode)).test(newValue)) {
          $scope.stockist.state = x;
          return;
        }
      }
    });

    $scope.cancel = function () {
      $location.path('/stockists');
    };

    $scope.submit = function () {
      $scope.loading = true;
      console.log('save');
      Stockists.save($scope.stockist, function () {
        $scope.loading = false;
        console.log('success');
        $location.path('/stockists');
      }, function () {
        console.log('error');
        $rootScope.error = "Failed to save stockist.";
        $scope.loading = false;
      });
    };
  }])
  .controller('NavCtrl', ['$rootScope', '$scope', '$location', 'Auth', 'User', 'userRoles', 'accessLevels', function ($rootScope, $scope, $location, Auth, User, userRoles, accessLevels) {
    $scope.user = User.current();
    $scope.userRoles = userRoles;
    $scope.accessLevels = accessLevels;
    $scope.showNav = false;

    $scope.logout = function () {
      Auth.logout(function () {
        $location.path('/login');
      }, function () {
        $rootScope.error = "Failed to logout";
      });
    };

    $scope.toggle = function () {
      console.log('toggle');
      $scope.showNav = !$scope.showNav;
    };
  }]);