'use strict';

/* Services */

/**
 * generates auth constants
 */
(function(){
  var
    roles =
    [
      'public',
      'user',
      'admin'
    ],
    accessLevels =
    {
      'public': "*",
      'anon': ['public'],
      'user': ['user', 'admin'],
      'admin': ['admin']
    },
    userRoles = buildRoles(roles);

  angular.module('myApp.services', [])
    .constant('roles', roles)
    .constant('userRoles', userRoles)
    .constant('accessLevels', buildAccessLevels(accessLevels, userRoles));

  /*
   Method to build a distinct bit mask for each role
   It starts off with "1" and shifts the bit to the left for each element in the
   roles array parameter
   */
  function buildRoles(roles) {

    var bitMask = "01";
    var userRoles = {};

    for (var role in roles) {
      var intCode = parseInt(bitMask, 2);
      userRoles[roles[role]] = {
        bitMask: intCode,
        title: roles[role]
      };
      bitMask = (intCode << 1 ).toString(2)
    }

    return userRoles;
  }

  /*
   This method builds access level bit masks based on the accessLevelDeclaration parameter which must
   contain an array for each access level containing the allowed user roles.
   */
  function buildAccessLevels(accessLevelDeclarations, userRoles) {

    var accessLevels = {};
    for (var level in accessLevelDeclarations) {

      if (typeof accessLevelDeclarations[level] == 'string') {
        if (accessLevelDeclarations[level] == '*') {

          var resultBitMask = '';

          for (var role in userRoles) {
            resultBitMask += "1"
          }
          //accessLevels[level] = parseInt(resultBitMask, 2);
          accessLevels[level] = {
            bitMask: parseInt(resultBitMask, 2)
          };
        }
        else console.log("Access Control Error: Could not parse '" + accessLevelDeclarations[level] + "' as access definition for level '" + level + "'")

      }
      else {

        var resultBitMask = 0;
        for (var role in accessLevelDeclarations[level]) {
          if (userRoles.hasOwnProperty(accessLevelDeclarations[level][role]))
            resultBitMask = resultBitMask | userRoles[accessLevelDeclarations[level][role]].bitMask
          else console.log("Access Control Error: Could not find role '" + accessLevelDeclarations[level][role] + "' in registered roles while building access for '" + level + "'")
        }
        accessLevels[level] = {
          bitMask: resultBitMask
        };
      }
    }

    return accessLevels;
  }
})();

angular.module('myApp.services')
  .factory('Auth', ['$http', 'User', 'userRoles', function ($http, User, userRoles) {
    return {
      authorize: function (accessLevel, role) {
        if (role === undefined) {
          role = User.getRole();
        }

        return accessLevel.bitMask & role.bitMask;
      },
      isLoggedIn: function (user) {
        if (user === undefined) {
          user = User.current();
        }
        return user.role.title === userRoles.user.title || user.role.title === userRoles.admin.title;
      },
      login: function (user, success, error) {
        $http.post('/login.json', user).success(function (user) {
          User.change(user);
          success(user);
        }).error(error);
      },
      logout: function (success, error) {
        $http.post('/logout.json', User.current()).success(function () {
          User.change({
            token: '',
            username: '',
            role: userRoles.public
          });
          success();
        }).error(error);
      }
    };
  }])
  .factory('User', ['$window', 'userRoles', function($window, userRoles) {
    var currentUser = JSON.parse($window.sessionStorage.user || 'false') ||
      { token: '', username: '', role: userRoles.public };
    return {
      hasToken: function () {
        return currentUser.token.length > 0;
      },
      getToken: function () {
        return currentUser.token;
      },
      getRole: function () {
        return currentUser.role;
      },
      change: function (user) {
        angular.extend(currentUser, user);
        $window.sessionStorage.user = JSON.stringify(currentUser);
      },
      current: function () {
        return currentUser;
      },
      logout: function () {
        delete $window.sessionStorage.user;
      }
    }
  }])
  .factory('authInterceptor', ['$rootScope', '$q', '$location', 'User', function ($rootScope, $q, $location, User) {
    return {
      request: function (config) {
        config.headers = config.headers || {};
        if (User.hasToken()) {
          config.headers.Authorization = 'Bearer ' + User.getToken();
        }
        return config;
      },
      responseError: function (rejection) {
        if (rejection.status === 403 || rejection.status === 401) {
          // handle the case where the user is not authenticated
          User.logout();
          $location.path('/login');
          $rootScope.error = 'You are not authenticated. Please sign in.';
        }
        return $q.reject(rejection);
      }
    };
  }]);

angular.module('myApp.services')
  .factory('Users', function ($http) {
    return {
      getAll: function (success, error) {
        $http.get('/users').success(success).error(error);
      }
    };
  });

angular.module('myApp.services')
  .factory('Stockists', function ($http) {
    return {
      getAll: function (success, error) {
        $http.get('/api/stockists.json').success(success).error(error);
      },
      getOneByName: function (name, success, error) {
        $http.get('/api/stockists/' + encodeURIComponent(name) + '.json').success(success).error(error);
      },
      save: function (data, success, error) {
        error();
      }
    };
  });
