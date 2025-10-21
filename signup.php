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
if (!empty($data->name) && !empty($data->email) && !empty($data->password)) {
    
    // Validate password length
    if (strlen($data->password) < 6) {
        echo json_encode([
            "success" => false,
            "message" => "Password must be at least 6 characters long"
        ]);
        exit;
    }

    // Set user properties
    $user->name = $data->name;
    $user->email = $data->email;
    $user->password = $data->password;

    // Check if email already exists
    if ($user->emailExists()) {
        echo json_encode([
            "success" => false,
            "message" => "Email already registered"
        ]);
        exit;
    }

    // Create user
    if ($user->create()) {
        // Start session
        session_start();
        $_SESSION['user_id'] = $user->user_id;
        $_SESSION['name'] = $user->name;
        $_SESSION['email'] = $user->email;

        // Log signup activity
        $activity_query = "INSERT INTO activities (user_id, activity_type, summary) 
                          VALUES (:user_id, 'signup', 'New user registered')";
        $activity_stmt = $db->prepare($activity_query);
        $activity_stmt->bindParam(':user_id', $user->user_id);
        $activity_stmt->execute();

        echo json_encode([
            "success" => true,
            "message" => "User registered successfully",
            "user" => [
                "user_id" => $user->user_id,
                "name" => $user->name,
                "email" => $user->email
            ]
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Unable to register user"
        ]);
    }
} else {
    echo json_encode([
        "success" => false,
        "message" => "All fields are required"
    ]);
}
?>