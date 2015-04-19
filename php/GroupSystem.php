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
        if ( !is_array($user_id) )
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
        else
        {
            $userList = join(',', $user_id);
            
            $sql = "SELECT DISTINCT id FROM groups WHERE user_id IN ($userList)";
            
            if ( $result = $this->db->query($sql) )
            {
                $output = array();
                
                while ( $row = $result->fetch_row() )
                {
                    array_push($output, $row[0]);
                }
                
                return $output;
            }
        }
        
        return false;
    }
    
    public function GetOpenGroups()
    {
        $sql = "SELECT id FROM groups 
                GROUP BY id 
                HAVING count(id)<". MAX_GROUP_SIZE;
        
        if ( $result = $this->db->query($sql) )
        {
            $output = array();
            
            while ( $row = $result->fetch_row() )
            {
                array_push($output, $row[0]);
            }
            
            if ( !empty($output) )
                return $output;
            else
                return array($this->GetLastGroup()+1);
        }
        
        return false;
    }
    
    public function FindGroup( $user_id )
    {
        $userGroups = $this->GetUserGroups( $user_id );
        $knownUsers = $this->GetMembers($userGroups);
        $knownUserGroups = $this->GetUserGroups( $knownUsers );
        $openGroups = $this->GetOpenGroups();
        
        $potentialGroups = array_diff($openGroups, $knownUserGroups);
        
        if ( empty($potentialGroups) )
            return array($this->GetLastGroup()+1);
        else
            return $potentialGroups;
        
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
    
    public function GetLastGroup()
    {
        $sql = "SELECT MAX(id) FROM groups";
        
        if ( $result = $this->db->query($sql) )
        {
            $row = $result->fetch_row(); // fetch single row
            $result->close();
            
            return $row[0];
        }
        
        return false;
    }
    
    public function GetMembers( $group_id )
    {
        if ( !is_array($group_id) )
        {
            // Single Group
            
            $sql = "SELECT user_id FROM groups WHERE id=?";

            if ( $stmt = $this->db->prepare($sql) )
            {
                $stmt->bind_param('i', $group_id);
                $stmt->execute();
                $stmt->bind_result( $user_id );

                $output = array();

                while ( $stmt->fetch() )
                {
                    array_push($output, $user_id);
                }

                $stmt->close();

                return $output;
            }
        }
        else
        {
            // Multiple Groups
            
            $groupList = join(',', $group_id);
            
            $sql = "SELECT DISTINCT user_id FROM groups WHERE groups.id IN ($groupList)";
            
            if ( $result = $this->db->query($sql) )
            {
                
                $output = array();
                
                while ( $row = $result->fetch_row() )
                {
                    array_push($output, $row[0]);
                }
                
                $result->close();
                
                return $output;
            }
        }
            
        return false;
    }
    
}











