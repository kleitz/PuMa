/* 
 * This file is part of the project tutteli/puma published under the Apache License 2.0
 * For the full copyright and license information, please have a look at LICENSE in the
 * root folder or visit https://github.com/robstoll/PuMa
 */
(function(){
'use strict';

function getCurrentMonth() {
    var month = new Date().getMonth() + 1;
    if (month <= 9) {
        month = '0' + month;
    }
    return month;
}

function getCurrentYear() {
    return new Date().getFullYear();
}

angular.module('tutteli.puma.routing', [
    'ui.router', 
    'tutteli.auth.routing', 
    'tutteli.login',
    'tutteli.logout',
    'tutteli.puma.core'
]).config(
  ['$locationProvider','$stateProvider', 'tutteli.auth.USER_ROLES',
  function($locationProvider, $stateProvider,  USER_ROLES) {
      
    $locationProvider.html5Mode(true);
    $stateProvider.state('home', {
        url: '/',
        redirectTo: 'new_puma'
    })
    
    .state('login', {
        url: '/login',
        controller: 'tutteli.LoginController',
        controllerAs: 'loginCtrl',
        templateUrl: 'login.tpl',
    }).state('logout', {
        url: '/logout',
        controller: 'tutteli.LogoutController'
    })
    
    .state('pumas', {
        url: '/pumas',
        redirectTo: 'pumas_month',
    })
    .state('pumas_currentMonth', {
        url: '/pumas/month',
        redirectTo: 'pumas_monthAndYear',
        redirectParams: {month: getCurrentMonth(), year: getCurrentYear()}            
    })
    .state('pumas_monthAndYear', {
        url: '/pumas/month-:month-:year',
        controller: 'tutteli.puma.PurchasesMonthController',
        controllerAs: 'pumasCtrl',
        templateUrl: 'pumas/month.tpl',
        data: {
            authRoles: [USER_ROLES.authenticated]
        }
    })
    .state('new_puma', {
        url: '/pumas/new',
        controller: 'tutteli.puma.NewPurchaseController',
        controllerAs: 'pumaCtrl',
        templateUrl: 'pumas/new.tpl',
        data: {
            authRoles: [USER_ROLES.authenticated]
        }
    })
    .state('edit_puma', {
        url: '/pumas/:pumaId/edit',
        controller: 'tutteli.puma.EditPurchaseController',
        controllerAs: 'pumaCtrl',
        templateUrl: 'pumas/edit.tpl',
        data: {
            authRoles: [USER_ROLES.authenticated]
        }
    })
    
    .state('users', {
        url: '/users',
        controller: 'tutteli.puma.UsersController',
        controllerAs: 'usersCtrl',
        templateUrl : 'users.tpl',
        data: {
            authRoles : [USER_ROLES.admin] 
        }
    }).state('new_user', {
        url: '/users/new',
        controller: 'tutteli.puma.NewUserController',
        controllerAs: 'userCtrl',
        templateUrl: 'users/new.tpl',
        data: {
            authRoles: [USER_ROLES.admin]
        }
    }).state('edit_user', {
        url : '/users/:userId/edit',
        controller: 'tutteli.puma.EditUserController',
        controllerAs: 'userCtrl',
        templateUrl: 'users/edit.tpl',
        data: {
            authRoles: [USER_ROLES.admin],
            userIdParamName: 'userId'
        }
    }).state('edit_user_password', {
        url: '/users/:userId/change-password',
        controller: 'tutteli.puma.ChangePasswordController',
        controllerAs: 'passwordCtrl',
        templateUrl: 'users/change-password.tpl',
        data: {
            //force that only current user is allowed
            authRoles: [USER_ROLES.noOne], 
            userIdParamName: 'userId'
        }
    })
    .state('reset_user_password', {
        url: '/users/:userId/reset-password',
        controller: 'tutteli.puma.ResetPasswordController',
        controllerAs: 'passwordCtrl',
        templateUrl: 'users/reset-password.tpl',
        data: {
            authRoles: [USER_ROLES.admin]
        }
    })
    
    .state('categories', {
        url: '/categories',
        controller: 'tutteli.puma.CategoriesController',
        controllerAs: 'categoriesCtrl',
        templateUrl : 'categories.tpl',
        data: {
            authRoles : [USER_ROLES.admin] 
        }
    }).state('new_category', {
        url: '/categories/new',
        controller: 'tutteli.puma.NewCategoryController',
        controllerAs: 'categoryCtrl',
        templateUrl: 'categories/new.tpl',
        data: {
            authRoles: [USER_ROLES.admin]
        }
    }).state('edit_category', {
        url : '/categories/:categoryId/edit',
        controller: 'tutteli.puma.EditCategoryController',
        controllerAs: 'categoryCtrl',
        templateUrl: 'categories/edit.tpl',
        data: {
            authRoles: [USER_ROLES.admin]
        }
    })
    
    .state('bills_currentYear', {
        url: '/accounting/bills',
        redirectTo: 'bills_year',
        redirectParams:  {year: getCurrentYear()}    
    })
    .state('bills_year', {
        url: '/accounting/bills-:year',
        controller: 'tutteli.puma.BillsController',
        controllerAs: 'billsCtrl',
        templateUrl: 'accounting/bills.tpl',
        data: {
            authRoles: [USER_ROLES.authenticated]
        }
    });
  }
]).run(['$rootScope', '$state', function($rootScope, $state) {
    $rootScope.$on('$stateChangeStart', function(evt, toState, params) {
      if (toState.redirectTo) {
        evt.preventDefault();
        var newParams = params;
        if (toState.redirectParams) {
            for(var prop in toState.redirectParams) {
                newParams[prop] = toState.redirectParams[prop];
            }
        }
        $state.go(toState.redirectTo, newParams);
      }
    });
}]).constant('tutteli.puma.ROUTES', {
    get_login_csrf: 'login/token',
    get_pumas_monthAndYear_json: 'pumas/month-:month-:year.json',
    get_puma_json: 'pumas/:pumaId.json',
    post_puma : 'pumas',
    put_puma : 'pumas/:pumaId',
    get_puma_csrf: 'pumas/new/token',    
    get_categories_json: 'categories.json',
    get_category_json: 'categories/:categoryId.json',
    get_category_csrf: 'categories/new/token',
    post_category: 'categories',
    put_category: 'categories/:categoryId',
    get_users_json: 'users.json',
    get_user_json: 'users/:userId.json',
    get_user_csrf: 'users/new/token',
    post_user: 'users',
    put_user: 'users/:userId',
    put_user_password: 'users/:userId/change-password',
    put_reset_user_password: 'users/:userId/reset-password',
    get_bills_year_json: 'accounting/bills-:year.json',
});

})();