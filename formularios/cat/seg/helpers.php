<?php

session_start();

function e($s) {
    return htmlspecialchars($s ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function json_response($data, $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function check_csrf($token) {
    if (empty($_SESSION['csrf_token'])) return false;
    return hash_equals($_SESSION['csrf_token'], (string)$token);
}

function require_post_json() {
    $raw = file_get_contents('php://input');
    $json = json_decode($raw, true);
    if (!is_array($json)) {
        json_response(['success'=>false,'error'=>'JSON inválido'],400);
    }
    return $json;
}

// Generar CSRF (usar en layout o index.php)
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(24));
}
