<?php

require_once('../php/Connection.php');
require_once('../php/LoginSystem.php');
require_once('../php/GroupSystem.php');

$db = new Connection();
$loginSys = new LoginSystem($db);
$groupSys = new GroupSystem($db);


// Find group
$openGroups = $groupSys->FindGroup( $loginSys->user['id'] );
if ( $groupSys->AddToGroup($openGroups[0], $loginSys->user['id']) )
{
    $loginSys->SelectGroup($openGroups[0]);
}

// Redirect back
header( 'Location: index.php' );