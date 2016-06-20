/* 
 * This file is part of the project tutteli/puma published under the Apache License 2.0
 * For the full copyright and license information, please have a look at LICENSE in the
 * root folder or visit https://github.com/robstoll/angular-pre-work
 */

(function(){
'use strict';

angular.module('tutteli.calc', [])
    .directive('calculator', CalculatorDirective);

CalculatorDirective.$inject = ['$parse'];
function CalculatorDirective($parse) {
    return {
        restrict: 'E',
        scope: {
            btnClass: '=',
            field: '=',
            disabled: '='
        },
        template: 
              '<div><div ng-click="press($event, \'7\')" ng-disabled="disabled" class="{{btnClass}}">7</div><div ng-click="press($event, \'8\')" ng-disabled="disabled" class="{{btnClass}}">8</div><div ng-click="press($event, \'9\')" ng-disabled="disabled" class="{{btnClass}}">9</div><div ng-click="press($event, \' + \')" ng-disabled="disabled" class="op {{btnClass}}">+</div></div>'
            + '<div><div ng-click="press($event, \'4\')" ng-disabled="disabled" class="{{btnClass}}">4</div><div ng-click="press($event, \'5\')" ng-disabled="disabled" class="{{btnClass}}">5</div><div ng-click="press($event, \'6\')" ng-disabled="disabled" class="{{btnClass}}">6</div><div ng-click="press($event, \' - \')" ng-disabled="disabled" class="op {{btnClass}}">-</div></div>'
            + '<div><div ng-click="press($event, \'1\')" ng-disabled="disabled" class="{{btnClass}}">1</div><div ng-click="press($event, \'2\')" ng-disabled="disabled" class="{{btnClass}}">2</div><div ng-click="press($event, \'3\')" ng-disabled="disabled" class="{{btnClass}}">3</div><div ng-click="press($event, \' * \')" ng-disabled="disabled" class="op {{btnClass}}">*</div></div>'
            + '<div><div ng-click="press($event, \'0\')" ng-disabled="disabled" class="zero {{btnClass}}">0</div><div ng-click="press($event, \'.\')" ng-disabled="disabled" class="dot op {{btnClass}}">.</div><div ng-click="del($event)" ng-disabled="disabled" class="del op {{btnClass}}">DEL</div></div>',
        controller: ['$scope', function($scope) {
            $scope.press = function($event, text) {
                $event.preventDefault();
                if ($scope.field === undefined || $scope.field === null) {
                    $scope.field = '';
                }
                $scope.field = $scope.field + text;
            };
            
            $scope.del = function($event) {
                $event.preventDefault();
                if ($scope.field !== undefined && $scope.field !== null && $scope.field.length > 0) {
                    var len = $scope.field.length;
                    if ($scope.field.substr(len - 1, 1) == ' ' && len >= 3 && 
                            $scope.field.substr(len - 3, 1) == ' ') {
                        $scope.field = $scope.field.substr(0, len - 3);
                    } else {
                        $scope.field = $scope.field.substr(0, len - 1);
                    }
                    
                }
            };
        }]
    };
}    

})();
