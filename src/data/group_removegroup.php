<?php

require_once('../php/Connection.php');
require_once('../php/LoginSystem.php');
require_once('../php/GroupSystem.php');


$success = true;
$error_msg = '';

try
{
    // Test Parameters
    if ( !isset( $_GET['group_id'] ) )
        throw new Exception("Parameters not received!");
    
    if ( !($db = new Connection()) )
        throw new Exception("Couldn't connect to database!");
    
    if ( !($loginSys = new LoginSystem($db)) )
        throw new Exception("Couldn't connect to login system!");
    
    if ( !($groupSys = new GroupSystem($db)) )
        throw new Exception("Couldn't connect to group system!");
    
    // Set Variables
    $group_id = $_GET['group_id'];
    $user_id = $loginSys->user['id'];
    
    if ( !$groupSys->is_member($group_id, $user_id) )
        throw new Exception("Not a member of group.");
    
    if (!$groupSys->RemoveFromGroup($group_id, $user_id))
        throw new Exception("Couldn't remove from group!");
    
    if (!$postSys->RemoveAllUserPosts($group_id, $user_id))
        throw new Exception("Couldn't delete user's posts!");

}
catch (Exception $e)
{
    $error_msg = $e->getMessage();
    $success = false;
}

echo json_encode(array('success' => $success, 'error_msg' => $error_msg));