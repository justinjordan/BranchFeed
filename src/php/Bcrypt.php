<?php

/* Bcrypt.php */

class Bcrypt
{
    public static function Authenticate($pass, $hash)
	{
		$salt = substr($hash, 0, 29);
		$newHash = self::CreateHash($pass, $salt);
        
		return $newHash == $hash;
	}
	
	public static function CreateHash($pass, $salt = null, $work_factor = 12)
	{
		if ($salt == null)
			$salt = self::CreateSalt($work_factor);
			
		return crypt($pass, $salt);
	}
	
	public static function CreateSalt($work_factor = 12)
	{
		$salt = '$2a$' . str_pad($work_factor, 2, '0', STR_PAD_LEFT) . '$' . substr(uniqid('', true), 0, 22);
		
		return $salt;
	}
    
}