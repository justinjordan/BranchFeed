<?php

require_once('../php/Connection.php');
require_once('../php/LoginSystem.php');
require_once('../php/GroupSystem.php');

$db = new Connection();
$loginSys = new LoginSystem($db);
$groupSys = new GroupSystem($db);



$success = false;

if ( isset($_POST['user'], $_POST['pass']) )
{
    $success = $loginSys->Login($_POST['user'], $_POST['pass']);
}

?>
<!doctype html>
<html>
    <head>
        <title>Login</title>
        
        <meta charset="utf-8"/>
        <meta http-equiv="refresh" content="2; url=index.php" />
        
        <link rel="stylesheet" type="text/css" href="css/php.css"/>
    </head>
    <body>
        <div><!-- wrapper div -->
            <div id="message">
                <?php

if ( $success )
{
    echo '<b>'. $loginSys->user['handle'] .'</b> is now logged in!';
}
else
{
    echo $loginSys->error;
}

?>
            </div>
        </div><!-- wrapper div -->
        
    </body>
</html>