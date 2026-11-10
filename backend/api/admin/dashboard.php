<?php
// backend/api/admin/dashboard.php

header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);
error_reporting(E_ALL);

try {
    require_once '../../config/database.php';
    require_once '../../config/auth_check.php';

    exigirPerfil(['admin']);

    $totalUsuarios = $pdo->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
    $totalFas      = $pdo->query("SELECT COUNT(*) FROM perfis_headbanger")->fetchColumn();
    $totalBandas   = $pdo->query("SELECT COUNT(*) FROM bandas")->fetchColumn();
    $totalHails    = $pdo->query("SELECT COUNT(*) FROM curtidas_bandas")->fetchColumn();

    try {
        $logs = $pdo->query("SELECT id, nivel, evento, mensagem, ip, criado_em AS data_hora FROM logs_sistema ORDER BY id DESC LIMIT 15")->fetchAll();
    } catch (PDOException $e) {
        $logs = $pdo->query("SELECT id, nivel, evento, mensagem, ip, data_hora FROM logs_sistema ORDER BY id DESC LIMIT 15")->fetchAll();
    }

    http_response_code(200);
    echo json_encode([
        "status" => 200,
        "metricas" => [
            "usuarios" => (int)$totalUsuarios,
            "fas" => (int)$totalFas,
            "bandas" => (int)$totalBandas,
            "hails" => (int)$totalHails
        ],
        "logs" => $logs
    ]);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(["status" => 500, "erro" => "Erro no painel administrativo: " . $e->getMessage()]);
}
