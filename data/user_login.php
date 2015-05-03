<?php

require_once('../php/Connection.php');
require_once('../php/LoginSystem.php');
require_once('../php/GroupSystem.php');


$success = true;
$error_msg = '';

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
    
    if ( !($params->user && $params->pass) )
        throw new Exception('Form incomplete!');
    
    
    // Login
    $user = $params->user;
    $pass = $params->pass;

    if ( !($success = $loginSys->Login( $user, $pass )) )
        throw new Exception('Wrong username/password!');
    
    
    // Find a group for user if not a member of any.
    $userId = $loginSys->user['id'];
    $userGroups = $groupSys->GetUserGroups($userId);
    
    if ( count($userGroups) == 0 )
    {
        $openGroups = $groupSys->FindGroup($userId);
        $groupSys->AddToGroup($openGroups[0], $userId);
        
        $loginSys->SetDefaultGroup($openGroups[0]);
        $loginSys->SelectGroup($openGroups[0]);
        
    }
}
catch (Exception $e)
{
    $error_msg = $e->getMessage();
    $success = false;
}

echo json_encode(array('success' => $success, 'error_msg' => $error_msg));