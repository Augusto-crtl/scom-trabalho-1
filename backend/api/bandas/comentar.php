<?php
// backend/api/bandas/comentar.php

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
    $texto = trim($dados['texto'] ?? '');

    if (!$bandaId || empty($texto)) {
        http_response_code(400);
        echo json_encode(["status" => 400, "erro" => "O comentário não pode estar em branco."]);
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO comentarios (usuario_id, banda_id, texto) VALUES (?, ?, ?)");
    $stmt->execute([$_SESSION['usuario_id'], $bandaId, $texto]);

    http_response_code(201);
    echo json_encode(["status" => 201, "mensagem" => "Comentário publicado!"]);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(["status" => 500, "erro" => "Erro ao comentar: " . $e->getMessage()]);
}
