(function(){

    var appControllers = angular.module('appControllers', []);

    //appControllers.run();
    
    
    /*  Global Controller  */
    appControllers.controller('globalCtrl', function($scope, $location, UserSystem, PostSystem, GroupSystem) {
        
        /*  Setup Variables  */
        $scope.user = {};
        $scope.userGroups = [];
        $scope.posts = [];
        $scope.groupMembers = [];
        $scope.selected_group = null;
        
        //  Make services available to html
        $scope.logout = function()
        {
            UserSystem.logout(function() {
                
                $scope.user = {};
                $scope.userGroups = [];
                $scope.posts = [];
                $scope.groupMembers = [];
                $scope.selected_group = null;
                
                $location.path('/');
                
            });
        };
        
        $scope.login = function( user, pass )
        {
            UserSystem.login({
                    user: user,
                    pass: pass
                }, function(success) {

                    if ( success )
                    {
                        $scope.init();
                    }
                    else
                    {
                        alert("Couldn't log in!");
                    }

                });
        };
        
        
        
        //  Get Session Data
        $scope.init = function()
        {
            UserSystem.getSession(function(data) {
                
                if ( data.success )
                {
                    $scope.user = data.user;
                    $scope.selected_group = data.user.default_group;

                    //  Get User Groups
                    GroupSystem.getUserGroups(function(data) {

                        $scope.userGroups = data;

                    });

                    //  Get Group Posts
                    PostSystem.getPosts({
                            group_id: $scope.user.default_group,
                            offset: 0,
                            amount: 5
                        },
                        function(data) {
                            if ( data )
                            {
                                $scope.posts = data;
                                
                            }
                        });

                    //  Get Group Members
                    GroupSystem.getGroupMembers({
                            group_id: $scope.user.default_group
                        },
                        function(data) {
                            if ( data )
                            {
                                $scope.groupMembers = data;
                            }
                        });


                    // Goto home
                    $location.path('/home');

                }
                else
                {
                    $location.path('/');
                }

            });
        };
        
        $scope.selectGroup = function(group)
        {
            
            $scope.selected_group = group;
            
            $scope.posts = [];
            $scope.groupMembers = [];
            
            //  Get Group Posts
            PostSystem.getPosts({
                    group_id: $scope.selected_group,
                    offset: 0,
                    amount: 5
                },
                function(data) {
                    if ( data )
                    {
                        $scope.posts = data;
                    }
                });

            //  Get Group Members
            GroupSystem.getGroupMembers({
                    group_id: $scope.selected_group
                },
                function(data) {
                    if ( data )
                    {
                        $scope.groupMembers = data;
                    }
                });
            
        };
        
        $scope.submitPost = function(group_id, content)
        {
            
            PostSystem.submitPost({
                group_id: group_id,
                content: content
            },
            function(data) {
                
                if ( data )
                {
                    if ( data.success )
                        $scope.postform.content = null;
                    else
                        alert(data);
                }
                else
                {
                    alert('HTTP error!');
                }
                
            });
            
        };
        
        $scope.getPostUpdateLoop = function()
        {
            PostSystem.getUpdate({
                group_id: $scope.selected_group,
                last_id: $scope.posts[0].id
            },
            function(data) {
                
                if ( data.success )
                {
                    var tempArray = data.posts.concat($scope.posts);
                    
                    $scope.posts = tempArray;
                    
                    
                }
                
                setTimeout($scope.getPostUpdateLoop, 2000);
                
            });
        };
        
        // Initialize home page if logged in
        $scope.init();
        
    });
    
    
    /*  Login Controller  */
    appControllers.controller('loginCtrl', function($scope, $rootScope, $http, $location) {
        
        /* Initialize Error Message */
        $scope.loginErrorMsg = '';
        
        
        /* Focus on username field */
        document.getElementById('loginUser').focus();
        
        $scope.register = function($user, $pass1, $pass2)
        {
            alert('register');
        }
        
    });
    
    
    /*  Home Controller  */
    appControllers.controller('homeCtrl', function($scope, $location, PostSystem, GroupSystem) {
        
        $scope.init();
        
        
        
    });
})();







