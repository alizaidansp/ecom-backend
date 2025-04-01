<?php
// backend/api/v1/migrate.php
require_once __DIR__ . '/../../helpers/cors.php';
require_once __DIR__ . '/../../db.php';

handleCors();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die(json_encode(["error" => "Method Not Allowed"]));
}

// // IP Whitelist (only allow from VPC CIDR)
// $allowed_ips = ['10.0.0.0/16']; 
// if (!in_array($_SERVER['REMOTE_ADDR'], $allowed_ips, true)) {
//     http_response_code(403);
//     die(json_encode(["error" => "Forbidden"]));
// }


header("Content-Type: application/json");

$response = [];
$migrationFiles = glob(__DIR__ . '/../../migrations/*.sql');

try {
    $conn->begin_transaction();
    
    foreach ($migrationFiles as $file) {
        $sql = file_get_contents($file);
        $conn->multi_query($sql);
        $response['migrations'][] = basename($file);
        
        // Clear multi-query results
        while ($conn->more_results()) {
            $conn->next_result();
        }
    }
    
    $conn->commit();
    http_response_code(200);
} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    $response['error'] = $e->getMessage();
}

echo json_encode($response);
?>
