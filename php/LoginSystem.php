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
        if ( $this->is_empty($handle, $pass) )
        {
            $this->error = FORMINCOMPLETE_ERROR_MSG;
        }
        else
        {
            if ( !($userData = $this->getUserData( $handle )) )
            {
                $this->error = USER_ERROR_MSG;
            }
            else
            {
                if ( !Bcrypt::Authenticate( $pass, $userData['hash'] ) )
                {
                    $this->error = PASSWORD_ERROR_MSG;
                }
                else
                {
                    return $this->createSession( $userData['handle'], $userData['default_group'] ) && ($this->user = $this->getUserData($handle));
                }
            }
        }
        
        return false;
    }
    
    public function Logout()
    {
        $_SESSION = array();  // Erase session data... replace with empty array!
        
        session_destroy();
    }
    
    public function Register( $handle, $pass1, $pass2, $email, $name, $location, $birthdate, $rights = 0 )
    {
        if ( $this->is_empty($handle, $pass1, $pass2, $email, $name, $location, $birthdate) )
        {
            $this->error = FORMINCOMPLETE_ERROR_MSG;
            return false;
        }
        if ( !Validate::CheckHandle($handle) )
        {
            $this->error = HANDLEVALIDATION_ERROR_MSG;
            return false;
        }
        if ( $this->userExists($handle) )
        {
            $this->error = NAMETAKEN_ERROR_MSG;
            return false;
        }
        if ( $pass1 != $pass2 )
        {
            $this->error = PASSWORDCONFIRM_ERROR_MSG;
            return false;
        }
        if ( !Validate::CheckPassword($pass1) )
        {
            $this->error = PASSWORDVALIDATION_ERROR_MSG;
            return false;
        }
        if ( !Validate::CheckEmail($email) )
        {
            $this->error = EMAILVALIDATION_ERROR_MSG;
            return false;
        }
        if ( !Validate::CheckName($name) )
        {
            $this->error = NAMEVALIDATION_ERROR_MSG;
            return false;
        }
        if ( !Validate::CheckLocation($location) )
        {
            $this->error = LOCATIONVALIDATION_ERROR_MSG;
            return false;
        }
        if ( !Validate::CheckBirthdate($birthdate) )
        {
            $this->error = BIRTHDATEVALIDATION_ERROR_MSG;
            return false;
        }
        
        
                
        $sql = "INSERT INTO users (handle,hash,email,name,location,birthdate,rights) VALUES (?,?,?,?,?,?,?)";

        if ( $stmt = $this->db->prepare( $sql ) )
        {
            $hash = Bcrypt::CreateHash($pass1);

            // Convert Date String to MySQL format
            date_default_timezone_set('America/Chicago');
            $birthdate = date('Y-m-d', strtotime($birthdate));

            // Execute Statement (run query)
            $stmt->bind_param('ssssssi', $handle, $hash, $email, $name, $location, $birthdate, $rights);

            if ( $stmt->execute() )
            {
                $stmt->close();

                return true;
            }
        }
        else
        {
            // Statement error

            $this->error = STMT_ERROR_MSG;
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
                
                return true;
            }
        }
        
        return false;
    }
    
    public function GetUsers( $userIdArray ) // Returns 2d array of user data, or false
    {
        $userIdList = join(',', $userIdArray);
        
        $sql = "SELECT id, handle, name, email, location FROM users WHERE id IN ($userIdList)";
        
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
        $sql = "SELECT id,handle,hash,name,email,location,birthdate,rights,default_group FROM users WHERE handle=?";
        
        if ( !($stmt = $this->db->prepare( $sql )) )
        {
            $this->error = STMT_ERROR_MSG;
        }
        else
        {
            $stmt->bind_param('s', $handle);
            $stmt->execute();
            $stmt->bind_result($id, $handle, $hash, $name, $email, $location, $birthdate, $rights, $default_group);
            if ( $stmt->fetch() )
            {
                date_default_timezone_set('America/Chicago');
                $birthdate = date('n-j-Y', strtotime($birthdate));
                
                $stmt->close();
                
                return array('id' => $id, 
                             'handle' => $handle, 
                             'hash' => $hash, 
                             'name' => $name, 
                             'email' => $email, 
                             'location' => $location, 
                             'birthdate' => $birthdate, 
                             'rights' => $rights, 
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
