(function(){

    const UPDATE_INTERVAL = 5000;
    
    var appControllers = angular.module('appControllers', []);

    appControllers.run();
    
    
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
        
        // Constants
        const POSTS_PER_LOAD = 10;
        
        
        // Variables
        $scope.postsLoading = true;
        $scope.groupsLoading = true;
        $scope.membersLoading = true;
        $scope.canUpdate = true;
        $scope.totalPosts = 0;
        $scope.updateErrorCount = 0;
        $scope.updateId = 0;
        
        
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
                    PostSystem.countPosts({
                        group_id: $scope.user.selected_group
                    })
                        .success(function(data, status, headers, config) {
                            $scope.totalPosts = data.count;
                        });
                })
                .then(function() {
                    
                    //  Get Group Posts
                    PostSystem.getPosts({
                        group_id: $scope.user.selected_group,
                        offset: 0,
                        amount: POSTS_PER_LOAD
                    })
                        .success(function(data, status, headers, config) {
                            $scope.posts = data.posts;
                        })
                        .then(function() {
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
                setTimeout( $scope.loadContent, UPDATE_INTERVAL );
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
        
        $scope.selectGroup = function(group_id) {
            
            UserSystem.selectGroup({
                group_id: group_id
            })
                .success(function(data, status, headers, config) {
                    if ( !data.success )
                    {
                        console.log("selectGroup error:  " + data.error_msg);
                    }
                })
                .error(function(data, status, headers, config) {
                    console.log("selectGroup error:  http error.");
                });
            
            $scope.user.selected_group = group_id;
            $scope.loadContent();
            
            
        };
        
        $scope.removeGroup = function(group_id, index) {
            
            var userConfirm = confirm("Want to remove group " + (index+1) + "?");
            
            if ( userConfirm )
            {
                GroupSystem.removeGroup({
                    group_id: group_id
                })
                    .success(function(data, status, headers, config) {
                        
                        var removedGroup = $scope.user.groups[index];
                        
                        $scope.user.groups.splice(index, 1); // remove group from menu
                        
                        if ( $scope.user.selected_group == removedGroup )
                        {
                            $scope.selectGroup($scope.user.groups[0]);
                        }
                        
                    })
                    .error(function(data, status, headers, config) {
                        console.log("removeGroup error:  http error.");
                    });
            }
            
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
            
            $scope.canUpdate = false; // prevents fetching update during submit
            
            PostSystem.submitPost({
                group_id: group_id,
                content: content
            })
            .success(function(data, status, headers, config) {
                if ( data.success )
                {
                    $scope.postform.content = null; // reset post form
                }
            })
            .then(function() {
                $scope.canUpdate = true;
            });
            
        };
        
        $scope.deletePost = function(post_id) {
            
            var confirmed = confirm("Want to delete?");
            
            if ( confirmed )
                
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
                                    
                                    $scope.posts.splice(i, 1); // Remove post from array
                                    $scope.totalPosts--; // decrement totalPost count
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
            
            if ( $scope.canUpdate )
            {
                $scope.canUpdate = false;
                
                PostSystem.getUpdate({
                    group_id: $scope.user.selected_group,
                    last_post: $scope.getFirstPost()
                })
                .success(function(data, status, headers, config) {
                    if ( data.success )
                    {
                        $scope.posts = data.posts.concat($scope.posts); // Append new posts
                        $scope.totalPosts += data.posts.length; // Count new posts
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
                    
                    $scope.canUpdate = true;
                    
                    // Stop after 3 errors and when logged out
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
        $scope.updateLoop();
        
        // Infinite Scrolling
        PostSystem.scrollBottomListener(function() {
            if ( $scope.posts.length < $scope.totalPosts && !$scope.postsLoading )
            {
                $scope.postsLoading = true;
                
                PostSystem.getPosts({
                        group_id: $scope.user.selected_group,
                        offset: $scope.posts.length,
                        amount: POSTS_PER_LOAD
                    })
                        .success(function(data, status, headers, config) {
                            $scope.posts = $scope.posts.concat(data.posts);
                        })
                        .then(function() {
                            $scope.postsLoading = false;
                        });
            }
        });
        
        
    });
})();







