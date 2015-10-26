/* 
 * This file is part of the project tutteli/purchase published under the Apache License 2.0
 * For the full copyright and license information, please have a look at LICENSE in the
 * root folder or visit https://github.com/robstoll/purchase
 */

var tutteliPurchaseCtrls = angular.module('tutteli-purchase.ctrls', []);

tutteliPurchaseCtrls.controller('tutteliLoginModalCtrl', function ($scope, tutteliLoginApi) {

    this.cancel = $scope.$dismiss;

    this.submit = function (username, password) {
      tutteliLoginApi.login(username, password).then(function (user) {
        $scope.$close(user);
      });
    };

});