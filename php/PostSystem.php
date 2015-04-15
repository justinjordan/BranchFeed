<?php

/*** php/PostSystem.php ***/

require_once('errormsg.php');


class PostSystem
{
    public $error;
    
    private $db;
    
    function __construct($db)
    {
        $this->db = $db;
    }
    
    public function GetPosts( $group_id ) // returns 2d array of rows, or false
    {
        $sql = "SELECT users.id, users.handle, posts.id, posts.date, posts.content 
                FROM users, posts 
                WHERE users.id=posts.user_id AND posts.group_id=?
                ORDER BY posts.id DESC";
        
        if ( $stmt = $this->db->prepare($sql) )
        {
            // Success
            
            $stmt->bind_param('i', $group_id);
            $stmt->execute();
            $stmt->bind_result( $user_id, $user_handle, $post_id, $post_date, $post_content );
            
            $output = array();
            
            while ( $stmt->fetch() )
            {
                $row = array( 'user_id' => $user_id, 'user_handle' => $user_handle, 'id' => $post_id, 'date' => $post_date, 'content' => $post_content );
                
                array_push( $output, $row );
            }
            
            $stmt->close();
            
            return $output;
        }
        else
        {
            // Statement error
            
            $this->error = STMT_ERROR_MSG;
        }
        
        
        return false;
    }
    
    public function NewPost( $user_id, $group_id, $content ) // returns true on success, or false
    {
        // Remove html tags
        $content = strip_tags($content);
        
        
        $success = false;
        
        $sql = "INSERT INTO posts (user_id,group_id,content) VALUES (?,?,?)";
        
        if ( $stmt = $this->db->prepare($sql) )
        {
            $stmt->bind_param('iis', $user_id, $group_id, $content);
            $stmt->execute();
            
            if ( $stmt->affected_rows > 0 )
            {
                $success = true;
            }
            
            $stmt->close();
            
        }
        else
        {
            // Statement error
            
            $this->error = STMT_ERROR_MSG;
        }
        
        return $success;
    }
    
    public function RemovePost( $user_id, $post_id )
    {
        $success = false;
        
        $sql = "DELETE FROM posts WHERE user_id=? AND id=?";
        
        if ( $stmt = $this->db->prepare($sql) )
        {
            $stmt->bind_param('ii', $user_id, $post_id);
            $stmt->execute();
            
            if ( $stmt->affected_rows > 0 )
            {
                $success = true;
            }
            
            $stmt->close();
        }
        else
        {
            $this->error = STMT_ERROR_MSG;
        }
                
        return $success;
    }
}











