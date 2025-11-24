<?php
session_start();
 
if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    header("location: index.php");
    exit;
}
 
require_once "config.php";
 
$username = $password = "";
$u_err = $p_err = $login_err = ""; 
 
if($_SERVER["REQUEST_METHOD"] == "POST"){
 
    if(empty(trim($_POST["username"]))){
        $u_err = "Username daalo.";
    } else{
        $username = trim($_POST["username"]);
    }
    
    if(empty(trim($_POST["password"]))){
        $p_err = "Password daalo.";
    } else{
        $password = trim($_POST["password"]);
    }
    
    if(empty($u_err) && empty($p_err)){
        $sql = "SELECT id, username, password FROM users WHERE username = ?";
        
        if($stmt = $conn->prepare($sql)){
            $stmt->bindParam(1, $param_username, PDO::PARAM_STR);
            $param_username = $username;
            
            if($stmt->execute()){
                if($stmt->rowCount() == 1){
                    if($row = $stmt->fetch()){
                        $hashed_password = $row["password"];
                        
                        if(password_verify($password, $hashed_password)){
                            $_SESSION["loggedin"] = true;
                            $_SESSION["id"] = $row["id"];
                            $_SESSION["username"] = $row["username"];                            
                            
                            header("location: index.php");
                        } else{
                            $login_err = "Username ya password galat hai.";
                        }
                    }
                } else{
                    $login_err = "Username ya password galat hai.";
                }
            }
            unset($stmt);
        }
    }
    unset($conn);
}
?>
 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>.card-wrapper{ width: 360px; margin: 0 auto; }</style>
</head>
<body>
    <div class="container mt-5">
        <div class="card-wrapper">
            <div class="card p-4">
                <h4 class="card-title text-center">Login Karo</h4>
                <?php 
                if(!empty($login_err)){
                    echo '<div class="alert alert-danger">' . $login_err . '</div>';
                }        
                ?>
                <form action="" method="post">
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="username" class="form-control <?php echo (!empty($u_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $username; ?>">
                        <div class="invalid-feedback"><?php echo $u_err; ?></div>
                    </div>    
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password" class="form-control <?php echo (!empty($p_err)) ? 'is-invalid' : ''; ?>">
                        <div class="invalid-feedback"><?php echo $p_err; ?></div>
                    </div>
                    <div class="form-group">
                        <input type="submit" class="btn btn-primary btn-block" value="Login">
                    </div>
                    <p class="text-center">Account nahi hai? <a href="signup.php">Sign Up karo</a>.</p>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
