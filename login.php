<?php
// backend/api/auth/login.php

header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);
error_reporting(E_ALL);

try {
    require_once '../../config/database.php';

    $json = file_get_contents('php://input');
    $dados = json_decode($json, true);

    $email = trim($dados['email'] ?? '');
    $senha = $dados['senha'] ?? '';
    $ipCliente = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

    if (empty($email) || empty($senha)) {
        http_response_code(400);
        echo json_encode(["status" => 400, "erro" => "E-mail e senha são obrigatórios."]);
        exit;
    }

    // Prepared Statement contra SQL Injection
    $stmt = $pdo->prepare('SELECT id, nome, email, senha_hash, perfil FROM usuarios WHERE email = ?');
    $stmt->execute([$email]);
    $usuario = $stmt->fetch();

    // Validação da senha com password_verify
    if (!$usuario || !password_verify($senha, $usuario['senha_hash'])) {
        // Log de falha de autenticação (Requisito de Observabilidade)
        $stmtLog = $pdo->prepare('INSERT INTO logs_sistema (nivel, evento, mensagem, ip) VALUES (?, ?, ?, ?)');
        $stmtLog->execute(['WARN', 'FALHA_AUTENTICACAO', "Tentativa de login frustrada para o e-mail: {$email}", $ipCliente]);

        http_response_code(401);
        echo json_encode(["status" => 401, "erro" => "E-mail ou senha incorretos."]);
        exit;
    }

    // Configuração de sessão segura
    if (session_status() === PHP_SESSION_NONE) {
        session_start([
            'cookie_httponly' => true,
            'cookie_samesite' => 'Lax'
        ]);
    }

    // Prevenção contra fixação de sessão
    session_regenerate_id(true);

    // Grava dados do usuário na sessão
    $_SESSION['usuario_id'] = $usuario['id'];
    $_SESSION['nome']       = $usuario['nome'];
    $_SESSION['email']      = $usuario['email'];
    $_SESSION['perfil']     = $usuario['perfil'];

    // Log de sucesso de autenticação
    $stmtLog = $pdo->prepare('INSERT INTO logs_sistema (nivel, evento, mensagem, ip) VALUES (?, ?, ?, ?)');
    $stmtLog->execute(['INFO', 'LOGIN_SUCESSO', "Usuário ID {$usuario['id']} ({$usuario['perfil']}) realizou login.", $ipCliente]);

    http_response_code(200);
    echo json_encode([
        "status" => 200,
        "mensagem" => "Login realizado com sucesso!",
        "usuario" => [
            "id" => $usuario['id'],
            "nome" => $usuario['nome'],
            "email" => $usuario['email'],
            "perfil" => $usuario['perfil']
        ]
    ]);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(["status" => 500, "erro" => "Erro interno no servidor: " . $e->getMessage()]);
}
