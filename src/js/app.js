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
    
    
    /*  Directives  */
    
    app.directive("contenteditable", function() {
        return {
            restrict: "A",
            require: "ngModel",
            link: function(scope, element, attrs, ngModel) {

                function read() {
                    ngModel.$setViewValue(element.html());
                }

                ngModel.$render = function() {
                    element.html(ngModel.$viewValue || "");
                };

                element.bind("blur keyup change", function() {
                    scope.$apply(read);
                });
            }
        };
    });
    
    
    /*  Services  */
    
    /*** Setup Helpers ***/
    app.service('Helpers', function() {
        
        this.cleanEditableText = function(text) {
            var find = null;
            var re = null;
            
            // Replace br tags
            find = '<br>';
            re = new RegExp(find, 'g');
            text = text.replace(re, "");
            
            // Replace div openings
            find = '<div>';
            re = new RegExp(find, 'g');
            text = text.replace(find, "\n");  // replace first div tag with new line
            text = text.replace(re, "");
            
            // Replace div closures
            find = '</div>';
            re = new RegExp(find, 'g');
            text = text.replace(re, "\n"); // replace div closures with newline character
            
            // Replace &nbsp; tags
            find = '&nbsp;';
            re = new RegExp(find, 'g');
            text = text.replace(re, " ");
            
            // Remove leading newline characters
            while ( text.charAt(0) == "\n" )
            {
                text = text.substr(1, text.length-1);
            }
            // Remove following newline characters
            while ( text.charAt(text.length-1) == "\n" )
            {
                text = text.substr(0, text.length-1);
            }
            
            
            // Remove any remain html
            text = text.replace(/(<([^>]+)>)/ig, "");
            
            return text;
        };
        
        this.getTimeInSeconds = function() {
            
            return Math.floor((new Date()).getTime()/1000);
            
        };
        
        this.waitUntilTrue = function( condition, callback ) {
            
            var loop = true;
            
            while( loop )
            {
                if ( condition )
                {
                    callback();
                    loop = false;
                }
            }
            
        };
        
        this.scrollBottomListener = function( callback )
        {
            setInterval(function() {
                var winHeight = angular.element(window).height();
                var docHeight = angular.element(document).height();
                var scrollPos = angular.element(window).scrollTop();
                var padding = 160;

                if ( docHeight - scrollPos <= winHeight + padding )
                {
                    callback();
                }
                
            }, 1000);
        };
        
    });
    
    /*** Setup UserSystem ***/
    app.service('UserSystem', function($http) {
        
        this.logout = function()
        {
            return $http.get('data/user_logout.php');
        };
        
        this.getSession = function() {
            
            return $http.get('data/user_getsession.php');
            
        };
        
        this.login = function( params )
        {
            return $http({
                method: 'post',
                url: 'data/user_login.php',
                data: {
                    user: params.user,
                    pass: params.pass
                }
            });
        }
        
        this.register = function( params ) // params expects handle, email, pass1, pass2,
        {
            return $http({
                method: 'post',
                url: 'data/user_register.php',
                data: {
                    handle: params.handle,
                    email: params.email,
                    pass1: params.pass1,
                    pass2: params.pass2
                }
            });
        }
    });
    
    /*** Setup PostSystem ***/
    app.service('PostSystem', function($http) {
        
        this.sortPosts = function( existing_posts, loaded_posts ) // expects post_array, update_array.
        {
            var new_posts = [];
            var updated_posts = [];
            
            for ( var l = 0; l < loaded_posts.length; ++l )
            {
                var foundInExisting = false;
                
                for ( var e = 0; e < existing_posts.length; ++e )
                {
                    if ( loaded_posts[l].id == existing_posts[e].id )
                    {
                        foundInExisting = true;
                        
                        break;
                    }
                }
                
                if ( foundInExisting )
                {
                    updated_posts.push( loaded_posts[l] );
                }
                else
                {
                    new_posts.push( loaded_posts[l] );
                }
            }
            
            return {
                new_posts: new_posts,
                updated_posts: updated_posts
            };
        };
        
        this.getPosts = function( params ) // Params expected are group_id, offset, amount, and callback
        {
            return $http({
                method: 'get',
                url: 'data/post_getposts.php',
                params: {
                    group_id: params.group_id,
                    offset: params.offset,
                    amount: params.amount
                }
            });
        };
        
        this.getComments = function( params )
        {
            return $http({
                method: 'get',
                url: 'data/post_getcomments.php',
                params: {
                    post_id: params.post_id,
                    offset: params.offset,
                    amount: params.amount
                }
            });
        };
        
        this.submitPost = function( params )
        {
            return $http({
                method: 'post',
                url: 'data/post_submitpost.php',
                data: {
                    group_id: params.group_id,
                    content: params.content
                }
            });
        };
        
        this.submitComment = function( params )
        {
            return $http({
                method: 'post',
                url: 'data/post_submitcomment.php',
                data: {
                    post_id: params.post_id,
                    content: params.content
                }
            });
        };
        
        this.editPost = function( params )
        {
            return $http({
                method: 'post',
                url: 'data/post_editpost.php',
                data: {
                    post_id: params.post_id,
                    content: params.content
                }
            });
        };
        
        this.getCommentUpdate = function( params )
        {
            return $http({
                method: 'get',
                url: 'data/post_getcommentupdate.php',
                params: {
                    post_id: params.post_id,
                    last_loaded: params.last_loaded
                }
            });
        };
        
        this.getPostUpdate = function( params )
        {
            return $http({
                method: 'get',
                url: 'data/post_getpostupdate.php',
                params: {
                    group_id: params.group_id,
                    last_post: params.last_post
                }
            });
        };
        
        this.deletePost = function( params )
        {
            return $http({
                method: 'post',
                url: 'data/post_deletepost.php',
                data: {
                    post_id: params.post_id
                }
            });
        };
        
        this.countPosts = function( params )
        {
            return $http({
                method: 'get',
                url: 'data/post_countposts.php',
                params: {
                    group_id: params.group_id
                }
            });
        };
        
        
    });
    
    /*** Setup GroupSystem ***/
    app.service('GroupSystem', function($http) {
        
        this.getGroupMembers = function( params )  // Params expected includes group_id
        {
            return $http({
                method: 'get',
                url: 'data/group_getgroupmembers.php',
                params: {
                    group_id: params.group_id
                }
            });
        }
        
        this.getUserGroups = function()
        {
            return $http({
                method: 'get',
                url: 'data/group_getusergroups.php'
            });
        }
        
        this.addGroup = function()
        {
            return $http({
                url: 'data/group_addgroup.php'
            });
        }
        
        this.removeGroup = function( params )
        {
            return $http({
                method: 'get',
                url: 'data/group_removegroup.php',
                params: {
                    group_id: params.group_id
                }
            });
        }
        
    });
    
    
})();