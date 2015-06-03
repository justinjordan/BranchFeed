<?php

require_once('../php/Connection.php');
require_once('../php/LoginSystem.php');
require_once('../php/PostSystem.php');
require_once('../php/GroupSystem.php');
require_once('../php/Time.php');

$success = true;
$error_msg = '';
$posts = array();

try
{
    // Test Parameters
    if ( !isset( $_GET['post_id'], $_GET['last_loaded'] ) )
        throw new Exception("Parameters not received!");
    else
    {
        $post_id = $_GET['post_id'];
        $last_post = $_GET['last_loaded'];
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
    
    
    $group_id = $postSys->GetPost($post_id)['group_id'];  // get group id directly from post to verify user's membership
    
    // Verify that user is member of group
    if ( !$groupSys->is_member($group_id, $loginSys->user['id']) )
        throw new Exception("User isn't a member of requested group!");
    
    
    // Get Posts
    if ( !($comments = $postSys->GetCommentsUpdate($post_id, $last_post)) )
        throw new Exception($postSys->error);
    
    
}
catch (Exception $e)
{
    $error_msg = $e->getMessage();
    $success = false;
}

echo json_encode(array('success' => $success, 'error_msg' => $error_msg, 'comments' => $comments));




