<?php

require_once('../php/Connection.php');
require_once('../php/LoginSystem.php');
require_once('../php/GroupSystem.php');

$db = new Connection();
$loginSys = new LoginSystem($db);
$groupSys = new GroupSystem($db);


$userGroups = $groupSys->GetUserGroups($loginSys->user['id']);

// Find group
$newGroup = $groupSys->FindGroup( $userGroups ); // excluding $userGroups
if ( $groupSys->AddToGroup($newGroup, $loginSys->user['id']) )
{
    $loginSys->SelectGroup($newGroup);
}

// Redirect back
header( 'Location: index.php' );