<?php
// backend/api/bandas/curtir.php

header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);
error_reporting(E_ALL);

try {
    require_once '../../config/database.php';
    require_once '../../config/auth_check.php';

    exigirAutenticacao();

    $json = file_get_contents('php://input');
    $dados = json_decode($json, true);
    $bandaId = (int)($dados['banda_id'] ?? 0);
    $usuarioId = $_SESSION['usuario_id'];

    if (!$bandaId) {
        http_response_code(400);
        echo json_encode(["status" => 400, "erro" => "ID da banda inválido."]);
        exit;
    }

    $stmtCheck = $pdo->prepare("SELECT id FROM curtidas_bandas WHERE usuario_id = ? AND banda_id = ?");
    $stmtCheck->execute([$usuarioId, $bandaId]);

    if ($stmtCheck->fetch()) {
        $stmtDel = $pdo->prepare("DELETE FROM curtidas_bandas WHERE usuario_id = ? AND banda_id = ?");
        $stmtDel->execute([$usuarioId, $bandaId]);
        $mensagem = 'Hail removido.';
    } else {
        $stmtAdd = $pdo->prepare("INSERT INTO curtidas_bandas (usuario_id, banda_id) VALUES (?, ?)");
        $stmtAdd->execute([$usuarioId, $bandaId]);
        $mensagem = '🤘 Hail concedido!';
    }

    $stmtCount = $pdo->prepare("SELECT COUNT(*) as total FROM curtidas_bandas WHERE banda_id = ?");
    $stmtCount->execute([$bandaId]);
    $total = (int)$stmtCount->fetch()['total'];

    http_response_code(200);
    echo json_encode(["status" => 200, "mensagem" => $mensagem, "total_hails" => $total]);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(["status" => 500, "erro" => "Erro ao processar Hail: " . $e->getMessage()]);
}
