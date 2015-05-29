<?php

/*** php/LoginSystem.php ***/

// Dependencies
require_once('Bcrypt.php');
require_once('Validate.php'); /* checks user input for registration */

require_once('errormsg.php'); // Standard error messages

class LoginSystem
{
    public $user = false;
    public $group_id = false;
    public $error;
    
    private $db;
    
    function __construct( $db )
    {
        $this->db = $db;
        $this->error = "Error!";
        
        // Start Cookie Session
        session_start();
        
        if ( $this->testSession() )
        {
            // User Logged In
            
            $this->user = $this->getUserData( $_SESSION['handle'] );
            $this->group_id = $_SESSION['group_id'];
        }
    }
    
    /*** Public Functions ***/
    
    public function Login( $handle, $pass )
    {
        try
        {
            if ( $this->is_empty($handle, $pass) )
                throw new Exception(FORMINCOMPLETE_ERROR_MSG);
            
            if ( !($userData = $this->getUserData( $handle )) )
                throw new Exception(USER_ERROR_MSG);
            
            if ( !Bcrypt::Authenticate( $pass, $userData['hash'] ) )
                throw new Exception(PASSWORD_ERROR_MSG);
            
            if ( !($this->createSession( $userData['handle'], $userData['default_group'] ) && ($this->user = $this->getUserData($handle))) )
                throw new Exception(SESSION_ERROR_MSG);
            
            return true;
        }
        catch (Exception $e)
        {
            $this->error = $e->getMessage();
        }
        
        return false;
    }
    
    public function Logout()
    {
        $_SESSION = array();  // Erase session data... replace with empty array!
        
        try
        {
            if ( !session_destroy() )
                throw new Exception("Couldn't destroy login session!");
            
        }
        catch (Exception $e)
        {
            $this->error = $e;
            return false;
        }
        
        return true;
    }
    
    public function Register( $handle, $email, $pass1, $pass2, $rights = 0 )
    {
        try
        {
            if ( $this->is_empty($handle, $email, $pass1, $pass2) )
                throw new Exception(FORMINCOMPLETE_ERROR_MSG);
            
            if ( !Validate::CheckHandle($handle) )
                throw new Exception(HANDLEVALIDATION_ERROR_MSG);
            
            if ( $this->userExists($handle) )
                throw new Exception(NAMETAKEN_ERROR_MSG);
            
            if ( !Validate::CheckEmail($email) )
                throw new Exception(EMAILVALIDATION_ERROR_MSG);
            
            if ( $pass1 != $pass2 )
                throw new Exception(PASSWORDCONFIRM_ERROR_MSG);
            
            if ( !Validate::CheckPassword($pass1) )
                throw new Exception(PASSWORDVALIDATION_ERROR_MSG);
            

            $sql = "INSERT INTO users (handle,hash,email,rights) VALUES (?,?,?,?)";

            if ( !($stmt = $this->db->prepare( $sql )) )
                throw new Exception(STMT_ERROR_MSG);
            
            $hash = Bcrypt::CreateHash($pass1);

            // Convert Date String to MySQL format
            date_default_timezone_set('America/Chicago');
            $birthdate = date('Y-m-d', strtotime($birthdate));

            // Execute Statement (run query)
            $stmt->bind_param('sssi', $handle, $hash, $email, $rights);

            if ( !$stmt->execute() )
                throw new Exception(EXECUTE_ERROR_MSG);
            
            
            $stmt->close();
            return true;
            
            
        }
        catch (Exception $e)
        {
            $this->error = $e->getMessage();
        }
        
        return false;
    }
    
    
    public function SelectGroup( $group_id )
    {
        $_SESSION['group_id'] = $group_id;
        $this->group_id = $group_id;
        
        return $_SESSION['group_id'] == $group_id && $this->group_id == $group_id;
    }
    
    public function SetDefaultGroup( $group_id )
    {
        if ( $this->user )
        {
            $sql = "UPDATE users SET default_group=? WHERE id=?";
            
            if ( $stmt = $this->db->prepare($sql) )
            {
                $stmt->bind_param('ii', $group_id, $this->user['id']);
                $stmt->execute();
                
                $this->user->default_group = $group_id;
                
                return true;
            }
        }
        
        return false;
    }
    
    public function GetUsers( $userIdArray ) // Returns 2d array of user data, or false
    {
        $userIdList = join(',', $userIdArray);
        
        $sql = "SELECT id, handle, email FROM users WHERE id IN ($userIdList) ORDER BY handle";
        
        if ( $result = $this->db->query($sql) )
        {
            
            $output = array();
            
            while ( $row = $result->fetch_assoc() )
            {
                array_push($output, $row);
            }
            
            return $output;
        }
        
        return false;
    }
    
    
    /*** Private Functions ***/
    
    private function userExists( $handle )
    {
        $sql = "SELECT COUNT(1) as total FROM users WHERE handle=?";
        
        if ( $stmt = $this->db->prepare( $sql ) )
        {
            $stmt->bind_param('s', $handle);
            $stmt->execute();
            $stmt->bind_result($total);
            if ( $stmt->fetch() )
            {
                if ( $total > 0 )
                {
                    $stmt->close();
                    
                    return true;
                }
            }
        }
        
        return false;
    }
    
    private function getUserData( $handle )
    {
        $sql = "SELECT id,handle,hash,email,rights,default_group FROM users WHERE handle=?";
        
        if ( !($stmt = $this->db->prepare( $sql )) )
        {
            $this->error = STMT_ERROR_MSG;
        }
        else
        {
            $stmt->bind_param('s', $handle);
            $stmt->execute();
            $stmt->bind_result($id, $handle, $hash, $email, $rights, $default_group);
            
            if ( $stmt->fetch() )
            {
                
                $stmt->close();
                
                $selected_group = $default_group;
            
                // Check if selected group is stored in session
                if ( $this->group_id )
                    $selected_group = $this->group_id; 
                
                return array('id' => $id, 
                             'handle' => $handle, 
                             'hash' => $hash, 
                             'email' => $email, 
                             'rights' => $rights,
                             'selected_group' => $selected_group,
                             'default_group' => $default_group );
            }
            
            $stmt->close();
        }
        
        return false;
    }
    
    private function testSession()
    {
        return isset($_SESSION['handle'], $_SESSION['group_id']);
    }
    
    private function createSession( $handle, $group_id )
    {
        $_SESSION['handle'] = $handle;
        $_SESSION['group_id'] = $group_id;
        
        return isset( $_SESSION['handle'], $_SESSION['group_id'] );
    }
    
    private function is_empty()
    {
        foreach ( func_get_args() as $arg )
        {
            if ( empty($arg) )
                return true;
        }
        
        return false;
    }
    
}
