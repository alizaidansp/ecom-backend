<?php
function handleCors() {
    $allowed_origins = [
        'http://localhost:5173',    #local-dev   'npm run dev
        'http://0.0.0.0:3000',      #docker-local 'docker run --name lamp-front-container  -p 3000:3000 lamp-front'            
        'http://localhost:3000',      #docker-local 'docker run --name lamp-front-container  -p 3000:3000 lamp-front'            
        getenv('FRONTEND_URL') ?: ''                 // Dynamic ECS origin
    ];

    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';

    if (in_array($origin, $allowed_origins)) {
        header("Access-Control-Allow-Origin: $origin");
    }

    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");
    header("Access-Control-Allow-Credentials: true");

    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(204);
        exit();
    }
}
