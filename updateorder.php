<?php
session_start();
require_once "config.php";

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit;
}

if (isset($_POST['order']) && is_array($_POST['order'])) {
    
    $task_order_array = $_POST['order'];
    $current_user_id = $_SESSION["id"];
    $order_position = 0;

    try {
        $conn->beginTransaction();

        $sql = "UPDATE tasks SET task_order = ? WHERE id = ? AND user_id = ?";
        $stmt = $conn->prepare($sql);

        foreach ($task_order_array as $task_id) {
            $order_position++; 
            
            $stmt->bindParam(1, $order_position, PDO::PARAM_INT);
            $stmt->bindParam(2, $task_id, PDO::PARAM_INT);
            $stmt->bindParam(3, $current_user_id, PDO::PARAM_INT);
            $stmt->execute();
        }

        $conn->commit();
        
        echo json_encode(['status' => 'success', 'message' => 'Order updated.']);

    } catch (Exception $e) {
        $conn->rollBack();
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Database error.']);
    }
    
    unset($conn);
    
} else {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid data received.']);
}
?>
