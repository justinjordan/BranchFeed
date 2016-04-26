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

            when('/pswdreset', {
                templateUrl: 'html/pswdreset.html',
                controller: 'pswdResetCtrl'
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

    /*** Setup Helpers Service ***/
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

    /*** Setup UserSystem Factory ***/
    app.factory('UserSystem', function($http) {

        var logout = function()
        {
            return $http.get('data/user_logout.php');
        };

        var getSession = function() {

            return $http.get('data/user_getsession.php');

        };

        var login = function( params )
        {
            return $http({
                method: 'post',
                url: 'data/user_login.php',
                data: {
                    user: params.user,
                    pass: params.pass
                }
            });
        };

        var register = function( params ) // params expects handle, email, pass1, pass2,
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
        };

        var sendPasswordReset = function( email )
        {
            return $http({
                method: 'post',
                url: 'data/user_sendpasswordreset.php',
                data: {
                    email: email
                }
            });
        };

        return {
            logout: logout,
            getSession: getSession,
            login: login,
            register: register,
            sendPasswordReset: sendPasswordReset
        };
    });

    /*** Setup PostSystem ***/
    app.factory('PostSystem', function($http) {

        var posts = [];

        var sortPosts = function( existing_posts, loaded_posts ) // expects post_array, update_array.
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

        var getPosts = function( params ) // Params expected are group_id, offset, amount, and callback
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

        var getComments = function( params )
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

        var submitPost = function( params )
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

        var submitComment = function( params )
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

        var editPost = function( params )
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

        var getCommentsUpdate = function( post )
        {
            var last_loaded = post.comments.length!=0?post.comments[post.comments.length-1].id:0;

            $http({
                method: 'get',
                url: 'data/post_getcommentupdate.php',
                params: {
                    post_id: post.id,
                    last_loaded: last_loaded
                }
            })
            .success(function(data, status, headers, config) {
                if ( data.success )
                {
                    post.comments = post.comments.concat(data.comments);
                    post.commentsUpdatePending = false;
                }
                else
                {
                    console.log("getCommentsUpdate() error:  " + data.error_msg);
                }
            })
            .error(function(data, status, headers, config) {
                console.log("getCommentsUpdate() error:  http error.");
            });
        };

        var getPostUpdate = function( params )
        {
            return $http({
                method: 'get',
                url: 'data/post_getpostupdate.php',
                params: {
                    group_id: params.group_id,
                    oldest_post_id: params.oldest_post_id,
                    last_update: params.last_update
                }
            });
        };

        var deletePost = function( params )
        {
            return $http({
                method: 'post',
                url: 'data/post_deletepost.php',
                data: {
                    post_id: params.post_id
                }
            });
        };

        var countPosts = function( params )
        {
            return $http({
                method: 'get',
                url: 'data/post_countposts.php',
                params: {
                    group_id: params.group_id
                }
            });
        };

        return {
            sortPosts: sortPosts,
            getPosts: getPosts,
            getComments: getComments,
            submitPost: submitPost,
            submitComment: submitComment,
            editPost: editPost,
            getCommentsUpdate: getCommentsUpdate,
            getPostUpdate: getPostUpdate,
            deletePost: deletePost,
            countPosts: countPosts
        };


    });

    /*** Setup GroupSystem Factory ***/
    app.factory('GroupSystem', function($http) {

        var getGroupMembers = function( params )  // Params expected includes group_id
        {
            return $http({
                method: 'get',
                url: 'data/group_getgroupmembers.php',
                params: {
                    group_id: params.group_id
                }
            });
        };

        var getUserGroups = function()
        {
            return $http({
                method: 'get',
                url: 'data/group_getusergroups.php'
            });
        };

        var addGroup = function()
        {
            return $http({
                url: 'data/group_addgroup.php'
            });
        };

        var removeGroup = function( params )
        {
            return $http({
                method: 'get',
                url: 'data/group_removegroup.php',
                params: {
                    group_id: params.group_id
                }
            });
        };

        return {
            getGroupMembers: getGroupMembers,
            getUserGroups: getUserGroups,
            addGroup: addGroup,
            removeGroup: removeGroup
        };

    });


})();
