<?php
// backend/api/bandas/minha_banda.php

header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);
error_reporting(E_ALL);

try {
    require_once '../../config/database.php';
    require_once '../../config/auth_check.php';

    // Garante que apenas usuários autenticados com perfil 'banda' ou 'admin' acessem
    exigirPerfil(['banda', 'admin']);

    $usuarioId = $_SESSION['usuario_id'];

    $stmt = $pdo->prepare('SELECT * FROM bandas WHERE usuario_id = ?');
    $stmt->execute([$usuarioId]);
    $banda = $stmt->fetch();

    if (!$banda) {
        http_response_code(404);
        echo json_encode(["status" => 404, "erro" => "Perfil de banda não encontrado."]);
        exit;
    }

    http_response_code(200);
    echo json_encode([
        "status" => 200,
        "banda" => $banda
    ]);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(["status" => 500, "erro" => "Erro no servidor: " . $e->getMessage()]);
}
