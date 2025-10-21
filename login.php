<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Include database and user class
include_once 'config/database.php';
include_once 'models/User.php';

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Initialize user object
$user = new User($db);

// Get posted data
$data = json_decode(file_get_contents("php://input"));

// Validate input
if (!empty($data->email) && !empty($data->password)) {
    
    $user->email = $data->email;
    $email_exists = $user->emailExists();

    if ($email_exists && password_verify($data->password, $user->password)) {
        // Start session (you can also use JWT tokens for stateless auth)
        session_start();
        $_SESSION['user_id'] = $user->user_id;
        $_SESSION['name'] = $user->name;
        $_SESSION['email'] = $user->email;

        // Log login activity
        $activity_query = "INSERT INTO activities (user_id, activity_type, summary) 
                          VALUES (:user_id, 'login', 'User logged in successfully')";
        $activity_stmt = $db->prepare($activity_query);
        $activity_stmt->bindParam(':user_id', $user->user_id);
        $activity_stmt->execute();

        echo json_encode([
            "success" => true,
            "message" => "Login successful",
            "user" => [
                "user_id" => $user->user_id,
                "name" => $user->name,
                "email" => $user->email
            ]
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Invalid email or password"
        ]);
    }
} else {
    echo json_encode([
        "success" => false,
        "message" => "Email and password are required"
    ]);
}
?>