'use strict';

/* Directives */


angular.module('myApp.directives', []).
  directive('appVersion', ['version', function (version) {
    return function (scope, elm, attrs) {
      elm.text(version);
    };
  }])
  .directive('accessLevel', ['Auth', 'User', function (Auth, User) {
    return {
      restrict: 'A',
      link: function ($scope, element, attrs) {
        var prevDisp = element.css('display')
          , userRole
          , accessLevel;

        $scope.user = User.current();
        $scope.$watch('user', function (user) {
          if (user.role)
            userRole = user.role;
          updateCSS();
        }, true);

        attrs.$observe('accessLevel', function (al) {
          if (al) accessLevel = $scope.$eval(al);
          updateCSS();
        });

        function updateCSS() {
          if (userRole && accessLevel) {
            if (!Auth.authorize(accessLevel, userRole))
              element.css('display', 'none');
            else
              element.css('display', prevDisp);
          }
        }
      }
    };
  }])
  .directive('activeNav', ['$location', function ($location) {
    return {
      restrict: 'A',
      link: function (scope, element, attrs) {
        var nestedA = element.find('a')[0];
        var path = nestedA.href;

        scope.location = $location;
        scope.$watch('location.absUrl()', function (newPath) {
          if (path === newPath) {
            element.addClass('active');
          } else {
            element.removeClass('active');
          }
        });
      }

    };

  }]);
