<?php
// backend/config/auth_check.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function exigirAutenticacao() {
    if (!isset($_SESSION['usuario_id'])) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(401);
        echo json_encode(["status" => 401, "erro" => "Acesso negado. Faça login para continuar."]);
        exit;
    }
}

function exigirPerfil(array $perfisPermitidos) {
    exigirAutenticacao();
    if (!in_array($_SESSION['perfil'], $perfisPermitidos)) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(403);
        echo json_encode(["status" => 403, "erro" => "Acesso negado. Perfil de usuário sem autorização."]);
        exit;
    }
}
