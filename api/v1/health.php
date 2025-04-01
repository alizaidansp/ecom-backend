<?php
require_once __DIR__ . '/../../helpers/cors.php';
require_once __DIR__ . '/../../db.php'; // Include the database connection

handleCors(); // Handle CORS

header("Content-Type: application/json");

$response = [
    "status" => "ok",
    "timestamp" => date("Y-m-d H:i:s"),
];

// Check if the database connection is alive
if ($conn->ping()) {
    $response["db_status"] = "connected";
} else {
    http_response_code(500); // Mark as unhealthy if DB is down
    $response["db_status"] = "down";
}

echo json_encode($response);
?>
