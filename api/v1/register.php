<?php
require_once __DIR__ . '/../../db.php';// Include the shared DB connection
require_once __DIR__ . '/../../helpers/token.php';
require_once __DIR__ . '/../../helpers/password.php';

require_once __DIR__ . '/../../helpers/cors.php';
handleCors();
// Check request method
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    // Validate input
    if (!isset($data['email'], $data['password'])) {
        http_response_code(400); // Bad Request
        echo json_encode(['error' => 'Email and password are required']);
        exit;
    }

    $email = trim($data['email']);
    $password = trim($data['password']);
    $username = trim($data['username']);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400); // Bad Request
        echo json_encode(['error' => 'Invalid email format']);
        exit;
    }

   // Validate password strength
    if (!isValidPassword($password)) {
        http_response_code(400); // Bad Request
        echo json_encode([
            'error' => 'Password must be at least 8 characters long and include an uppercase letter, lowercase letter, number, and special character (!@#$%^&*()-_).'
        ]);
        exit;
    }

    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        http_response_code(409); // Conflict
        echo json_encode(['error' => 'Email already registered']);
        exit;
    }

    $stmt->close();

    // Hash password and store user
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (email, password, username) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $email, $hashedPassword, $username);

    if ($stmt->execute()) {
        $userId = $stmt->insert_id;
        $stmt->close();

        $jwt_secret = getenv('JWT_SECRET') ?: 'keyboardcat';

        // Generate JWT
        $token = generateJWT(["user_id" => $userId, "email" => $email], $jwt_secret);

        http_response_code(201); // Created
        echo json_encode([
            'success' => true,
            'user' => [
                'id' => $userId,
                'username' => $username,
                'email' => $email
            ],
            'access_token' => $token
        ]);
    } else {
        http_response_code(500); // Internal Server Error
        echo json_encode(['error' => 'Registration failed']);
    }
} 

 elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Handle GET request (Fetch users)
    $result = $conn->query("SELECT id, email FROM users");
    $users = $result->fetch_all(MYSQLI_ASSOC);

    echo json_encode($users);
} else {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['error' => 'Only POST method is allowed']);
}


$conn->close();
?>
