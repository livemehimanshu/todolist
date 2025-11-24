<?php
require_once "config.php";

$username = $password = "";
$u_err = $p_err = "";

if($_SERVER["REQUEST_METHOD"] == "POST"){
    
    if(empty(trim($_POST["username"]))){
        $u_err = "Username zaroori hai.";
    } else {
        $sql = "SELECT id FROM users WHERE username = ?";
        
        if($stmt = $conn->prepare($sql)){ 
            $stmt->bindParam(1, $param_username, PDO::PARAM_STR);
            $param_username = trim($_POST["username"]);
            
            if($stmt->execute()){
                if($stmt->rowCount() == 1){
                    $u_err = "Yeh username pehle se registered hai.";
                } else{
                    $username = trim($_POST["username"]);
                }
            }
            unset($stmt);
        }
    }

    if(empty(trim($_POST["password"]))){
        $p_err = "Password enter karo.";     
    } elseif(strlen(trim($_POST["password"])) < 6){
        $p_err = "Password mein kam se kam 6 characters hone chahiye.";
    } else{
        $password = trim($_POST["password"]);
    }

    if(empty($u_err) && empty($p_err)){
        $sql = "INSERT INTO users (username, password) VALUES (?, ?)";
         
        if($stmt = $conn->prepare($sql)){
            $param_password = password_hash($password, PASSWORD_DEFAULT); 
            
            $stmt->bindParam(1, $username, PDO::PARAM_STR);
            $stmt->bindParam(2, $param_password, PDO::PARAM_STR);
            
            if($stmt->execute()){
                header("location: login.php");
            } else{
                echo "Kuch gadbad ho gayi, try again.";
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
    <title>Register</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>.card-wrapper{ width: 360px; margin: 0 auto; }</style>
</head>
<body>
    <div class="container mt-5">
        <div class="card-wrapper">
            <div class="card p-4">
                <h4 class="card-title text-center">Naya Account Banao</h4>
                <p>Form fill karke account banao.</p>
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
                        <input type="submit" class="btn btn-primary btn-block" value="Sign Up">
                    </div>
                    <p class="text-center">Account hai? <a href="login.php">Login karo</a>.</p>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
