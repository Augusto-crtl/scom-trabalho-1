<?php
// backend/api/auth/register.php

header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);
error_reporting(E_ALL);

try {
    require_once '../../config/database.php';

    $json = file_get_contents('php://input');
    $dados = json_decode($json, true);

    if (!$dados) {
        http_response_code(400);
        echo json_encode(["status" => 400, "erro" => "Payload JSON inválido ou vazio."]);
        exit;
    }

    $nome      = trim($dados['fname'] ?? '');
    $email     = trim($dados['email'] ?? '');
    $senha     = $dados['senha'] ?? '';
    $genero    = $dados['genero_id'] ?? '';
    $perfil    = $dados['tipo_perfil'] ?? 'headbanger';
    $novidades = !empty($dados['novidades']) ? 1 : 0;

    if (empty($nome) || empty($email) || empty($senha) || empty($genero)) {
        http_response_code(400);
        echo json_encode(["status" => 400, "erro" => "Preencha todos os campos obrigatórios."]);
        exit;
    }

    // 1. Verificar e-mail duplicado
    $stmt = $pdo->prepare('SELECT id FROM usuarios WHERE email = ?');
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        http_response_code(409);
        echo json_encode(["status" => 409, "erro" => "Este e-mail já está cadastrado no fórum."]);
        exit;
    }

    // 2. Hash de senha
    $senhaHash = password_hash($senha, PASSWORD_BCRYPT);

    // 3. Inserção na tabela principal
    $stmt = $pdo->prepare('INSERT INTO usuarios (nome, email, senha_hash, genero, perfil, receber_novidades) VALUES (?, ?, ?, ?, ?, ?)');
    $stmt->execute([$nome, $email, $senhaHash, $genero, $perfil, $novidades]);
    $usuarioId = $pdo->lastInsertId();

    // 4. Inserção na tabela secundária conforme o perfil
    if ($perfil === 'headbanger') {
        $bandaFav = trim($dados['banda_favorita'] ?? '');
        $stmtPerfil = $pdo->prepare('INSERT INTO perfis_headbanger (usuario_id, banda_favorita) VALUES (?, ?)');
        $stmtPerfil->execute([$usuarioId, $bandaFav]);
    } else if ($perfil === 'banda') {
        $nomeBanda = trim($dados['nome_banda'] ?? '');
        $subgenero = $dados['subgenero_banda'] ?? 'heavy';
        $linkMedia = trim($dados['link_media'] ?? '');

        $stmtBanda = $pdo->prepare('INSERT INTO bandas (usuario_id, nome_banda, subgenero, link_media) VALUES (?, ?, ?, ?)');
        $stmtBanda->execute([$usuarioId, !empty($nomeBanda) ? $nomeBanda : $nome, $subgenero, $linkMedia]);
    }

    // 5. Registro de log de auditoria
    $ipCliente = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $stmtLog = $pdo->prepare('INSERT INTO logs_sistema (nivel, evento, mensagem, ip) VALUES (?, ?, ?, ?)');
    $stmtLog->execute(['INFO', 'CRIACAO_ENTIDADE', "Usuário ID {$usuarioId} ({$perfil}) cadastrado.", $ipCliente]);

    http_response_code(201);
    echo json_encode(["status" => 201, "mensagem" => "Cadastro efetuado com sucesso! Bem-vindo à aliança."]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["status" => 500, "erro" => "Erro no banco de dados: " . $e->getMessage()]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(["status" => 500, "erro" => "Erro interno no servidor: " . $e->getMessage()]);
}
