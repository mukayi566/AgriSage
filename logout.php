<?php
session_start();

// Log logout activity if user was logged in
if (isset($_SESSION['user_id'])) {
    include_once 'config/database.php';
    $database = new Database();
    $db = $database->getConnection();
    
    $activity_query = "INSERT INTO activities (user_id, activity_type, summary) 
                      VALUES (:user_id, 'logout', 'User logged out')";
    $activity_stmt = $db->prepare($activity_query);
    $activity_stmt->bindParam(':user_id', $_SESSION['user_id']);
    $activity_stmt->execute();
}

// Destroy session
session_destroy();

// Redirect to home screen
header("Location: agrisage.php");
exit;
?>