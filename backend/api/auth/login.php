<?php
// backend/api/auth/login.php

header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    require_once '../../config/database.php';
    require_once '../../config/rate_limit.php';

    $ipCliente = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    verificarRateLimit($pdo, $ipCliente);

    $json = file_get_contents('php://input');
    $dados = json_decode($json, true);

    $email = trim($dados['email'] ?? '');
    $senha = trim($dados['senha'] ?? '');

    if (empty($email) || empty($senha)) {
        http_response_code(400);
        echo json_encode(["status" => 400, "erro" => "E-mail e senha são obrigatórios."]);
        exit;
    }

    $stmt = $pdo->prepare('SELECT * FROM usuarios WHERE email = ?');
    $stmt->execute([$email]);
    $usuario = $stmt->fetch();

    // Busca o hash correto independente do nome da coluna (senha_hash ou senha)
    $hashNoBanco = $usuario['senha_hash'] ?? $usuario['senha'] ?? '';

    if ($usuario && !empty($hashNoBanco) && password_verify($senha, $hashNoBanco)) {
        // Renova o ID da sessão para prevenir Session Fixation
        session_regenerate_id(true);

        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['usuario_nome'] = $usuario['nome'];
        $_SESSION['perfil'] = $usuario['perfil'];

        // Log de sucesso
        $stmtLog = $pdo->prepare('INSERT INTO logs_sistema (nivel, evento, mensagem, ip) VALUES (?, ?, ?, ?)');
        $stmtLog->execute(['INFO', 'LOGIN_SUCESSO', "Usuário ID {$usuario['id']} logou com sucesso.", $ipCliente]);

        http_response_code(200);
        echo json_encode([
            "status" => 200,
            "mensagem" => "Login realizado com sucesso!",
            "usuario" => [
                "id" => $usuario['id'],
                "nome" => $usuario['nome'],
                "perfil" => $usuario['perfil']
            ]
        ]);
    } else {
        // Destruição de sessões residuais em caso de falha
        $_SESSION = array();
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();

        // Log de falha para o Rate Limiting
        $stmtLog = $pdo->prepare('INSERT INTO logs_sistema (nivel, evento, mensagem, ip) VALUES (?, ?, ?, ?)');
        $stmtLog->execute(['WARN', 'FALHA_AUTENTICACAO', "Tentativa de login falhou para o email: {$email}", $ipCliente]);

        http_response_code(401);
        echo json_encode(["status" => 401, "erro" => "Credenciais inválidas."]);
    }

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(["status" => 500, "erro" => "Erro no servidor: " . $e->getMessage()]);
}
