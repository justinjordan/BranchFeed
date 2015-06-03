<?php

require_once('../php/Connection.php');
require_once('../php/LoginSystem.php');


$success = true;
$error_msg = '';
$user = array();

try
{
    if ( !($db = new Connection()) )
        throw new Exception("Couldn't connect to database!");
    
    if ( !($loginSys = new LoginSystem($db)) )
        throw new Exception("Couldn't connect to login system!");
    
    if ( !$loginSys->user )
        throw new Exception("Not logged in!");
    
    $user = array( 'id' => $loginSys->user['id'], 'handle' => $loginSys->user['handle'], 'selected_group' => $loginSys->user['selected_group'], 'default_group' => $loginSys->user['default_group'] );
}
catch (Exception $e)
{
    $error_msg = $e->getMessage();
    $success = false;
}

echo json_encode(array('success' => $success, 'error_msg' => $error_msg, 'user' => $user));