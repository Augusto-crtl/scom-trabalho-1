<?php
// backend/api/bandas/listar.php

header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    require_once '../../config/database.php';

    $usuarioLogadoId = $_SESSION['usuario_id'] ?? 0;

    $pagina = max(1, (int)($_GET['pagina'] ?? 1));
    $limite = max(1, min(20, (int)($_GET['limite'] ?? 6)));
    $offset = ($pagina - 1) * $limite;

    $busca = trim($_GET['busca'] ?? '');
    $subgenero = trim($_GET['subgenero'] ?? '');

    $where = [];
    $params = [];

    if (!empty($busca)) {
        $where[] = '(b.nome_banda LIKE ? OR b.biografia LIKE ?)';
        $params[] = "%{$busca}%";
        $params[] = "%{$busca}%";
    }

    if (!empty($subgenero)) {
        $where[] = 'b.subgenero = ?';
        $params[] = $subgenero;
    }

    $sqlWhere = count($where) > 0 ? 'WHERE ' . implode(' AND ', $where) : '';

    $stmtCount = $pdo->prepare("SELECT COUNT(*) as total FROM bandas b {$sqlWhere}");
    $stmtCount->execute($params);
    $totalRegistros = (int)$stmtCount->fetch()['total'];
    $totalPaginas = ceil($totalRegistros / $limite);

    $sql = "
        SELECT 
            b.id, 
            b.nome_banda, 
            b.subgenero, 
            b.link_media, 
            b.biografia, 
            b.criado_em,
            COUNT(DISTINCT c.id) AS total_hails,
            MAX(CASE WHEN c.usuario_id = ? THEN 1 ELSE 0 END) AS curtido_por_mim
        FROM bandas b
        LEFT JOIN curtidas_bandas c ON b.id = c.banda_id
        {$sqlWhere}
        GROUP BY b.id
        ORDER BY b.id DESC
        LIMIT ? OFFSET ?
    ";

    $stmt = $pdo->prepare($sql);
    $queryParams = array_merge([$usuarioLogadoId], $params, [$limite, $offset]);
    
    foreach ($queryParams as $idx => $val) {
        $stmt->bindValue($idx + 1, $val, is_int($val) ? PDO::PARAM_INT : PDO::PARAM_STR);
    }
    
    $stmt->execute();
    $bandas = $stmt->fetchAll();

    foreach ($bandas as &$banda) {
        $stmtComentarios = $pdo->prepare("
            SELECT cm.id, cm.texto, cm.criado_em, u.nome AS autor 
            FROM comentarios cm
            JOIN usuarios u ON cm.usuario_id = u.id
            WHERE cm.banda_id = ?
            ORDER BY cm.id DESC
            LIMIT 5
        ");
        $stmtComentarios->execute([$banda['id']]);
        $banda['comentarios'] = $stmtComentarios->fetchAll();
    }

    http_response_code(200);
    echo json_encode([
        "status" => 200,
        "bandas" => $bandas,
        "paginacao" => [
            "pagina_atual" => $pagina,
            "total_paginas" => $totalPaginas,
            "total_registros" => $totalRegistros,
            "limite" => $limite
        ]
    ]);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(["status" => 500, "erro" => "Erro ao buscar bandas: " . $e->getMessage()]);
}
