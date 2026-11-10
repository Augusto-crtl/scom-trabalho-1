<?php
// backend/api/auth/logout.php

header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$usuarioId = $_SESSION['usuario_id'] ?? null;

if ($usuarioId) {
    try {
        require_once '../../config/database.php';
        $ipCliente = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $stmtLog = $pdo->prepare('INSERT INTO logs_sistema (nivel, evento, mensagem, ip) VALUES (?, ?, ?, ?)');
        $stmtLog->execute(['INFO', 'LOGOUT', "Usuário ID {$usuarioId} encerrou a sessão.", $ipCliente]);
    } catch (Throwable $e) {
        // Ignora erro secundário de log se o banco falhar no logout
    }
}

// Limpa todas as variáveis de sessão
$_SESSION = array();

// Apaga o cookie da sessão no navegador
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destrói a sessão no servidor
session_destroy();

http_response_code(200);
echo json_encode(["status" => 200, "mensagem" => "Sessão encerrada com sucesso."]);
