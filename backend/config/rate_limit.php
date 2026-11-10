<?php
// backend/config/rate_limit.php

function verificarRateLimit(PDO $pdo, string $ip, int $maxTentativas = 5, int $minutos = 15) {
    try {
        // Identifica dinamicamente o nome da coluna de timestamp na tabela logs_sistema
        $colunaData = null;
        $stmtCols = $pdo->query("SHOW COLUMNS FROM logs_sistema");
        $colunas = $stmtCols->fetchAll(PDO::FETCH_COLUMN);

        if (in_array('criado_em', $colunas)) {
            $colunaData = 'criado_em';
        } elseif (in_array('data_hora', $colunas)) {
            $colunaData = 'data_hora';
        } elseif (in_array('created_at', $colunas)) {
            $colunaData = 'created_at';
        }

        // Se encontrou a coluna de data, faz a checagem por IP e tempo
        if ($colunaData) {
            $sql = "SELECT COUNT(*) as total 
                    FROM logs_sistema 
                    WHERE evento = 'FALHA_AUTENTICACAO' 
                      AND ip = ? 
                      AND {$colunaData} >= NOW() - INTERVAL ? MINUTE";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([$ip, $minutos]);
            $resultado = $stmt->fetch();

            if ($resultado && (int)$resultado['total'] >= $maxTentativas) {
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(429);
                echo json_encode([
                    "status" => 429,
                    "erro" => "Muitas tentativas incorretas. Seu IP foi temporariamente bloqueado por {$minutos} minutos."
                ]);
                exit;
            }
        }
    } catch (Throwable $e) {
        // Evita erro 500 no login caso a tabela de logs ainda não tenha registros de tempo
        error_log("Aviso no Rate Limiter: " . $e->getMessage());
    }
}
