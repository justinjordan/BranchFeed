<?php

require_once('../php/Connection.php');
require_once('../php/LoginSystem.php');
require_once('../php/GroupSystem.php');
require_once('../php/PostSystem.php');

require_once('../php/Time.php');

$db = new Connection();
$loginSys = new LoginSystem($db);
$groupSys = new GroupSystem($db);
$postSys = new PostSystem($db);

// Not logged in
if ( !$loginSys->user ):

?>

<!doctype html>
<html>
    <head>
        <meta http-equiv="refresh" content="0; url=/"/>
    </head>
    <body></body>
</html>

<?php

// Logged in
else:

// Get Associated groups
$groups = $groupSys->GetUserGroups( $loginSys->user['id'] );

// Get Group Members
$members = $loginSys->GetUsers( $groupSys->GetMembers($loginSys->group_id) );

// Get group posts
$posts = $postSys->GetPosts( $loginSys->group_id );

?>
<!doctype html>
<html>
    <head>
        <title>BranchFeed</title>
        
        <meta charset="utf-8"/>
        
        <link rel="stylesheet" type="text/css" href="css/home.css"/>
    </head>
    <body>
        
        <div id="wrapper">
            
            <div id="topPanel">
                <div id="logo"></div>
                <div id="userPanel">
                    <ul>
                        <li class="userHandle"><?php echo $loginSys->user['handle']; ?></li>
                        <li class="userNavItem"><a href="logout.php">sign out</a></li>
                    </ul>
                </div><!-- end #userPanel -->
            </div><!-- end #topPanel -->
            
            <!-- Group List -->
            <div id="leftPanel">
                
                <div id="groupPanel">
                
                    <?php foreach ( $groups as $group ): ?>

                    <?php if ( $group != $loginSys->group_id ): ?>
                    <a href="selectgroup.php?group=<?php echo $group; ?>">
                        <div class="groupButton">
                            <?php echo $group; ?>
                        </div><!-- .groupButton -->
                    </a>
                    <?php else: ?>
                    <div class="groupButton gb_selected">
                        <?php echo $group; ?>
                    </div><!-- .groupButton -->
                    <?php endif; ?>

                    <?php endforeach; ?>
                    
                    <a href="newgroup.php">
                        <div id="groupAddButton">
                        </div><!-- #groupAddButton -->
                    </a>
                    
                </div><!-- end #groupPanel -->
                
            </div><!-- end #leftPanel -->
            
            <!-- Member List -->
            <div id="rightPanel">
                
                <div id="memberPanel">
                    <ul>
                    
                        <?php

                        if ( $members )
                            echo '<li class="header">Group Members</li>';

                            foreach( $members as $member )
                            {
                                echo '<li>'. $member['handle'] .'</li>';
                            }

                        ?>
                        
                    </ul>
                </div><!-- end #memberPanel -->
                
            </div><!-- end #rightPanel -->
            
            <div id="mainContainer">
                <div id="content">
                    
                    <div id="postForm" class="post">
                        <form method="post" action="post.php">
                            <div id="postFormInput">
                                <textarea name="content"></textarea>
                            </div>
                            <div id="postFormAction">
                                <input type="submit" value="Post"/>
                            </div>
                        </form>
                    </div><!-- #postForm -->
                    
                    <?php if ( count($posts) == 0 ): ?>
                    
                    <div class="alert">No messages exist for this group.</div>
                    
                    <?php else: foreach ( $posts as $post ): ?>
                    
                    <div class="post">
                        <?php if ($post['user_id']==$loginSys->user['id']): ?>
                        <div class="postActions"><a href="removepost.php?id=<?php echo $post['id']; ?>">X</a></div>
                        <?php endif; ?>
                        <h2 class="postName"><?php echo $post['user_handle']; ?></h2>
                        <aside class="postDate"><?php echo Time::FormatDate($post['date']); ?></aside>
                        <p><?php echo $post['content']; ?></p>
                    </div>
                    
                    <?php endforeach; endif; ?>
                    
                    
                </div><!-- end #content -->
            </div><!-- end #container -->
            
        </div><!-- end #wrapper -->
        
    </body>
</html>
<?php

endif;

?>