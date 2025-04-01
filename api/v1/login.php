<?php
require_once __DIR__ . '/../../db.php'; // Shared DB connection
require_once __DIR__ . '/../../helpers/token.php';
require_once __DIR__ . '/../../helpers/cors.php';
handleCors();
// Ensure POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['error' => 'Only POST requests are allowed']);
    exit;
}

// Parse incoming JSON
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['email'], $data['password'])) {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'Email and password are required']);
    exit;
}

$email = trim($data['email']);
$password = $data['password'];

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'Invalid email format']);
    exit;
}

// Retrieve user from database
$stmt = $conn->prepare("SELECT id, password FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(401); // Unauthorized
    echo json_encode(['error' => 'Invalid credentials']);
    exit;
}

$user = $result->fetch_assoc();

// Verify password
if (!password_verify($password, $user['password'])) {
    http_response_code(401); // Unauthorized
    echo json_encode(['error' => 'Invalid credentials']);
    exit;
}

$stmt->close();

// Generate JWT token
$jwt_secret = getenv('JWT_SECRET') ?: 'keyboardcat';
$token = generateJWT(['user_id' => $user['id'], 'email' => $email], $jwt_secret);

// Successful login
http_response_code(200); // OK
echo json_encode([
    'success' => true,
    'user' => [
        'id' => $user['id'],
        'email' => $email
    ],
    'access_token' => $token
]);

$conn->close();
?>
