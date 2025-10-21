<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Include database configuration
include_once '../config/database.php';

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Get posted data
$data = json_decode(file_get_contents("php://input"));

// Validate input
if (!empty($data->email) && !empty($data->password)) {
    
    $query = "SELECT user_id, name, email, password FROM users WHERE email = :email LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":email", $data->email);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $user_id = $row['user_id'];
        $name = $row['name'];
        $email = $row['email'];
        $hashed_password = $row['password'];

        if (password_verify($data->password, $hashed_password)) {
            // Log login activity
            $activity_query = "INSERT INTO activities (user_id, activity_type, summary) 
                              VALUES (:user_id, 'mobile_login', 'User logged in via mobile app')";
            $activity_stmt = $db->prepare($activity_query);
            $activity_stmt->bindParam(':user_id', $user_id);
            $activity_stmt->execute();

            echo json_encode([
                "success" => true,
                "message" => "Login successful",
                "user" => [
                    "user_id" => $user_id,
                    "name" => $name,
                    "email" => $email
                ]
            ]);
        } else {
            echo json_encode([
                "success" => false,
                "message" => "Invalid password"
            ]);
        }
    } else {
        echo json_encode([
            "success" => false,
            "message" => "User not found"
        ]);
    }
} else {
    echo json_encode([
        "success" => false,
        "message" => "Email and password are required"
    ]);
}
?>