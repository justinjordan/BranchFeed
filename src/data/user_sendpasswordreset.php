<?php

require_once('../php/Connection.php');
require_once('../php/LoginSystem.php');


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
    
    if ( !$params->email )
        throw new Exception('Form incomplete!');
    

    if ( !$loginSys->OpenRecoveryTicket( $params->email ) )
        throw new Exception($loginSys->error);
    
}
catch (Exception $e)
{
    $error_msg = $e->getMessage();
    $success = false;
}

echo json_encode(array('success' => $success, 'error_msg' => $error_msg));