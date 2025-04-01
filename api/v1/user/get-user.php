<?php
// get-user.php

require_once __DIR__ . '/../../../db.php'; // Shared DB connection
require_once __DIR__ . '/../../../helpers/jwt_required.php';
require_once __DIR__ . '/../../../helpers/cors.php';
handleCors();

// Function to fetch user details from the database
function get_user_info($user_id) {
    global $conn;
    $sql = "SELECT id, email, username, status FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->num_rows > 0 ? $result->fetch_assoc() : null;
}

// Protect the route with jwt_required middleware
$user_data = jwt_required();

// Validate the token data
if (!isset($user_data["user_id"])) {
    http_response_code(401); // Unauthorized
    echo json_encode(['error' => 'Invalid token or user_id missing']);
    exit;
}

// Fetch user data from DB
$user_info = get_user_info($user_data["user_id"]);

// Set response headers
header('Content-Type: application/json');

if ($user_info) {
    http_response_code(200); // Success
    echo json_encode([
        'id' => $user_info['id'],
        'username' => $user_info['username'],
        'email' => $user_info['email'],
        'status' => $user_info['status']
    ]);
} else {
    http_response_code(404); // Not Found
    echo json_encode(['error' => 'User not found']);
}
?>
