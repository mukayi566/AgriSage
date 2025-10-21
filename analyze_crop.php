<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Database configuration
$host = "localhost";
$username = "root";
$password = "";
$database = "agrisage_db";

// Create connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die(json_encode(["success" => false, "message" => "Database connection failed"]));
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
$imageBase64 = $input['image'];

// For now, we'll use a simple image analysis simulation
// In production, integrate with TensorFlow, PyTorch, or ML model

// Simulate crop detection and disease analysis
$analysisResult = analyzeCropImage($imageBase64);

if ($analysisResult) {
    echo json_encode([
        "success" => true,
        "crop_data" => $analysisResult
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Could not analyze the image"
    ]);
}

function analyzeCropImage($imageBase64) {
    // This is a simulation - replace with actual ML model integration
    
    // For demo purposes, randomly select a crop and condition
    $crops = [
        [
            "crop_name" => "Maize",
            "disease" => "Healthy",
            "confidence" => "92",
            "description" => "Maize plant showing healthy growth with proper leaf development.",
            "treatment" => "Continue current care. Ensure proper watering and fertilization.",
            "growing_conditions" => "Full sun, Well-drained soil, Regular watering"
        ],
        [
            "crop_name" => "Maize",
            "disease" => "Northern Leaf Blight",
            "confidence" => "87",
            "description" => "Elliptical gray-green lesions on leaves that turn brown with age.",
            "treatment" => "Apply fungicide. Remove infected leaves. Rotate crops.",
            "growing_conditions" => "Full sun, Well-drained soil"
        ],
        [
            "crop_name" => "Cassava",
            "disease" => "Healthy",
            "confidence" => "95",
            "description" => "Cassava plant with healthy tuber development and green leaves.",
            "treatment" => "Maintain current care practices.",
            "growing_conditions" => "Warm climate, Sandy soil, Moderate watering"
        ]
    ];
    
    return $crops[array_rand($crops)];
}

$conn->close();
?>