<?php

require_once('../php/Connection.php');
require_once('../php/LoginSystem.php');
require_once('../php/GroupSystem.php');

$success = true;
$error_msg = '';
$groups = array();

try
{
    // Setup API
    if ( !($db = new Connection()) )
        throw new Exception("Couldn't connect to database!");
    
    if ( !($loginSys = new LoginSystem($db)) )
        throw new Exception("Couldn't connect to login system!");
    
    if ( !($groupSys = new GroupSystem($db)) )
        throw new Exception("Couldn't connect to group system!");
    
    
    // Get user groups
    if ( !($groups = $groupSys->getUserGroups($loginSys->user['id'])) )
        throw new Exception("Couldn't retreive user groups!");
    
}
catch (Exception $e)
{
    $error_msg = $e->getMessage();
    $success = false;
}

echo json_encode(array('success' => $success, 'error_msg' => $error_msg, 'groups' => $groups ));




