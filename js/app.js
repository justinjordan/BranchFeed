(function(){
    
    var app = angular.module('app', [
        'ngRoute',
        'appControllers'
    ]);
    
    app.config(function($routeProvider) {
        $routeProvider.

            when('/home', {
                templateUrl: 'html/home.html',
                controller: 'homeCtrl'
            }).
        
            otherwise({
                templateUrl: 'html/login.html',
                controller: 'loginCtrl'
            });
        
            
        
    });
    
    
    /*  Services  */
    
    /*** Setup UserSystem ***/
    app.service('UserSystem', function($http) {
        
        this.logout = function(callback)
        {
            $http.get('data/user_logout.php')
                .success(function(data, status, headers, config) {
                    
                    callback();
                    
                });
        };
        
        this.getSession = function(callback)
        {
            
            $http.get('data/user_getsession.php')
                .success(function(data, status, headers, config) {
                    
                    callback(data);
                    
                })
                .error(function(data, status, headers, config) {
                    
                    callback(false);
                    
                });
            
        };
        
        this.login = function( params, callback )
        {
            $http({
                method: 'post',
                url: 'data/user_login.php',
                headers: {
                    'Content-Type': undefined
                },
                data: {
                    user: params.user,
                    pass: params.pass
                }
            }).
                success(function(data, status, headers, config) {
                    
                    if ( data.success )
                    {
                        /* Logged In */
                        
                        callback(true);
                    }
                    else
                    {
                        /* Access Denied */
                        
                        callback(false);
                    }
                    
                });
        }
        
    });
    
    /*** Setup PostSystem ***/
    app.service('PostSystem', function($http) {
        
        this.getPosts = function( params, callback ) // Params expected are group_id, offset, amount, and callback
        {
            $http({
                method: 'get',
                url: 'data/post_getposts.php',
                params: {
                    group_id: params.group_id,
                    offset: params.offset,
                    amount: params.amount
                }
            })
                .success(function(data, status, headers, config) {
                    
                    if ( data.success )
                        callback(data.posts);
                    else
                        callback(false);
                    
                });
        }
        
        this.submitPost = function( params, callback )
        {
            $http({
                method: 'post',
                url: 'data/post_submitpost.php',
                data: {
                    group_id: params.group_id,
                    content: params.content
                }
            })
                .success(function(data, status, headers, config) {
                    
                    callback(data);
                    
                })
                .error(function(data, status, headers, config) {
                    
                    callback(false);
                    
                });
        }
        
        this.getUpdate = function( params, callback )
        {
            $http({
                method: 'get',
                url: 'data/post_getupdate.php',
                data: {
                    group_id: params.group_id,
                    last_post: params.last_post
                }
            })
                .success(function(data, status, headers, config) {
                    callback(data);
                })
                .error(function(data, status, headers, config) {
                    callback(false);
                });
        };
        
        
    });
    
    /*** Setup GroupSystem ***/
    app.service('GroupSystem', function($http) {
        
        this.getGroupMembers = function( params, callback )  // Params expected includes group_id
        {
            $http({
                method: 'get',
                url: 'data/group_getgroupmembers.php',
                params: {
                    group_id: params.group_id
                }
            })
                .success(function(data, status, headers, config) {
                    
                    if ( data.success )
                        callback(data.members);
                    else
                        callback(false);
                    
                });
        }
        
        this.getUserGroups = function( callback )
        {
            $http({
                method: 'get',
                url: 'data/group_getusergroups.php'
            })
                .success(function(data, status, headers, config) {
                    
                    if ( data.success )
                        callback(data.groups);
                    else
                        callback([]);
                    
                });
        }
        
    });
    
    
})();