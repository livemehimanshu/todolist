<?php
session_start();
require_once "config.php";

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

$current_user_id = $_SESSION["id"];

if(isset($_GET["id"]) && !empty(trim($_GET["id"]))){
    $id = trim($_GET["id"]);
    
    $sql = "DELETE FROM tasks WHERE id = ? AND user_id = ?"; 
    
    if($stmt = $conn->prepare($sql)){
        $stmt->bindParam(1, $id, PDO::PARAM_INT);
        $stmt->bindParam(2, $current_user_id, PDO::PARAM_INT);
        
        if($stmt->execute()){
            header("location: index.php");
            exit();
        } else{
            echo "Task delete karne mein error aayi.";
        }
        unset($stmt);
    }
    unset($conn);
} else {
    header("location: index.php");
    exit();
}
?>
