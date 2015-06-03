<?php

require_once('../php/Connection.php');
require_once('../php/LoginSystem.php');

$db = new Connection();
$loginSys = new LoginSystem($db);

$loginSys->Logout();

?>
<!doctype html>
<html>
    <head>
        <title>Logout</title>
        
        <meta charset="utf-8"/>
        <meta http-equiv="refresh" content="2; url=index.php" />
        
        <link rel="stylesheet" type="text/css" href="css/php.css"/>
    </head>
    <body>
        <div><!-- wrapper div -->
            <div id="message">
                You are now logged out!
            </div>
        </div><!-- wrapper div -->
        
    </body>
</html>