<?php
function base64UrlEncode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function base64UrlDecode($data) {
    return base64_decode(strtr($data, '-_', '+/'));
}

function generateJWT($payload, $secret, $expiry = 7200) {
    // 7200 -> 2hrs
    $header = json_encode(['alg' => 'HS256', 'typ' => 'JWT']);

    $payload['exp'] = time() + $expiry; // Add expiration
    $payload = json_encode($payload);

    $base64Header = base64UrlEncode($header);
    $base64Payload = base64UrlEncode($payload);

    $signature = hash_hmac('sha256', "$base64Header.$base64Payload", $secret, true);
    $base64Signature = base64UrlEncode($signature);

    return "$base64Header.$base64Payload.$base64Signature";
}

function validateJWT($token, $secret) {
    $parts = explode('.', $token);
    if (count($parts) !== 3) {
        return false;
    }

    list($base64Header, $base64Payload, $base64Signature) = $parts;

    $signature = base64UrlEncode(hash_hmac('sha256', "$base64Header.$base64Payload", $secret, true));

    if (!hash_equals($signature, $base64Signature)) {
        return false; // Invalid signature
    }

    $payload = json_decode(base64UrlDecode($base64Payload), true);

    if ($payload['exp'] < time()) {
        return false; // Token expired
    }

    return $payload; // Valid payload
}
