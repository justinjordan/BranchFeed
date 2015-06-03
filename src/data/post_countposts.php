<?php

require_once('../php/Connection.php');
require_once('../php/LoginSystem.php');
require_once('../php/PostSystem.php');
require_once('../php/GroupSystem.php');

$success = true;
$error_msg = '';
$count = 0;

try
{
    // Test Parameters
    if ( !isset( $_GET['group_id'] ) )
        throw new Exception("Request not received!");
    else
    {
        $group_id = $_GET['group_id'];
    }
    

    // Setup API
    if ( !($db = new Connection()) )
        throw new Exception("Couldn't connect to database!");
    
    if ( !($loginSys = new LoginSystem($db)) )
        throw new Exception("Couldn't connect to login system!");
    
    if ( !($postSys = new PostSystem($db)) )
        throw new Exception("Couldn't connect to post system!");
    
    if ( !($groupSys = new GroupSystem($db)) )
        throw new Exception("Couldn't connect to group system!");
    
    
    // Verify that user is member of group
    if ( !$groupSys->is_member($group_id, $loginSys->user['id']) )
        throw new Exception("User isn't a member of requested group!");
    
    
    // Get Posts
    if ( !($count = $postSys->CountPosts($group_id)) )
        throw new Exception("Unable to count posts!");
    
    
}
catch (Exception $e)
{
    $error_msg = $e->getMessage();
    $success = false;
}

echo json_encode(array('success' => $success, 'error_msg' => $error_msg, 'count' => $count));




