<?php
// backend/api/bandas/salvar.php

header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);
error_reporting(E_ALL);

try {
    require_once '../../config/database.php';
    require_once '../../config/auth_check.php';

    // Restrição RBAC: Apenas perfis autorizados
    exigirPerfil(['banda', 'admin']);

    $json = file_get_contents('php://input');
    $dados = json_decode($json, true);

    $usuarioId = $_SESSION['usuario_id'];
    $nomeBanda = trim($dados['nome_banda'] ?? '');
    $subgenero = $dados['subgenero_banda'] ?? 'heavy';
    $linkMedia = trim($dados['link_media'] ?? '');
    $biografia = trim($dados['biografia'] ?? '');

    if (empty($nomeBanda)) {
        http_response_code(400);
        echo json_encode(["status" => 400, "erro" => "O nome da banda é obrigatório."]);
        exit;
    }

    // Atualiza os dados da banda no banco
    $stmt = $pdo->prepare('UPDATE bandas SET nome_banda = ?, subgenero = ?, link_media = ?, biografia = ? WHERE usuario_id = ?');
    $stmt->execute([$nomeBanda, $subgenero, $linkMedia, $biografia, $usuarioId]);

    // Log de observabilidade
    $ipCliente = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $stmtLog = $pdo->prepare('INSERT INTO logs_sistema (nivel, evento, mensagem, ip) VALUES (?, ?, ?, ?)');
    $stmtLog->execute(['INFO', 'ATUALIZACAO_ENTIDADE', "Página da Banda ID (Usuário {$usuarioId}) foi atualizada.", $ipCliente]);

    http_response_code(200);
    echo json_encode(["status" => 200, "mensagem" => "Página da banda atualizada com sucesso!"]);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(["status" => 500, "erro" => "Erro no servidor: " . $e->getMessage()]);
}
