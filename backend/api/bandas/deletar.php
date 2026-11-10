<?php
// backend/api/bandas/deletar.php
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);
error_reporting(E_ALL);

try {
    require_once '../../config/database.php';
    require_once '../../config/auth_check.php';

    exigirPerfil(['banda', 'admin']);
    $usuarioId = $_SESSION['usuario_id'];

    $stmt = $pdo->prepare('DELETE FROM bandas WHERE usuario_id = ?');
    $stmt->execute([$usuarioId]);

    $ipCliente = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $stmtLog = $pdo->prepare('INSERT INTO logs_sistema (nivel, evento, mensagem, ip) VALUES (?, ?, ?, ?)');
    $stmtLog->execute(['WARN', 'EXCLUSAO_ENTIDADE', "Banda vinculada ao usuário ID {$usuarioId} foi removida.", $ipCliente]);

    http_response_code(200);
    echo json_encode(["status" => 200, "mensagem" => "Registro da banda excluído com sucesso."]);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(["status" => 500, "erro" => "Erro ao excluir: " . $e->getMessage()]);
}
