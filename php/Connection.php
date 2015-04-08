<?php

/*** php/Connection.php ***/

require_once('mysqlinfo.php');


class Connection extends mysqli
{
    function __construct( $host = MYSQL_HOST, $user = MYSQL_USER, $pass = MYSQL_PASS, $db = MYSQL_DB )
    {
        parent::__construct( $host, $user, $pass, $db );
    }
    
    function __destruct()
    {
        $this->close();
    }
}
