<?php

require_once('../php/Connection.php');
require_once('../php/LoginSystem.php');
require_once('../php/GroupSystem.php');


$success = true;
$error_msg = '';

try
{
    if ( !($db = new Connection()) )
        throw new Exception("Couldn't connect to database!");
    
    if ( !($loginSys = new LoginSystem($db)) )
        throw new Exception("Couldn't connect to login system!");
    
    if ( !($groupSys = new GroupSystem($db)) )
        throw new Exception("Couldn't connect to group system!");
    
    
    // Find a group for user if not a member of any.
    if ( !($userId = $loginSys->user['id']) )
        throw new Exception("Not logged in!");

    $newGroup = $groupSys->FindGroup($userId);
    
    if (!$groupSys->AddToGroup($newGroup, $userId))
        throw new Exception("Couldn't add new group!");

}
catch (Exception $e)
{
    $error_msg = $e->getMessage();
    $success = false;
}

echo json_encode(array('success' => $success, 'error_msg' => $error_msg, 'group' => $newGroup));