<?php

require_once('../php/Connection.php');
require_once('../php/LoginSystem.php');

$db = new Connection();
$loginSys = new LoginSystem($db);

?>
<!doctype html>
<html>
    <head>
        <title>BranchFeed</title>
        
        <meta charset="utf-8"/>
        
        <link rel="stylesheet" type="text/css" href="css/index.css"/>
        
        <?php if ( $loginSys->user ): ?>
        <meta http-equiv="refresh" content="0; url=home.php" />
        <?php endif; ?>
    </head>
    <body>
          
<?php

if ( !$loginSys->user ):

?>
        
        <div><!-- wrapper div -->
            
            <div id="mainContainer">
                
                <div id="loginForm" class="formDiv">
                    <form method="post" action="login.php">
                        <h2>Login</h2>
                        <div id="loginFormInput">
                            <p>
                                Username<br>
                                <input type="text" name="user" id="userField"/><br>
                            </p>
                            <p>
                                Password<br>
                                <input type="password" name="pass"/>
                            </p>
                        </div>
                        <div id="loginFormAction">
                            <input type="submit" value="Login"/>
                        </div>
                    </form>
                </div><!-- loginForm -->
                
                <div id="registerForm" class="formDiv">
                    <form method="post" action="register.php">
                        <h2>Register</h2>
                        <div id="registerFormInput">
                            <p>
                                Username<br>
                                <input type="text" name="user"/><br>
                            </p>
                            <p>
                                Password<br>
                                <input type="password" name="pass1"/><br>
                            </p>
                            <p>
                                Confirm Password<br>
                                <input type="password" name="pass2"/><br>
                            </p>
                            <p>
                                Email<br>
                                <input type="text" name="email"/><br>
                            </p>
                            <p>
                                Name<br>
                                <input type="text" name="name"/><br>
                            </p>
                            <p>
                                Location<br>
                                <input type="text" name="location"/><br>
                            </p>
                            <p>
                                Birth Date<br>
                                <input type="text" name="birthdate"/><br>
                            </p>
                        </div>
                        <div id="registerFormAction">
                            <input type="submit" value="Register"/>
                        </div>
                    </form>
                </div><!-- #registerForm -->
                
            </div><!-- #container -->
            
        </div><!-- wrapper div -->
        
<?php

endif;

?>
        
    </body>
</html>