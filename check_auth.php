<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

session_start();

if (isset($_SESSION['user_id'])) {
    echo json_encode([
        "success" => true,
        "user" => [
            "user_id" => $_SESSION['user_id'],
            "name" => $_SESSION['name'],
            "email" => $_SESSION['email']
        ]
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Not authenticated"
    ]);
}
?>