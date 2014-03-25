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
  .controller('StockistCtrl', ['$scope', '$rootScope', '$location', '$routeParams', 'Stockists', 'AustraliaStates', function ($scope, $rootScope, $location, $routeParams, Stockists, AustraliaStates) {
    $scope.stockist = {};
    $scope.loading = true;

    Stockists.getOneByName($routeParams.name, function (res) {
      $scope.stockist = res.Stockist;
      $scope.loading = false;
    }, function (err) {
      $rootScope.error = "Failed to fetch stockist.";
      $scope.loading = false;
    });

    $scope.states = AustraliaStates.getAll();

    $scope.$watch('stockist.postcode', function(newValue){
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
      Stockists.save($scope.stockist, function () {
        $scope.loading = false;
        $location.path('/stockists');
      }, function () {
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
  }])
  .controller('ImportCtrl', ['$scope', '$upload', '$location', function ($scope, $upload, $location) {
    $scope.loading = false;
    $scope.onFileSelect = function ($files) {
      $scope.loading = true;
      //$files: an array of files selected, each file has name, size, and type.
      if ($files.length > 0) {
        var file = $files[0],
          fileReader = new FileReader();
        fileReader.readAsArrayBuffer(file);
        fileReader.onload = function (e) {
          $scope.upload = $upload.http({
            url: '/api/stockists.csv',
            headers: {'Content-Type': file.type},
            data: e.target.result
          }).progress(function (evt) {
            console.log('percent: ' + parseInt(100.0 * evt.loaded / evt.total));
          }).success(function (data, status, headers, config) {
            // file is uploaded successfully
            $location.path('/stockists');
          });
        };
      }
    };
  }]);