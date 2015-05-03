<?php

require_once('../php/Connection.php');
require_once('../php/LoginSystem.php');


$success = false;
$error_msg = '';

try
{
    if ( !($params = json_decode(file_get_contents('php://input'))) )
        throw new Exception("Couldn't decode incoming data!");
    
    if ( !($db = new Connection()) )
        throw new Exception("Couldn't connect to database!");
    
    if ( !($loginSys = new LoginSystem($db)) )
        throw new Exception("Couldn't connect to login system!");
    
    if ( !($params->handle && $params->email && $params->pass1 && $params->pass2) )
        throw new Exception('Form incomplete!');
    else
    {
        $handle = $params->handle;
        $email = $params->email;
        $pass1 = $params->pass1;
        $pass2 = $params->pass2;

        if ( !($success = $loginSys->Register( $handle, $email, $pass1, $pass2 )) )
            throw new Exception($loginSys->error);
        else
            $success = true;
    }
}
catch (Exception $e)
{
    $error_msg = $e->getMessage();
}

echo json_encode(array('success' => $success, 'error_msg' => $error_msg));