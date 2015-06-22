(function () {
    
    const UPDATE_INTERVAL = 5000;
    
    var appControllers = angular.module('appControllers', []);

    appControllers.run();
    
    
    /*  Global Controller  */
    appControllers.controller('globalCtrl', function($scope, $location, UserSystem, PostSystem, GroupSystem) {
        
        //  Determine if logged in or not
        $scope.getSession = function()
        {
            UserSystem.getSession()
                .success(function(data, status, headers, config) {
                    
                    // Logged in so goto Home
                    if ( data.success )
                    {
                        
                        $scope.user = data.user;
                        
                        // Goto home
                        $location.path('/home');
                    }
                    
                })
                .error(function(data, status, headers, config) {
                    
                    console.log('getSession error:  http error.');
                    
                });
            
        };
        

        
        // Setup Variables
        $scope.initialize = function() {
            $scope.user = {};
            $scope.posts = [];
            $scope.groups = [];
            $scope.groupMembers = [];
            $scope.selected_group = null;
        };
        $scope.initialize();
        
        
        
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
                        
                        console.log('login error:  ' + data.error_msg);
                    }
                })
                .error(function(data, status, headers, config) {
                    
                    console.log('login error: http error.');
                    
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
                    
                })
                .error(function(data, status, headers, config) {
                    
                    console.log('register error:  http error.');
                    
                });
        }
        
    });
    
    /*  Password Reset Controller  */
    appControllers.controller('pswdResetCtrl', function($scope, $location, Helpers, UserSystem) {
        
        $scope.pswdResetSent = false;
        
        $scope.sendPswdReset = function( email ) {
            
            UserSystem.sendPasswordReset(email)
            .success(function(data, status, headers, config) {
                
                if ( !data.success )
                {
                    //console.log("sendPasswordReset() error:  " + data.error_msg);
                    console.log(data);
                    
                    $scope.pswdResetErrorMsg = data.error_msg;
                }
                else
                {
                    $scope.pswdResetSent = true;
                }
                
            })
            .error(function(data, status, headers, config) {
                
                console.log("sendPasswordReset() error:  http error.");
                
            });
            
        };
        
    });
    
    
    /*  Home Controller  */
    appControllers.controller('homeCtrl', function($scope, $location, Helpers, UserSystem, PostSystem, GroupSystem) {
        
        // Constants
        const POSTS_PER_LOAD = 10;
        const COMMENTS_PER_LOAD = 4;
        
        
        // Variables
        $scope.postFormVisible = false;
        $scope.postsUpdatePending = false;
        $scope.postsLoading = true;
        $scope.groupsLoading = true;
        $scope.membersLoading = true;
        $scope.canUpdate = true;
        $scope.totalPosts = 0;
        $scope.updateErrorCount = 0;
        $scope.updateId = 0;
        $scope.lastUpdate = Helpers.getTimeInSeconds();  // get current time in seconds
        
        
        $scope.loadContent = function() {
            
            // Run when user data has loaded
            if ( $scope.user )
            {
                // Clear old content
                $scope.posts = [];
                $scope.groupMembers = [];
                $scope.postsLoading = true;
                $scope.membersLoading = true;
                
                // Get User Groups
                GroupSystem.getUserGroups()
                    .success(function(data, status, headers, config) {
                        $scope.groups = data.groups;
                        $scope.groupsLoading = false; // hide spinner
                        
                        if ( $scope.selected_group == null )
                        {
                            $scope.selected_group = $scope.groups[0];
                        }
                    })
                .then(function() {
                    PostSystem.countPosts({
                        group_id: $scope.selected_group
                    })
                        .success(function(data, status, headers, config) {
                            $scope.totalPosts = data.count;
                        });
                })
                .then(function() {
                    
                    //  Get Group Posts
                    PostSystem.getPosts({
                        group_id: $scope.selected_group,
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
                            group_id: $scope.selected_group
                    })
                        .success(function(data, status, headers, config) {
                            $scope.groupMembers = data.members;
                            $scope.membersLoading = false; // hide spinner
                        });
                    
                });
                
                
                
            }
            
        };
        
        
        
        //  Event Listeners
        
        // Hide active components
        angular.element(document).click(function() {
            
            if ( angular.element(".user-panel_menu").hasClass("visible") )
            {
                //$scope.showUserPanelMenu();
            }
            
        });
        
        
        //  Functions
        
        $scope.showUserPanelMenu = function() {
            
            angular.element(".user-panel_menu").toggleClass("visible");
            
        };
        
        $scope.logout = function() {
            UserSystem.logout()
                .success(function() {

                    $scope.initialize();

                    $location.path('/');

                });
        };
        
        $scope.selectGroup = function(group_id) {
            
            $scope.selected_group = group_id;
            
            Helpers.waitUntilTrue( $scope.user, function() {
                $scope.loadContent();
            });
        };
        
        $scope.removeGroup = function(group_id, index) {
            
            var userConfirm = confirm("Want to remove group " + (index+1) + "?");
            
            if ( userConfirm )
            {
                GroupSystem.removeGroup({
                    group_id: group_id
                })
                    .success(function(data, status, headers, config) {
                        
                        var removedGroup = $scope.groups[index];
                        
                        $scope.groups.splice(index, 1); // remove group from menu
                        
                        if ( $scope.selected_group == removedGroup )
                        {
                            $scope.selectGroup($scope.groups[0]);
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
                        $scope.groups.push(data.group);
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
        
        $scope.showPostForm = function() {
            
            $scope.postFormVisible = true;
            
            setTimeout(function() {
                
                // Delay focus to let DOM catch up
                angular.element("#postFormInput").find("#postFormTextBox").focus();
                
            }, 50);
            
        };
        
        $scope.hidePostForm = function() {
            
            angular.element("#postFormInput").find("#postFormTextBox").text("");
            $scope.postFormVisible = false;
            
        };
        
        $scope.submitPost = function(group_id, content) {
            
            content = Helpers.cleanEditableText(content);
            
            // Call server if content has been submitted
            if ( content != "" )
            {
                $scope.canUpdate = false; // prevents fetching update during submit
                
                PostSystem.submitPost({
                    group_id: group_id,
                    content: content
                })
                .success(function(data, status, headers, config) {
                    if ( !data.success )
                    {
                        console.log('submitPost error:  ' + date.error_msg);
                    }
                    else
                    {
                        // Show Spinner
                        $scope.postsUpdatePending = true;
                    }
                })
                .error(function(data, status, headers, config) {
                    console.log('submitPost error: http error.');
                })
                .then(function() {
                    $scope.canUpdate = true;
                });
            }
            
            $scope.hidePostForm();
        };
        
        $scope.submitComment = function(index) {
            
            var post = $scope.posts[index];
            var post_id = post.id;
            var content = Helpers.cleanEditableText(post.commentsFormContent);
            
            // Call server if content has been submitted
            if ( content != "" )
            {
                $scope.canUpdate = false; // prevents fetching update during submit
                
                PostSystem.submitComment({
                    post_id: post_id,
                    content: content
                })
                .success(function(data, status, headers, config) {
                    
                    if ( !data.success )
                    {
                        console.log('submitComment error:  ' + data.error_msg);
                    }
                    else
                    {
                        post.commentsUpdatePending = true;
                    }
                })
                .error(function(data, status, headers, config) {
                    console.log('submitComment error: http error.');
                })
                .then(function() {
                    $scope.canUpdate = true;
                });
            }
            
            post.commentsFormContent = "";
        };
        
        $scope.showEditForm = function(index)
        {
            var post = $scope.posts[index];
            if ( !post.editMode )
            {
                post.editContent = post.content;
            }
            
            post.editMode = !post.editMode;
            
        };
        
        $scope.editPost = function(index)
        {
            var post = $scope.posts[index];
            var editContent = Helpers.cleanEditableText(post.editContent);
            
            // Call server if content has been edited
            if ( post.content != editContent )
            {
                PostSystem.editPost({
                    post_id: post.id,
                    content: editContent
                })
                .success(function(data, status, headers, config) {

                    if ( !data.success )
                    {
                        console.log('editPost error:  ' + data.error_msg);
                    }

                })
                .error(function(data, status, headers, config) {
                    console.log('editPost error:  http error.');
                });
            }
            
            post.content = editContent;
            post.editMode = false;
        };
        
        $scope.deletePost = function(index) {
            
            var post = $scope.posts[index];
            var post_id = post.id;
            
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
        
        $scope.getComments = function(postIndex) {
            
            var post = $scope.posts[postIndex];
            
            if ( !post.comments )
            {
                post.comments = [];
            }
            
            // show spinner
            post.commentsLoading = true;
            
            PostSystem.getComments({
                post_id: post.id,
                offset: post.comments.length,
                amount: COMMENTS_PER_LOAD
            })
            .success(function(data, status, headers, config) {
                
                if ( data.success )
                {
                    $scope.posts[postIndex].comments = data.comments.concat($scope.posts[postIndex].comments);
                }
                else
                {
                    console.log('getComments error:  ' + data.error_msg);
                }
                
            })
            .error(function(data, status, headers, config) {
                
                console.log('getComments error:  http error.');
                
            })
            .then(function() {
                // Hide spinner
                $scope.posts[postIndex].commentsLoading = false;
            });
            
        };
        
        // Get comments newer than last loaded
        $scope.updateComments = function(postIndex) {
            
            var post = $scope.posts[postIndex];
            
            if ( !post.comments )
            {
                post.comments = [];
            }
            
            // show spinner
            post.commentsLoading = true;
            
            PostSystem.getComments({
                post_id: post.id,
                last_loaded: post.comments[post.comments.length-1].id
            })
            .success(function(data, status, headers, config) {
                
                if ( data.success )
                {
                    $scope.posts[postIndex].comments = $scope.posts[postIndex].comments.concat(data.comments);
                }
                else
                {
                    console.log('getComments error:  ' + data.error_msg);
                }
                
            })
            .error(function(data, status, headers, config) {
                
                console.log('getComments error:  http error.');
                
            })
            .then(function() {
                // Hide spinner
                $scope.posts[postIndex].commentsLoading = false;
            });
            
        };
        
        $scope.showComments = function(index) {
            
            $scope.posts[index].commentsVisible = true;
            
            // Start Loading Comments
            $scope.getComments(index);
            
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
        $scope.getOldestPost = function()
        {
            var lastPost = 0;
            
            if ( $scope.posts.length > 0 )
            {
                var i = $scope.posts.length-1;
                lastPost = $scope.posts[(i<0?0:i)].id;
            }
            
            return lastPost;
        };
        
        $scope.updateLoop = function() {
            
            if ( $scope.canUpdate )
            {
                $scope.canUpdate = false;
                
                // Get new posts
                PostSystem.getPostUpdate({
                    group_id: $scope.selected_group,
                    oldest_post_id: $scope.getOldestPost(),
                    last_update: $scope.lastUpdate
                })
                .success(function(data, status, headers, config) {
                    
                    // Error
                    if ( !data.success )
                    {
                        console.log("updateLoop error:  " + data.error_msg);
                    }
                    
                    // Success
                    else
                    {
                        
                        var sorted = PostSystem.sortPosts( $scope.posts, data.posts );
                        
                        // Update posts on page
                        for ( var i = 0; i < sorted.updated_posts.length; ++i )
                        {
                            var current_update = sorted.updated_posts[i];
                            
                            for ( var j = 0; j < $scope.posts.length; ++j )
                            {
                                var current_post = $scope.posts[j];
                                
                                if ( current_post.id == current_update.id )
                                {
                                    // Update required fields
                                    current_post.content = current_update.content;
                                    current_post.comment_count = current_update.comment_count;
                                }
                                
                            }
                        }
                        
                        // Prepend new posts to page
                        $scope.posts = sorted.new_posts.concat($scope.posts);
                        $scope.totalPosts += sorted.new_posts.length;
                        
                        // Hide Spinner
                        $scope.postsUpdatePending = false;
                    }


                })
                .error(function(data, status, headers, config) {
                    
                    // Keep a count of errors
                    $scope.updateErrorCount++;

                })
                .then(function() {
                    
                    // Update comments
                    for ( var i = 0; i < $scope.posts.length; ++i )
                    {
                        var post = $scope.posts[i];
                        
                        if ( post.commentsVisible )
                        {
                            PostSystem.getCommentsUpdate(post);
                        }
                    }
                    
                    // Log update time
                    $scope.lastUpdate = Helpers.getTimeInSeconds();
                    
                    $scope.canUpdate = true;
                    
                    // Stop after 3 errors and when logged out
                    if ( $scope.user.id && $scope.updateErrorCount < 3 )
                        setTimeout($scope.updateLoop, UPDATE_INTERVAL);
                });
                
                // Get new users
                GroupSystem.getGroupMembers({
                        group_id: $scope.selected_group
                })
                    .success(function(data, status, headers, config) {
                        $scope.groupMembers = data.members;
                    });
                
                
                
            } // if ($scope.canUpdate)
            
            
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
        Helpers.waitUntilTrue( $scope.user, function() {
            $scope.loadContent();
        });
        
        // Start Update Loop
        $scope.updateLoop();
        
        // Infinite Scrolling Listenter
        Helpers.scrollBottomListener(function() {
            if ( $scope.posts.length < $scope.totalPosts && !$scope.postsLoading )
            {
                $scope.postsLoading = true;
                
                PostSystem.getPosts({
                        group_id: $scope.selected_group,
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







