<?php

require_once('../php/Connection.php');
require_once('../php/LoginSystem.php');
require_once('../php/PostSystem.php');


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
    
    if ( !($postSys = new PostSystem($db)) )
        throw new Exception("Couldn't connect to post system!");
    
    
    
    if ( !($params->post_id && $params->content) )
        throw new Exception("Missing parameters!");
    else
    {
        $user_id = $loginSys->user['id'];
        $post_id = $params->post_id;
        $content = $params->content;
        
        // Edit message
        if ( !$postSys->EditPost($user_id, $post_id, $content) )
            throw new Exception("Unable to edit post!");
        else
            $success = true;
    }
}
catch (Exception $e)
{
    $error_msg = $e->getMessage();
}

echo json_encode(array('success' => $success, 'error_msg' => $error_msg));