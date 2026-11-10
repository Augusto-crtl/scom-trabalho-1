<?php
// backend/api/auth/me.php

header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(["status" => 401, "autenticado" => false, "erro" => "Nenhum usuário autenticado."]);
    exit;
}

http_response_code(200);
echo json_encode([
    "status" => 200,
    "autenticado" => true,
    "usuario" => [
        "id" => $_SESSION['usuario_id'],
        "nome" => $_SESSION['nome'],
        "email" => $_SESSION['email'],
        "perfil" => $_SESSION['perfil']
    ]
]);
