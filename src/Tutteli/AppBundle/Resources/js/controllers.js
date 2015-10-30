/* 
 * This file is part of the project tutteli-purchase published under the Apache License 2.0
 * For the full copyright and license information, please have a look at LICENSE in the
 * root folder or visit https://github.com/robstoll/purchase
 */
'use strict';

angular.module('tutteli.ctrls', ['tutteli.preWork', 'tutteli.auth'])
  .controller('tutteli.LoginCtrl', 
    ['$scope', '$rootScope', '$cookies', 
     'tutteli.PreWork','tutteli.auth.AuthService', 'tutteli.auth.EVENTS',
    function ($scope, $rootScope, $cookies, 
            PreWork, AuthService, AUTH_EVENTS) {
    
        $scope.credentials = {
                username: '',
                password: '',
                csrf_token: ''
        };
        
        PreWork.merge('login.tpl', $scope);        
        
        $scope.login = function (credentials, $event) {
            $event.preventDefault();
            AuthService.login(credentials).then(function(response) {
                //TODO redirect
            }, function(response){
                //TODO show error message to user
            });
        };
    }
]).controller('tutteli.LoginModalCtrl',  
  ['$scope', 'tutteli.auth.AuthService', 
  function ($scope, AuthService) {

    this.cancel = $scope.$dismiss;

    this.submit = function (credentials) {
      AuthService.login(credentials).then(function (user) {
        $scope.$close(user);
      }, function() {
          
      });
    };
  }
]);