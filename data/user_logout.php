<?php

require_once('../php/Connection.php');
require_once('../php/LoginSystem.php');


$success = false;
$error_msg = '';

try
{
    
    if ( !($db = new Connection()) )
        throw new Exception("Couldn't connect to database!");
    
    if ( !($loginSys = new LoginSystem($db)) )
        throw new Exception("Couldn't connect to login system!");
    
    
    // Perform Logout
    if ( !$loginSys->Logout() )
        throw new Exception("Couldn't logout!");
    else
    {
        $success = true;
    }
    
}
catch (Exception $e)
{
    $error_msg = $e->getMessage();
}

echo json_encode(array('success' => $success, 'error_msg' => $error_msg));