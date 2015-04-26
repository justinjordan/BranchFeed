(function(){

    var appControllers = angular.module('appControllers', []);

    //appControllers.run();
    
    
    /*  Global Controller  */
    appControllers.controller('globalCtrl', function($scope, $location, UserSystem, PostSystem, GroupSystem) {
        
        //  Get Session Data
        $scope.getSession = function()
        {
            UserSystem.getSession()
                .success(function(data, status, headers, config) {
                    
                    if ( data.success )
                    {
                        $scope.user = data.user;
                        $scope.user.selected_group = data.user.default_group;
                        
                        // Goto home
                        $location.path('/home');
                    }
                    else
                    {
                        $location.path('/');
                    }
                    
                });
            
        };
        

        
        // Setup Variables
        $scope.user = {};
        $scope.posts = [];
        $scope.groupMembers = [];
        
        
        
        // Check for session
        $scope.getSession();
        
    });
    
    
    /*  Login Controller  */
    appControllers.controller('loginCtrl', function($scope, $location, UserSystem) {
        
        /* Initialize Error Message */
        $scope.loginErrorMsg = '';
        
        
        /* Focus on username field */
        document.getElementById('loginUser').focus();
        
        // Login user
        $scope.login = function( user, pass )
        {
            UserSystem.login({
                user: user,
                pass: pass
            })
                .success(function(data, status, headers, config) {
                    
                    if ( data.success )
                    {
                        // Goto Home
                        $scope.getSession();
                    }
                    else
                    {
                        // Display Error Message
                        $scope.loginErrorMsg = data.error_msg;
                    }
                });
        };
        
        // Register user
        $scope.register = function($user, $pass1, $pass2)
        {
            console.log('register');
        }
        
    });
    
    
    /*  Home Controller  */
    appControllers.controller('homeCtrl', function($scope, $location, UserSystem, PostSystem, GroupSystem) {
        
        $scope.clearContent = function() {
            $scope.posts = [];
            $scope.groupMembers = [];
        };
        
        
        $scope.loadContent = function() {
            
            if ( $scope.user )
            {
                // Get User Groups
                GroupSystem.getUserGroups()
                    .success(function(data, status, headers, config) {
                        $scope.user.groups = data.groups;
                    })
                .then(function() {
                    
                    //  Get Group Posts
                    PostSystem.getPosts({
                        group_id: $scope.user.selected_group,
                        offset: 0,
                        amount: 5
                    })
                    .success(function(data, status, headers, config) {
                        $scope.posts = data.posts;
                    })
                    .then(function() {
                        // Start Update Loop
                        $scope.updateLoop();
                    });


                    //  Get Group Members
                    GroupSystem.getGroupMembers({
                            group_id: $scope.user.selected_group
                    })
                        .success(function(data, status, headers, config) {
                            $scope.groupMembers = data.members;
                        });
                    
                });
                
                
                
            }
            else
            {
                setTimeout( $scope.loadContent, 1000 );
            }
            
        };
        
        
        
        
        //  Functions
        
        $scope.logout = function() {
            UserSystem.logout()
                .success(function() {

                    $scope.clearContent();

                    $location.path('/');

                });
        };
        
        $scope.selectGroup = function(group) {
            
            $scope.user.selected_group = group;
            
            $scope.loadContent();
            
        };
        
        $scope.submitPost = function(group_id, content) {
            
            PostSystem.submitPost({
                group_id: group_id,
                content: content
            })
            .success(function(data, status, headers, config) {
                if ( data.success )
                {
                    $scope.postform.content = null;
                }
            });
            
        };
        
        $scope.getFirstPost = function()
        {
            var firstPost = 0;
            
            if ( $scope.posts.length > 0 )
            {
                firstPost = $scope.posts[0].id;
            }
            
            return firstPost;
        };
        
        $scope.updateLoop = function() {
            
            PostSystem.getUpdate({
                group_id: $scope.user.selected_group,
                last_post: $scope.getFirstPost()
            })
            .success(function(data, status, headers, config) {
                if ( data.success )
                {
                    $scope.posts = data.posts.concat($scope.posts);
                }
                else
                {
                    console.log(data.error_msg);
                }
                
                setTimeout($scope.updateLoop, 2000);
            });
            
            
        };
        
        
        
        
        // Load Content
        $scope.loadContent();
        
        
        
    });
})();







