<?php
session_start();
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

require_once "config.php";

$current_user_id = $_SESSION["id"];
$task_name = $error = "";

// CREATE (Adding a New Task)
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_task'])){
    if(empty(trim($_POST["task_name"]))){
        $error = "Task ka naam daalna zaroori hai.";
    } else {
        $task_name = trim($_POST["task_name"]);
        
        // Naya task hamesha sabse neeche (highest order) aayega.
        $sql_max_order = "SELECT MAX(task_order) AS max_order FROM tasks WHERE user_id = ?";
        $stmt_max = $conn->prepare($sql_max_order);
        $stmt_max->bindParam(1, $current_user_id, PDO::PARAM_INT);
        $stmt_max->execute();
        $max_order = $stmt_max->fetchColumn();
        $new_order = $max_order + 1;

        $sql = "INSERT INTO tasks (user_id, task_name, task_order) VALUES (?, ?, ?)"; 
        
        if($stmt = $conn->prepare($sql)){
            $stmt->bindParam(1, $current_user_id, PDO::PARAM_INT);
            $stmt->bindParam(2, $task_name, PDO::PARAM_STR);
            $stmt->bindParam(3, $new_order, PDO::PARAM_INT);
            
            if($stmt->execute()){
                header("location: index.php"); 
                exit();
            } else{
                $error = "Task add karne mein error aayi.";
            }
            unset($stmt);
        }
    }
}

// UPDATE (Editing an Existing Task)
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_task'])){
    $task_id = $_POST['task_id'];
    $new_name = trim($_POST['new_name']);

    if(empty($new_name)){
        $error = "Task ka naam khali nahi ho sakta.";
    } else {
        $sql = "UPDATE tasks SET task_name = ? WHERE id = ? AND user_id = ?"; 
        
        if($stmt = $conn->prepare($sql)){
            $stmt->bindParam(1, $new_name, PDO::PARAM_STR);
            $stmt->bindParam(2, $task_id, PDO::PARAM_INT);
            $stmt->bindParam(3, $current_user_id, PDO::PARAM_INT);
            
            if($stmt->execute()){
                header("location: index.php");
                exit();
            } else{
                $error = "Task update nahi ho paya.";
            }
            unset($stmt);
        }
    }
}

// READ (Fetching all Tasks)
$tasks = [];
$sql = "SELECT id, task_name, task_order, created_at FROM tasks WHERE user_id = ? ORDER BY task_order ASC"; 
if($stmt = $conn->prepare($sql)){
    $stmt->bindParam(1, $current_user_id, PDO::PARAM_INT);
    $stmt->execute();
    $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    unset($stmt);
}

unset($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My To-Do List</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.0/themes/base/jquery-ui.css"> 
    <style>
        body{ background-color: #f8f9fa; }
        .container{ max-width: 800px; margin-top: 30px; }
        /* Dragging ke waqt item ka style */
        .ui-sortable-helper { background: #f0f0f0; border: 1px solid #ccc; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .list-group-item { cursor: move; } /* Cursor change */
    </style>
</head>
<body>
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="m-0">Welcome, <?php echo htmlspecialchars($_SESSION["username"]); ?>!</h2>
            <a href="logout.php" class="btn btn-danger">Logout</a>
        </div>
        <hr>

        <h3 class="mb-4">Your To-Do List</h3>
        
        <?php if(!empty($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="card p-3 mb-4">
            <h5 class="card-title">Naya Task Jodein</h5>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="form-inline">
                <input type="text" name="task_name" class="form-control mr-2 flex-grow-1" placeholder="Task ka naam likhein" required>
                <button type="submit" name="add_task" class="btn btn-primary">Add Task</button>
            </form>
        </div>

        <h4 class="mt-5">Pending Tasks (<?php echo count($tasks); ?>)</h4>
        <ul class="list-group" id="sortable-list">
            <?php if (empty($tasks)): ?>
                <li class="list-group-item text-center text-muted">Koi task nahi hai. Naya task jodein!</li>
            <?php else: ?>
                <?php foreach ($tasks as $task): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center" data-id="<?php echo $task['id']; ?>">
                        <span><?php echo htmlspecialchars($task['task_name']); ?></span>
                        
                        <div>
                            <button type="button" class="btn btn-sm btn-info mr-2" data-toggle="modal" data-target="#editModal" 
                                    data-id="<?php echo $task['id']; ?>" data-name="<?php echo htmlspecialchars($task['task_name']); ?>">
                                Edit
                            </button>
                            <a href="delete.php?id=<?php echo $task['id']; ?>" class="btn btn-sm btn-danger" 
                               onclick="return confirm('Kya aap sure hain ki yeh task delete karna hai?');">
                                Delete
                            </a>
                        </div>
                    </li>
                <?php endforeach; ?>
            <?php endif; ?>
        </ul>
    </div>

    <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Task Edit Karein</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="index.php" method="post">
                    <div class="modal-body">
                        <input type="hidden" name="task_id" id="task-id-input">
                        <div class="form-group">
                            <label for="new-name-input">Naya Task Name:</label>
                            <input type="text" name="new_name" id="new-name-input" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" name="update_task" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> 
    <script src="https://code.jquery.com/ui/1.13.0/jquery-ui.min.js"></script> 
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Modal Data Setup
        $('#editModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var taskId = button.data('id');
            var taskName = button.data('name');
            
            var modal = $(this);
            modal.find('#task-id-input').val(taskId);
            modal.find('#new-name-input').val(taskName);
        });

        // Drag and Drop (Sortable) AJAX Logic
        $(document).ready(function(){
            $("#sortable-list").sortable({ 
                axis: 'y', 
                cursor: 'grabbing',
                update: function (event, ui) {
                    var newOrder = $(this).sortable('toArray', {attribute: 'data-id'}); // data-id values lega
                    
                    $.ajax({
                        type: "POST",
                        url: "update_order.php", 
                        data: { order: newOrder },
                        success: function(response){
                            console.log("Order updated successfully:", response);
                        },
                        error: function(xhr, status, error){
                            console.error("Error updating order.");
                        }
                    });
                }
            });
        });
    </script>
</body>
</html>
