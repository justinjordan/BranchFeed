<?php

if ( isset($_GET['group']) )
{
    
    require_once('../php/Connection.php');
    require_once('../php/LoginSystem.php');
    require_once('../php/GroupSystem.php');

    $db = new Connection();
    $loginSys = new LoginSystem($db);
    $groupSys = new GroupSystem($db);


    $userGroups = $groupSys->GetUserGroups( $loginSys->user['id'] );

    foreach ( $userGroups as $group )
    {
        if ( $group == $_GET['group'] )
        {
            // user is in group
            
            $loginSys->SelectGroup( $_GET['group'] );
            break;
        }
    }
    
}


// Redirect back
header( 'Location: index.php' );