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

    // Obtém o IP considerando proxies configurados
    $ipCliente = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    if (strpos($ipCliente, ',') !== false) {
        $ipCliente = trim(explode(',', $ipCliente)[0]);
    }

    $json = file_get_contents('php://input');
    $dados = json_decode($json, true) ?? [];

    $email = trim($dados['email'] ?? '');
    $senha = trim($dados['senha'] ?? '');

    if (empty($email) || empty($senha)) {
        http_response_code(400);
        echo json_encode(["status" => 400, "erro" => "E-mail e senha são obrigatórios."]);
        exit;
    }

    // 1. RATE LIMIT: Checa por IP + E-mail e interrompe com HTTP 429 se bloqueado
    if (!verificarRateLimit($pdo, $ipCliente, $email)) {
        http_response_code(429);
        echo json_encode(["status" => 429, "erro" => "Muitas tentativas de login. Tente novamente mais tarde."]);
        exit;
    }

    $stmt = $pdo->prepare('SELECT * FROM usuarios WHERE email = ?');
    $stmt->execute([$email]);
    $usuario = $stmt->fetch();

    $hashNoBanco = $usuario['senha_hash'] ?? $usuario['senha'] ?? '';
    
    // Hash Falso fixo para evitar Timing Attack quando o e-mail não existir
    $dummyHash = '$2y$10$abcdefghijklmnopqrstuuOU2N38k3ABy8Uve/w2I69Iq3z913/S6'; 
    $hashParaValidar = !empty($hashNoBanco) ? $hashNoBanco : $dummyHash;

    $senhaValida = password_verify($senha, $hashParaValidar);

    if ($usuario && $senhaValida) {
        session_regenerate_id(true);

        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['usuario_nome'] = $usuario['nome'];
        $_SESSION['perfil'] = $usuario['perfil'];

        // Reseta as tentativas com falha em caso de sucesso
        resetarTentativas($pdo, $ipCliente, $email);

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
        // Registra a falha no banco de dados imediatamente
        registrarFalhaLogin($pdo, $ipCliente, $email);

        $_SESSION = array();
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();

        $stmtLog = $pdo->prepare('INSERT INTO logs_sistema (nivel, evento, mensagem, ip) VALUES (?, ?, ?, ?)');
        $stmtLog->execute(['WARN', 'FALHA_AUTENTICACAO', "Tentativa de login falhou para o email: {$email}", $ipCliente]);

        http_response_code(401);
        echo json_encode(["status" => 401, "erro" => "Credenciais inválidas."]);
    }

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(["status" => 500, "erro" => "Erro no servidor: " . $e->getMessage()]);
}
