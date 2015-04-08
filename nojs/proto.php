<?php

require_once('../php/Validate.php');

if ( Validate::CheckUsername('titties') )
{
    echo 'good';
}
else
{
    echo 'bad';
}