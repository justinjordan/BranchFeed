<?php

/*** php/LoginSystem.php ***/

// Dependencies
require_once('Bcrypt.php');
require_once('Validate.php'); /* checks user input for registration */

require_once('errormsg.php'); // Standard error messages

class LoginSystem
{
    public $user = false;
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
            
            if ( !($this->createSession( $userData['handle'] ) && ($this->user = $this->getUserData($handle))) )
                throw new Exception(SESSION_ERROR_MSG);
            
            // Clear any password recovery tickets that exist in the database
            $this->clearRecoveryTickets($userData['id']);
            
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
            
            if ( $this->emailExists($email) )
                throw new Exception(EMAILTAKEN_ERROR_MSG);
            
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
    
    public function OpenRecoveryTicket( $email )
    {
        try
        {
            // Validate email
            if ( !Validate::CheckEmail($email) )
                throw new Exception("Email invalid!");
            
            $userData = $this->getUserByEmail($email);
            
            // Get user id
            if ( !($user_id = $userData[id]) )
                throw new Exception("The email given isn't associated with an account!");
            
            // Start Recovery Ticket
            if ( !($ticketHash = $this->createRecoveryTicket($user_id)) )
                throw new Exception("Unable to open a recovery ticket!");
            
            // Send email
            if ( !$this->sendRecoveryEmail($userData, $ticketHash) )
                throw new Exception("Unable to send recovery email!");
            
            
            return true;
        }
        catch (Exception $e)
        {
            $this->error = $e->getMessage();
            return false;
        }
    }
    
    
    /*** Private Functions ***/
    
    private function getUserByEmail( $email )
    {
        $sql = "SELECT id,handle,hash,email,rights FROM users WHERE email=?";
        
        try
        {
            if ( !($stmt = $this->db->prepare($sql)) )
                throw new Exception(STMT_ERROR_MSG);
            
            if ( !$stmt->bind_param('s', $email) )
                throw new Exception("Couldn't bind parameter to statement!");
            
            if ( !$stmt->execute() )
                throw new Exception("Couldn't execute statement!");
            
            if ( !$stmt->bind_result($id, $handle, $hash, $email, $rights) )
                throw new Exception("Couldn't bind statement results to variables!");
            
            if ( !$stmt->fetch() )
                throw new Exception("Couldn't fetch statement results!");
            
            $stmt->close();
            
            return array(
                'id' => $id,
                'handle' => $handle,
                'hash' => $hash,
                'email' => $email,
                'rights' => $rights
            );
        }
        catch (Exception $e)
        {
            $this->error = $e->getMessage();
            return false;
        }
    }
    
    private function createRecoveryTicket( $user_id )
    {
        try
        {
            // Clear any previous tickets
            if ( !$this->clearRecoveryTickets($user_id) )
                throw new Exception("Couldn't remove old recovery tickets!");
            
            // Create random hash
            $hash = md5(rand());
            
            $sql = "INSERT INTO recovery (user_id, hash) VALUES (?,?)";
            
            if ( !($stmt = $this->db->prepare($sql)) )
                throw new Exception(STMT_ERROR_MSG);
            
            if ( !$stmt->bind_param('is', $user_id, $hash) )
                throw new Exception("Couldn't bind parameter to statement!");
            
            if ( !$stmt->execute() )
                throw new Exception("Couldn't execute statement!");
            
            return $hash;
        }
        catch(Exception $e)
        {
            $this->error = $e->getMessage();
            return false;
        }
    }
    
    private function testRecoveryTicket( $user_id, $hash )
    {
        
    }
    
    private function clearRecoveryTickets( $user_id )
    {
        try
        {
            $sql = "DELETE FROM recovery WHERE user_id=?";
            
            if ( !($stmt = $this->db->prepare($sql)) )
                throw new Exception(STMT_ERROR_MSG);
            
            if ( !$stmt->bind_param('i', $user_id) )
                throw new Exception("Couldn't bind parameter to statement!");
            
            if ( !$stmt->execute() )
                throw new Exception("Couldn't execute statement!");
        }
        catch(Exception $e)
        {
            $this->error = $e->getMessage();
            return false;
        }
        
        return true;
    }
    
    private function sendRecoveryEmail( $userData, $hash )
    {
        // recipient
        $to  = $userData['email'];

        // subject
        $subject = 'Branchfeed.dev Password Reset';

        // message
        $message = 'To reset your password, goto the following address:  branchfeed.dev/#/pswdreset/'. $userData['id'] .'/'. $hash;

        // Mail it
        if ( mail($to, $subject, $message) )
            return true;
        else
            return false;
    }
    
    private function userExists( $handle ) // check database for handle
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
    
    private function emailExists( $email ) // check database for email
    {
        $sql = "SELECT COUNT(1) as total FROM users WHERE email=?";
        
        if ( $stmt = $this->db->prepare( $sql ) )
        {
            $stmt->bind_param('s', $email);
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
        $sql = "SELECT id,handle,hash,email,rights FROM users WHERE handle=?";
        
        if ( !($stmt = $this->db->prepare( $sql )) )
        {
            $this->error = STMT_ERROR_MSG;
        }
        else
        {
            $stmt->bind_param('s', $handle);
            $stmt->execute();
            $stmt->bind_result($id, $handle, $hash, $email, $rights);
            
            if ( $stmt->fetch() )
            {
                
                $stmt->close();
                
                return array('id' => $id, 
                             'handle' => $handle, 
                             'hash' => $hash, 
                             'email' => $email, 
                             'rights' => $rights);
            }
            
            $stmt->close();
        }
        
        return false;
    }
    
    private function testSession()
    {
        return isset($_SESSION['handle']);
    }
    
    private function createSession( $handle )
    {
        $_SESSION['handle'] = $handle;
        
        return isset( $_SESSION['handle'] );
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
