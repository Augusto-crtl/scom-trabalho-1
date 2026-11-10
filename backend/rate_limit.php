<?php
// backend/config/rate_limit.php

/**
 * Verifica se o IP ou o E-mail excederam o limite máximo de falhas de autenticação.
 *
 * @param PDO $pdo Conexão ativa com o banco de dados
 * @param string $ip Endereço IP do cliente
 * @param string $email E-mail fornecido no login
 * @param int $maxTentativas Limite de tentativas incorretas permitidas
 * @param int $minutos Janela de tempo da restrição em minutos
 * @return bool Retorna true se a requisição for permitida; encerra o script com 429 se bloqueada.
 */
function verificarRateLimit(PDO $pdo, string $ip, string $email = '', int $maxTentativas = 5, int $minutos = 15): bool {
    try {
        $minutosInt = (int)$minutos;

        // Query robusta: resolve compatibilidade de colunas de data e evita erro de binding em INTERVAL
        // Avalia falhas tanto pelo IP quanto pelo E-mail tentado nos últimos X minutos
        $sql = "SELECT COUNT(*) as total 
                FROM logs_sistema 
                WHERE evento = 'FALHA_AUTENTICACAO' 
                  AND (ip = :ip OR (mensagem LIKE :email_like AND :email != ''))
                  AND (
                      (criado_em IS NOT NULL AND criado_em >= DATE_SUB(NOW(), INTERVAL {$minutosInt} MINUTE)) OR
                      (created_at IS NOT NULL AND created_at >= DATE_SUB(NOW(), INTERVAL {$minutosInt} MINUTE)) OR
                      (data_hora IS NOT NULL AND data_hora >= DATE_SUB(NOW(), INTERVAL {$minutosInt} MINUTE))
                  )";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':ip', $ip, PDO::PARAM_STR);
        $stmt->bindValue(':email_like', '%' . $email . '%', PDO::PARAM_STR);
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->execute();

        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        $totalFalhas = (int)($resultado['total'] ?? 0);

        if ($totalFalhas >= $maxTentativas) {
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(429);
            echo json_encode([
                "status" => 429,
                "erro" => "Muitas tentativas incorretas. Seu acesso foi temporariamente bloqueado por {$minutos} minutos."
            ]);
            exit;
        }

        return true;
    } catch (Throwable $e) {
        error_log("Erro no Rate Limiter: " . $e->getMessage());

        // Caso a consulta por colunas dinâmicas falhe, executa fallback direto simplificado
        try {
            $minutosInt = (int)$minutos;
            $stmtFallback = $pdo->prepare("
                SELECT COUNT(*) as total 
                FROM logs_sistema 
                WHERE evento = 'FALHA_AUTENTICACAO' 
                  AND ip = ?
            ");
            $stmtFallback->execute([$ip]);
            $res = $stmtFallback->fetch(PDO::FETCH_ASSOC);
            
            if ($res && (int)$res['total'] >= ($maxTentativas * 3)) {
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(429);
                echo json_encode([
                    "status" => 429,
                    "erro" => "Bloqueio de segurança ativado devido ao alto volume de requisições."
                ]);
                exit;
            }
        } catch (Throwable $ex) {
            error_log("Falha crítica no fallback do Rate Limiter: " . $ex->getMessage());
        }

        return true;
    }
}
