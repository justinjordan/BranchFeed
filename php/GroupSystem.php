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
    
    public function is_member( $group_id, $user_id ) // return boolean
    {
        try
        {
            $sql = "SELECT count(*) AS count FROM groups WHERE id=? AND user_id=?";

            if ( !($stmt = $this->db->prepare($sql)) )
                throw new Exception(STMT_ERROR_MSG);
            
            if ( !$stmt->bind_param('ii', $group_id, $user_id) )
                throw new Exception(BIND_PARAM_ERROR_MSG);
            
            if ( !$stmt->execute() )
                throw new Exception(EXECUTE_ERROR_MSG);
            
            if ( !$stmt->bind_result($count) )
                throw new Exception(BIND_RESULT_ERROR_MSG);
            
            if ( !$stmt->fetch() )
                throw new Exception(FETCH_RESULT_ERROR_MSG);
            
            
            return $count>0;  // return true if group/user match
            
        }
        catch (Exception $e)
        {
            $this->error = $e;
        }
        
        return false;
    }
    
    public function GetUserGroups( $user_id ) // returns array of group ids, or false
    {
        if ( !is_array($user_id) )
        {
            // SINGLE USER
            
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
            // MULTIPLE USERS
            
            
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
        try
        {
            if ( !($userGroups = $this->GetUserGroups( $user_id )) )
                throw new Exception("FindGroup() error:  GetUserGroups() failed.");
            
            if ( !($knownUsers = $this->GetMembers($userGroups)) )
                throw new Exception("FindGroup() error:  GetMembers() failed.");
                
            if ( !($knownUserGroups = $this->GetUserGroups( $knownUsers )) )
                throw new Exception("FindGroup() error:  GetUserGroups() failed.");
            
            if ( !($openGroups = $this->GetOpenGroups()) )
                throw new Exception("FindGroup() error:  GetOpenGroups() failed.");
            
            $potentialGroups = array_diff($openGroups, $knownUserGroups);
            
            
            if ( count($potentialGroups) > 0 )
            {
                return current($potentialGroups);
            }
            else
            {
                if ( !($lastGroup = $this->GetLastGroup()) )
                    throw new Exception("FindGroup() error:  GetLastGroup() failed.");
                    
                return ($lastGroup+1);
            }
            
        }
        catch(Exception $e)
        {
            $this->error = $e->getMessage();
            
            return false;
        }
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
    
    public function RemoveFromGroup( $group_id, $user_id )
    {
        $sql = "DELETE FROM groups WHERE id=? AND user_id=?";
        
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
    
    public function GetMembers( $group_id )  // return array of member ids
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











