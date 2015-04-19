(function(){

    var appControllers = angular.module('appControllers', []);

    appControllers.run(function($rootScope, $http){
    });
    
    /*  Global Controller  */
    appControllers.controller('globalCtrl', function($scope, $rootScope) {
        
        $rootScope.user = {
            logged_in: false
        };
        
    });
    
    
    /*  Login Controller  */
    appControllers.controller('loginCtrl', function($scope, $rootScope, $http, $location) {
        
        /* Initialize Error Message */
        $scope.loginErrorMsg = '';
        
        
        /* Focus on username field */
        document.getElementById('loginUser').focus();
        
        
        $scope.login = function(user, pass)
        {
            $http({
                method: 'post',
                url: 'data/login.php',
                headers: {
                    'Content-Type': undefined
                },
                data: {
                    user: user,
                    pass: pass
                }
            }).
                success(function(data, status, headers, config) {
                    
                    if ( data.success )
                    {
                        /* Logged In */
                        
                        $rootScope.user.logged_in = true;
                        
                        $location.path('/home');
                    }
                    else
                    {
                        /* Access Denied */
                        
                        $scope.loginErrorMsg = data.error_msg;
                    }
                    
                }).
                error(function(data, status, headers, config) {
                    
                    /* ERROR */
                    
                });
        }
        
        $scope.register = function($user, $pass1, $pass2)
        {
            alert('register');
        }
        
    });
    
    
    /*  Home Controller  */
    appControllers.controller('homeCtrl', function($scope, $rootScope) {
        
    });
})();