<?php

require_once('../php/Connection.php');
require_once('../php/LoginSystem.php');
require_once('../php/GroupSystem.php');

$success = true;
$error_msg = '';
$memberIds = array();
$members = array();

try
{
    // Test Parameters
    if ( !isset( $_GET['group_id'] ) )
        throw new Exception("Request not received!");
    else
        $group_id = $_GET['group_id'];
    

    // Setup API
    if ( !($db = new Connection()) )
        throw new Exception("Couldn't connect to database!");
    
    if ( !($loginSys = new LoginSystem($db)) )
        throw new Exception("Couldn't connect to login system!");
    
    if ( !($groupSys = new GroupSystem($db)) )
        throw new Exception("Couldn't connect to group system!");
    
    
    // Get Group Member Ids
    if ( !($memberIds = $groupSys->GetMembers( $group_id )) )
        throw new Exception("Couldn't retreive group members!");
    
    // Get Member Details
    if ( !($members = $loginSys->GetUsers($memberIds)) )
        throw new Exception("Couldn't retreive member details!");
    
    // Prepare output array
    $output = array();
    foreach ( $members as $member )
    {
        array_push($output, array( 'id' => $member['id'], 'handle' => $member['handle']));
    }
    
}
catch (Exception $e)
{
    $error_msg = $e->getMessage();
    $success = false;
}

echo json_encode(array('success' => $success, 'error_msg' => $error_msg, 'members' => $output ));




