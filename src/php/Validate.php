<?php


class Validate
{
    public static function CheckHandle( $handle ) // returns true on acceptable, or false
    {
        if ( self::containsVulgarity($handle) )
        {
            return false;
        }
        
        return true;
    }
    
    public static function CheckPassword( $password ) // returns true on acceptable, or false
    {
        if ( strlen($password) < 8 )
        {
            return false;
        }
        
        return true;
    }
    
    public static function CheckName( $name ) // returns true on acceptable, or false
    {
        if ( !preg_match("/^[a-zA-Z ]*$/",$name) ) {
            
            // unacceptable
            return false;
        }
        
        return true;
    }
    
    public static function CheckEmail( $email ) // returns true on acceptable, or false
    {
        if ( !filter_var($email, FILTER_VALIDATE_EMAIL) ) {
            
            // unacceptable
            return false;
        }
        
        return true;
    }
    
    public static function CheckLocation( $location ) // returns true on acceptable, or false
    {
        if ( !preg_match("/^[a-zA-Z ]*$/",$name) ) {
            
            // unacceptable
            return false;
        }
        
        return true;
    }
    
    public static function CheckBirthdate( $birthdate ) // returns true on acceptable, or false
    {
        
        
        return true;
    }
    
    
    
    // Private
    
    private static function containsVulgarity( $text )
    {
        $regexFilter = "(fuck|shit|ass|bitch|cunt|dick|tits|titties)";
        
        if ( preg_match($regexFilter,$text) )
        {
            return true;
        }
        
        return false;
    }
    
}