<?php

require_once('../php/Connection.php');
require_once('../php/LoginSystem.php');
require_once('../php/PostSystem.php');

$db = new Connection();
$loginSys = new LoginSystem($db);
$postSys = new PostSystem($db);


$success = false;

if ( isset($_POST['content']) && $loginSys->user )
{
    $success = $postSys->NewPost( $loginSys->user['id'], $loginSys->group_id, $_POST['content'] );
}

?>
<!doctype html>
<html>
    <head>
        <title>Submit Post</title>
        
        <meta charset="utf-8"/>
        <meta http-equiv="refresh" content="2; url=index.php" />
        
        <link rel="stylesheet" type="text/css" href="css/php.css"/>
    </head>
    <body>
        <div><!-- wrapper div -->
            <div id="message">
                <?php if ( $success ): ?>
                
                Post submitted!
                
                <?php else: ?>
                
                Unable to submit post!
                
                <?php endif; ?>
            </div>
        </div><!-- wrapper div -->
        
    </body>
</html>