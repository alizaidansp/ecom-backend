<?php
// helpers/jwt_required.php

require_once 'token.php'; // This includes the validate_jwt() function

// Middleware-like function to require JWT token in request
function jwt_required() {
    // Get headers from the request
    $headers = apache_request_headers();

    if (!isset($headers['Authorization'])) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Authorization token is required']);
        exit();  // Terminate the request if no token is found
    }

    $token = str_replace('Bearer ', '', $headers['Authorization']);
    $jwt_secret = getenv('JWT_SECRET') ?: 'keyboardcat';


    try {
        // Validate JWT Token and return the decoded user data
        return validateJWT($token,$jwt_secret);  // This will throw an exception if the token is invalid or expired
    } catch (Exception $e) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Invalid or expired token: ' . $e->getMessage()]);
        exit();  // Terminate the request if the token is invalid
    }
}
?>
