(function(){
    
    var app = angular.module('app', [
        'ngRoute',
        'appControllers'
    ])
    
    .config(['$routeProvider', function($routeProvider){
        $routeProvider.

            when('/home', {
                templateUrl: 'html/home.html',
                controller: 'homeCtrl'
            }).
        
            otherwise({
                templateUrl: 'html/login.html',
                controller: 'loginCtrl'
            });
    }]);
    
    
})();