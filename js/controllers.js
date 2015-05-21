(function(){

    const UPDATE_INTERVAL = 5000;
    
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
        $scope.register = function(handle, email, pass1, pass2)
        {
            UserSystem.register({
                handle: handle,
                email: email,
                pass1: pass1,
                pass2: pass2
            })
                .success(function(data, status, headers, config) {
                    
                    if ( data.success )
                    {
                        // Login
                        $scope.login(handle, pass1);
                    }
                    else
                    {
                        // Display Error Message
                        $scope.registerErrorMsg = data.error_msg;
                    }
                    
                });
        }
        
    });
    
    
    /*  Home Controller  */
    appControllers.controller('homeCtrl', function($scope, $location, UserSystem, PostSystem, GroupSystem) {
        
        $scope.updateErrorCount = 0;
        $scope.submitting = false;
        
        // Spinner Statuses
        $scope.postsLoading = true;
        $scope.groupsLoading = true;
        $scope.membersLoading = true;
        
        $scope.clearContent = function() {
            $scope.user = {};
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
                        $scope.groupsLoading = false; // hide spinner
                    })
                .then(function() {
                    
                    //  Get Group Posts
                    PostSystem.getPosts({
                        group_id: $scope.user.selected_group,
                        offset: 0,
                        amount: 20
                    })
                        .success(function(data, status, headers, config) {
                            $scope.posts = data.posts;
                        })
                        .then(function() {
                            // Start Update Loop
                            $scope.updateLoop();
                            $scope.postsLoading = false; // hide spinner
                        });


                    //  Get Group Members
                    GroupSystem.getGroupMembers({
                            group_id: $scope.user.selected_group
                    })
                        .success(function(data, status, headers, config) {
                            $scope.groupMembers = data.members;
                            $scope.membersLoading = false; // hide spinner
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
        
        $scope.addGroup = function() {
            
            GroupSystem.addGroup()
                .success(function(data, status, headers, config) {
                    
                    if ( data.success )
                    {
                        // Append to group list
                        $scope.user.groups.push(data.group);
                        $scope.selectGroup(data.group);
                    }
                    else
                    {
                        console.log("addGroup error:  " + data.error_msg);
                    }
                    
                })
                .error(function(data, status, headers, config) {
                    console.log("addGroup error:  problem retrieveing data file.");
                });
            
        };
        
        $scope.submitPost = function(group_id, content) {
            
            $scope.submitting = true; // prevents fetching update during submit
            
            PostSystem.submitPost({
                group_id: group_id,
                content: content
            })
            .success(function(data, status, headers, config) {
                if ( data.success )
                {
                    $scope.postform.content = null;
                }
            })
            .then(function() {
                $scope.submitting = false;
            });
            
        };
        
        $scope.deletePost = function(post_id) {
            
            PostSystem.deletePost({
                post_id: post_id
            })
                .success(function(data, status, headers, config) {
                    
                    if ( data.success )
                    {
                        // Remove post div from page
                        for ( var i = 0; i < $scope.posts.length; i++ )
                        {
                            if ( $scope.posts[i].id == post_id )
                            {
                                // Remove me
                                $scope.posts.splice(i, 1);
                                break;
                            }
                        }
                    }
                    else
                    {
                        // Couldn't remove post
                        console.log("deletePost error: " + data.error_msg);
                    }
                    
                })
                .error(function(data, status, headers, config) {
                    console.log('deletePost error: problem retrieving data file.');
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
            
            if ( !$scope.submitting )
            {
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
                        console.log("updateLoop error:  " + data.error_msg);
                    }


                })
                .error(function(data, status, headers, config) {

                    if ( $scope.updateErrorCount < 3 )
                    {
                        // Keep a count of errors
                        $scope.updateErrorCount++;
                    }

                })
                .then(function() {

                    // Stop after 3 hours and when logged out
                    if ( $scope.user.id && $scope.updateErrorCount < 3 )
                        setTimeout($scope.updateLoop, UPDATE_INTERVAL);
                });
            }
            
            
        };
        
        $scope.convertDate = function(dateStr) // Returns date to time since string
        {
            var date = new Date(dateStr);
            var now = new Date().getTime();
            
            var sec = Math.floor((now - date) / 1000);
            
            var output = 'just posted';
            
            var tokens = [
                {text: 'year', unit: 31536000},
                {text: 'month', unit: 2628000},
                {text: 'day', unit: 86400},
                {text: 'hour', unit: 3600},
                {text: 'minute', unit: 60},
                {text: 'second', unit: 1}
            ];
            
            if ( sec > 0 )
            {
                for ( var i = 0; i < tokens.length; i++ )
                {
                    if ( sec > tokens[i].unit )
                    {
                        var numOfUnits = Math.floor(sec / tokens[i].unit);

                        output = numOfUnits + ' ' + tokens[i].text + (numOfUnits>1||numOfUnits==0?'s':'') + ' ago';

                        break;
                    }
                }
            }
            
            return output;
        };
        
        
        // Load Content
        $scope.loadContent();
        
        
        
    });
})();







