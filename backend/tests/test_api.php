<?php
// backend/tests/test_api.php - Executar via terminal: php backend/tests/test_api.php

echo "========================================================\n";
echo "   SUÍTE DE TESTES AUTOMATIZADOS - METAL ARCHIVES API   \n";
echo "========================================================\n\n";

$baseUrl = "http://localhost/metal-archives/backend/api";
$testesPassaram = 0;
$totalTestes = 5;

function rodarTeste($nome, $condicao) {
    global $testesPassaram;
    if ($condicao) {
        echo "[ PASS ] $nome\n";
        $testesPassaram++;
    } else {
        echo "[ FAIL ] $nome\n";
    }
}

// Teste 1: Conexão com o Banco de Dados
require_once __DIR__ . '/../config/database.php';
rodarTeste("1. Conexão PDO com MySQL estabelecida com sucesso", isset($pdo));

// Teste 2: Rejeitar Registro com E-mail em Branco (Inválido)
$ch = curl_init("$baseUrl/auth/register.php");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(["fname" => "Teste", "email" => ""]));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$res = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
rodarTeste("2. Validação Backend rejeita e-mail em branco (HTTP 400)", $code === 400);

// Teste 3: Tentativa de Login com Credenciais Inexistentes (Inválido)
$ch = curl_init("$baseUrl/auth/login.php");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(["email" => "inexistente@test.com", "senha" => "12345678"]));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$res = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
rodarTeste("3. Bloqueio de login com credenciais incorretas (HTTP 401)", $code === 401);

// Teste 4: Listagem Pública de Bandas (Válido)
$ch = curl_init("$baseUrl/bandas/listar.php");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$res = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
rodarTeste("4. Endpoint de listagem pública responde com sucesso (HTTP 200)", $code === 200);

// Teste 5: Proteção de Rota sem Autenticação (Inválido / RBAC)
$ch = curl_init("$baseUrl/bandas/minha_banda.php");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$res = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
rodarTeste("5. Rota protegida rejeita requisição sem sessão (HTTP 401)", $code === 401);

echo "\n--------------------------------------------------------\n";
echo "RESULTADO FINAL: {$testesPassaram} de {$totalTestes} testes aprovados.\n";
echo "--------------------------------------------------------\n";
