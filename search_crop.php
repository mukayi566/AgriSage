<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

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

$cropName = $_GET['crop_name'] ?? '';

if (empty($cropName)) {
    echo json_encode(["success" => false, "message" => "Crop name is required"]);
    exit;
}

// Search for crop in database
$stmt = $conn->prepare("SELECT * FROM crops WHERE name LIKE ?");
$searchTerm = "%" . $cropName . "%";
$stmt->bind_param("s", $searchTerm);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $crop = $result->fetch_assoc();
    echo json_encode([
        "success" => true,
        "crop_data" => [
            "crop_name" => $crop['name'],
            "yield" => $crop['yield'],
            "height" => $crop['height'],
            "growth_period" => $crop['growth_period'],
            "description" => $crop['description']
        ]
    ]);
} else {
    echo json_encode(["success" => false, "message" => "Crop not found"]);
}

$stmt->close();
$conn->close();
?>