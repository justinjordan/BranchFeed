<?php

/*** php/GroupSystem.php ***/

define('MAX_GROUP_SIZE', 20);

require_once('errormsg.php'); // Standard error messages





class GroupSystem
{
    private $db;
    
    public $error;
    
    function __construct($db)
    {
        $this->db = $db;
    }
    
    public function GetUserGroups( $user_id ) // returns array of group ids, or false
    {
        if ( !empty($user_id) )
        {
            $sql = "SELECT groups.id FROM groups WHERE groups.user_id=?";
            
            if ( !($stmt = $this->db->prepare( $sql )) )
            {
                // Error
                
                $this->error = STMT_ERROR_MSG;
            }
            else
            {
                // Success
                
                $stmt->bind_param('i',$user_id);
                $stmt->execute();
                $stmt->bind_result($group_id);
                
                $output = array();
                
                while ( $stmt->fetch() )
                {
                    array_push( $output, $group_id );
                }
                
                $stmt->close();
                
                return $output;
            }
        }
        
        return false;
    }
    
    public function FindGroup( $exclude=0 )
    {
        if ( is_array($exclude) )
        {
            $excludeList = join(',', $exclude);
        }
        else
        {
            $excludeList = $exclude;
        }
        
        $sql = "SELECT (
                    SELECT id FROM groups 
                    WHERE id NOT IN (". $excludeList .")
                    GROUP BY id 
                    HAVING count(*)<? 
                    LIMIT 1
                ) AS open, 
                MAX(id) AS last 
                FROM groups";
        
        if ( $stmt = $this->db->prepare( $sql ) )
        {
            $max = MAX_GROUP_SIZE;
            $stmt->bind_param('i', $max);
            $stmt->execute();
            $stmt->bind_result( $open_group, $last_group );
            $stmt->fetch();
            $stmt->close();
            
            if ( !empty($open_group) )
            {
                // Group Available
                return $open_group;
            }
            else
            {
                // All groups full
                return $last_group+1;
            }
        }
        
        return false;
    }
    
    public function AddToGroup( $group_id, $user_id )
    {
        $sql = "INSERT INTO groups (id,user_id) VALUES (?,?)";
        
        if ( $stmt = $this->db->prepare($sql) )
        {
            $stmt->bind_param('ii', $group_id, $user_id);
            $stmt->execute();
            $stmt->close();
            
            return true;
        }
        
        return false;
    }
}











