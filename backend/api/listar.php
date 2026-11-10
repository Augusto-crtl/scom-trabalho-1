<?php
// backend/api/bandas/listar.php

header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);
error_reporting(E_ALL);

try {
    require_once '../../config/database.php';

    $stmt = $pdo->query('SELECT id, nome_banda, subgenero, link_media, biografia, criado_em FROM bandas ORDER BY id DESC');
    $bandas = $stmt->fetchAll();

    http_response_code(200);
    echo json_encode([
        "status" => 200,
        "bandas" => $bandas
    ]);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(["status" => 500, "erro" => "Erro ao buscar bandas: " . $e->getMessage()]);
}
