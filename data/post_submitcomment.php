<?php

require_once('../php/Connection.php');
require_once('../php/LoginSystem.php');
require_once('../php/PostSystem.php');
require_once('../php/GroupSystem.php');


$success = false;
$error_msg = 'msg';

try
{
    if ( !($params = json_decode(file_get_contents('php://input'))) )
        throw new Exception("Couldn't decode incoming data!");
    
    if ( !($db = new Connection()) )
        throw new Exception("Couldn't connect to database!");
    
    if ( !($loginSys = new LoginSystem($db)) )
        throw new Exception("Couldn't connect to login system!");
    
    if ( !($groupSys = new GroupSystem($db)) )
        throw new Exception("Couldn't connect to group system!");
    
    if ( !($postSys = new PostSystem($db)) )
        throw new Exception("Couldn't connect to post system!");
    
    
    
    if ( !($params->post_id && $params->group_id && $params->content) )
        throw new Exception("Missing parameters!");
    else
    {
        $user_id = $loginSys->user['id'];
        $post_id = $params->post_id;
        $group_id = $params->group_id;
        $content = $params->content;

        // Verify that user is a member of group
        if ( !$groupSys->is_member($group_id, $user_id) )
            throw new Exception("User is not a member of the group!");
        
        // Post message
        if ( !$postSys->NewComment($user_id, $post_id, $group_id, $content) )
            throw new Exception("Unable to submit comment!");
        else
            $success = true;
    }
}
catch (Exception $e)
{
    $error_msg = $e->getMessage();
}

echo json_encode(array('success' => $success, 'error_msg' => $error_msg));