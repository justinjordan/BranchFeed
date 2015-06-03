<?php

require_once('../php/Connection.php');
require_once('../php/LoginSystem.php');
require_once('../php/GroupSystem.php');

$db = new Connection();
$loginSys = new LoginSystem($db);
$groupSys = new GroupSystem($db);


$success = false;

if ( isset($_POST['user'], $_POST['pass1'], $_POST['pass2'], $_POST['email'], $_POST['name'], $_POST['location'], $_POST['birthdate']) )
{
    // Register New User
    $success = $loginSys->Register( $_POST['user'], $_POST['pass1'], $_POST['pass2'], $_POST['email'], $_POST['name'], $_POST['location'], $_POST['birthdate'] );

    // Login
    if ( $success )
    {
        // Login
        $loginSys->Login( $_POST['user'], $_POST['pass1'] );
        
        // Find group
        if ( $openGroups = $groupSys->FindGroup( $loginSys->user['id'] ) )
        {
            if ( $groupSys->AddToGroup($openGroups[0], $loginSys->user['id']) )
            {
                $loginSys->SetDefaultGroup($openGroups[0]);
                $loginSys->SelectGroup($openGroups[0]);
            }
        }
        
    }
}

?>
<!doctype html>
<html>
    <head>
        <title>Register</title>
        
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
    echo $loginSys->user['handle'] .' is now registered.';
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