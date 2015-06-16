<?php


class Validate
{
    public static function CheckHandle( $handle ) // returns true on valid, or false
    {
        if ( self::containsVulgarity($handle) )
        {
            return false;
        }
        
        return true;
    }
    
    public static function CheckPassword( $password ) // returns true on valid, or false
    {
        if ( strlen($password) < 8 )
        {
            return false;
        }
        
        return true;
    }
    
    public static function CheckName( $name ) // returns true on valid, or false
    {
        if ( !preg_match("/^[a-zA-Z ]*$/",$name) ) {
            
            // Invalid
            return false;
        }
        
        return true;
    }
    
    public static function CheckEmail( $email ) // returns true on valid, or false
    {
        if ( filter_var($email, FILTER_VALIDATE_EMAIL) ) {
            
            // Valid
            return true;
        }
        
        return false;
    }
    
    public static function CheckLocation( $location ) // returns true on valid, or false
    {
        if ( !preg_match("/^[a-zA-Z ]*$/",$name) ) {
            
            // Invalid
            return false;
        }
        
        return true;
    }
    
    public static function CheckBirthdate( $birthdate ) // returns true on valid, or false
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