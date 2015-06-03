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
    
    public function GetPost( $id ) // returns array, or false
    {
        $sql = "SELECT users.id, users.handle, posts.id, posts.date, posts.content, posts.group_id,
                (SELECT count(*) FROM comments WHERE post_id=posts.id) as comment_count
                FROM users, posts 
                WHERE users.id=posts.user_id AND posts.id=?
                LIMIT 1";
        
        if ( $stmt = $this->db->prepare($sql) )
        {
            // Success
            
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->bind_result( $user_id, $user_handle, $post_id, $post_date, $post_content, $group_id, $comment_count );
            $stmt->fetch();


            $output = array( 
                'user_id' => $user_id,
                'user_handle' => $user_handle,
                'id' => $post_id,
                'date' => $post_date,
                'content' => $post_content,
                'group_id' => $group_id,
                'comment_count' => $comment_count
            );

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
    
    public function GetPosts( $group_id, $offset=0, $amount=5 ) // returns 2d array of rows, or false
    {
        $sql = "SELECT users.id, users.handle, posts.id, posts.date, posts.content, posts.group_id,
                (SELECT count(*) FROM comments WHERE post_id=posts.id) as comment_count
                FROM users, posts 
                WHERE users.id=posts.user_id AND posts.group_id=?
                ORDER BY posts.id DESC
                LIMIT ?,?";
        
        if ( $stmt = $this->db->prepare($sql) )
        {
            // Success
            
            $stmt->bind_param('iii', $group_id, $offset, $amount);
            $stmt->execute();
            $stmt->bind_result( $user_id, $user_handle, $post_id, $post_date, $post_content, $group_id, $comment_count );
            
            $output = array();
            
            while ( $stmt->fetch() )
            {
                $row = array( 
                    'user_id' => $user_id, 
                    'user_handle' => $user_handle, 
                    'id' => $post_id, 
                    'date' => $post_date, 
                    'content' => $post_content, 
                    'group_id' => $group_id,
                    'comment_count' => $comment_count
                );
                
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
    
    public function GetComments( $post_id, $offset=0, $amount=5 ) // returns 2d array of rows, or false
    {
        $sql = "SELECT users.id, users.handle, comments.id, comments.date, comments.content 
                FROM users, comments 
                WHERE users.id=comments.user_id AND comments.post_id=?
                ORDER BY comments.id DESC
                LIMIT ?,?";
        
        if ( $stmt = $this->db->prepare($sql) )
        {
            // Success
            
            $stmt->bind_param('iii', $post_id, $offset, $amount);
            $stmt->execute();
            $stmt->bind_result( $user_id, $user_handle, $comment_id, $comment_date, $comment_content );
            
            $output = array();
            
            while ( $stmt->fetch() )
            {
                $row = array( 'user_id' => $user_id, 'user_handle' => $user_handle, 'id' => $comment_id, 'date' => $comment_date, 'content' => $comment_content );
                
                array_unshift( $output, $row );  // sorts the DESC result by ASC
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
    
    public function GetPostUpdate( $group_id, $last_loaded )
    {
        
        $sql = "SELECT users.id, users.handle, posts.id, posts.date, posts.content, posts.group_id,
                (SELECT count(*) FROM comments WHERE post_id=posts.id) as comment_count
                FROM users, posts
                WHERE users.id=posts.user_id AND posts.group_id=? AND posts.id>?
                ORDER BY posts.id DESC";
        
        if ( $stmt = $this->db->prepare($sql) )
        {
            $stmt->bind_param('ii', $group_id, $last_loaded);
            $stmt->execute();
            $stmt->bind_result( $user_id, $user_handle, $post_id, $post_date, $post_content, $group_id, $comment_count );
            $stmt->store_result();
            
            if ( $stmt->num_rows > 0 )
            {
                $output = array();

                while ( $stmt->fetch() )
                {
                    $row = array( 
                        'user_id' => $user_id, 
                        'user_handle' => $user_handle, 
                        'id' => $post_id, 
                        'date' => $post_date, 
                        'content' => $post_content,
                        'group_id' => $group_id,
                        'comment_count' => $comment_count
                    );

                    array_push( $output, $row );
                }
                
                $stmt->free_result();
                $stmt->close();

                return $output;
            }
            else
            {
                $this->error = "No update available.";
            }
        }
        else
        {
            // Statement error
            
            $this->error = STMT_ERROR_MSG;
        }
        
        return false;
    }
    
    public function GetCommentsUpdate( $post_id, $last_loaded ) // returns 2d array of rows, or false
    {
        $sql = "SELECT users.id, users.handle, comments.id, comments.date, comments.content 
                FROM users, comments 
                WHERE users.id=comments.user_id AND comments.post_id=? AND comments.id>?";
        
        if ( $stmt = $this->db->prepare($sql) )
        {
            // Success
            
            $stmt->bind_param('ii', $post_id, $last_loaded);
            $stmt->execute();
            $stmt->bind_result( $user_id, $user_handle, $comment_id, $comment_date, $comment_content );
            
            $output = array();
            
            while ( $stmt->fetch() )
            {
                $row = array( 'user_id' => $user_id, 'user_handle' => $user_handle, 'id' => $comment_id, 'date' => $comment_date, 'content' => $comment_content );
                
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
            else
            {
                $this->error = "Couldn't create new post!";
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
    
    public function NewComment( $user_id, $post_id, $group_id, $content ) // returns true on success, or false
    {
        // Remove html tags
        $content = strip_tags($content);
        
        
        $success = false;
        
        $sql = "INSERT INTO comments (user_id,post_id,group_id,content) VALUES (?,?,?,?)";
        
        if ( $stmt = $this->db->prepare($sql) )
        {
            $stmt->bind_param('iiis', $user_id, $post_id, $group_id, $content);
            $stmt->execute();
            
            if ( $stmt->affected_rows > 0 )
            {
                $success = true;
            }
            else
            {
                $this->error = "Couldn't create new comment!";
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
    
    public function EditPost( $user_id, $post_id, $content )
    {
        // Remove html tags
        $content = strip_tags($content);
        
        
        $success = false;
        
        $sql = "UPDATE posts SET content=? WHERE user_id=? AND id=?";
        
        if ( $stmt = $this->db->prepare($sql) )
        {
            $stmt->bind_param('sii', $content, $user_id, $post_id);
            $stmt->execute();
            
            if ( $stmt->affected_rows > 0 )
            {
                $success = true;
            }
            else
            {
                $this->error = "Couldn't edit post!";
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
    
    public function EditComment( $user_id, $id, $content )
    {
        // Remove html tags
        $content = strip_tags($content);
        
        
        $success = false;
        
        $sql = "UPDATE comments SET content=? WHERE user_id=? AND id=?";
        
        if ( $stmt = $this->db->prepare($sql) )
        {
            $stmt->bind_param('sii', $content, $user_id, $id);
            $stmt->execute();
            
            if ( $stmt->affected_rows > 0 )
            {
                $success = true;
            }
            else
            {
                $this->error = "Couldn't edit comment!";
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
        try
        {
            // Remove Post
            
            $sql = "DELETE FROM posts WHERE user_id=? AND id=?";
            
            if ( !($stmt = $this->db->prepare($sql)) )
                throw new Exception(STMT_ERROR_MSG);
            
            $stmt->bind_param('ii', $user_id, $post_id);
            $stmt->execute();

            if ( $stmt->affected_rows == 0 )
                throw new Exception("Couldn't delete post!");
            
            
            // Remove Comments of Post
            
            $sql = "DELETE FROM comments WHERE post_id=?";
            
            if ( !($stmt = $this->db->prepare($sql)) )
                throw new Exception(STMT_ERROR_MSG);
            
            $stmt->bind_param('i', $post_id);
            $stmt->execute();
            
            $stmt->close();
           
        }
        catch (Exception $e)
        {
            $this->error = $e->getMessage();
            
            return false;
        }
                
        return true;
    }
    
    public function RemoveAllUserPosts( $group_id, $user_id )
    {
        try
        {
            // Remove Post
            
            $sql = "DELETE FROM posts WHERE group_id=? AND user_id=?";
            
            if ( !($stmt = $this->db->prepare($sql)) )
                throw new Exception(STMT_ERROR_MSG);
            
            $stmt->bind_param('ii', $group_id, $user_id);
            $stmt->execute();
            
            // Remove Comments
            
            $sql = "DELETE FROM comments WHERE group_id=? AND user_id=?";
            
            if ( !($stmt = $this->db->prepare($sql)) )
                throw new Exception(STMT_ERROR_MSG);
            
            $stmt->bind_param('ii', $group_id, $user_id);
            $stmt->execute();
            
            $stmt->close();
           
        }
        catch (Exception $e)
        {
            $this->error = $e->getMessage();
            
            return false;
        }
                
        return true;
    }
    
    public function RemoveComment( $user_id, $comment_id )
    {
        $success = false;
        
        $sql = "DELETE FROM comments WHERE user_id=? AND id=?";
        
        if ( $stmt = $this->db->prepare($sql) )
        {
            $stmt->bind_param('ii', $user_id, $comment_id);
            $stmt->execute();
            
            if ( $stmt->affected_rows > 0 )
            {
                $success = true;
            }
            else
            {
                $this->error = "Couldn't delete comment!";
            }
            
            $stmt->close();
        }
        else
        {
            $this->error = STMT_ERROR_MSG;
        }
                
        return $success;
    }
    
    public function CountPosts( $group_id )
    {
        $success = false;
        
        $sql = "SELECT COUNT(*) as count FROM posts WHERE group_id=?";
        
        if ( $stmt = $this->db->prepare($sql) )
        {
            $stmt->bind_param('i', $group_id);
            $stmt->execute();
            $stmt->bind_result($count);
            $stmt->fetch();
            
            $stmt->free_result();
            $stmt->close();
            
            return $count;
        }
        else
        {
            $this->error = STMT_ERROR_MSG;
        }
        
        return false;
    }
    
    public function CountComments( $post_id )
    {
        $success = false;
        
        $sql = "SELECT COUNT(*) as count FROM comments WHERE post_id=?";
        
        if ( $stmt = $this->db->prepare($sql) )
        {
            $stmt->bind_param('i', $post_id);
            $stmt->execute();
            $stmt->bind_result($count);
            $stmt->fetch();
            
            $stmt->free_result();
            $stmt->close();
            
            return $count;
        }
        else
        {
            $this->error = STMT_ERROR_MSG;
        }
        
        return false;
    }
}











