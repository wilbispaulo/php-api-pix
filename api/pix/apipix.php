<?php

use wilbispaulo\BRcode\BRcode;

require_once(__DIR__ . "/vendor/autoload.php");

header("Content-Type: application/json; charset=UTF-8");

date_default_timezone_set("America/Sao_Paulo");

$requestMethod = $_SERVER['REQUEST_METHOD'];

if ($requestMethod !== 'POST') {
    header($_SERVER["SERVER_PROTOCOL"] . " 405 Method not allowed");
    exit("Método não permitido");
}

$input = file_get_contents('php://input', true);
$input = json_decode($input, true);

// Inicializa código, mensagem e header de resposta como OK
$cod = 200;
$msg = "";
$header = $_SERVER["SERVER_PROTOCOL"] . " 200 OK";

if (!array_key_exists('chave_pix', $input)) {
    $cod = 412;
    $msg .= "Chave Pix ausente ";
    $header = $_SERVER["SERVER_PROTOCOL"] . " 412 Bad request";
} elseif (!BRcode::validaChave($input['chave_pix'])) {
    $cod = 412;
    $msg = "Chave Pix invalida ";
    $header = $_SERVER["SERVER_PROTOCOL"] . " 412 Bad request";
}
if (!array_key_exists('nome', $input)) {
    $cod = 412;
    $msg .= "Nome ausente ";
    $header = $_SERVER["SERVER_PROTOCOL"] . " 412 Bad request";
} elseif (!BRcode::validaNome($input['nome'])) {
    $cod = 412;
    $msg .= "Nome invalido ";
    $header = $_SERVER["SERVER_PROTOCOL"] . " 412 Bad request";
}
if (!array_key_exists('cidade', $input)) {
    $cod = 412;
    $msg .= "Cidade ausente ";
    $header = $_SERVER["SERVER_PROTOCOL"] . " 412 Bad request";
} elseif (!BRcode::validaCidade($input['cidade'])) {
    $cod = 412;
    $msg .= "Cidade invalida ";
    $header = $_SERVER["SERVER_PROTOCOL"] . " 412 Bad request";
}
if (!array_key_exists('tx_id', $input)) {
    $input['tx_id'] = "***";
} elseif (!BRcode::validaTxId($input['tx_id'])) {
    $cod = 412;
    $msg .= "TxId invalido ";
    $header = $_SERVER["SERVER_PROTOCOL"] . " 412 Bad request";
}
if (!array_key_exists('valor', $input)) {
    $input['valor'] = "";
} elseif (!BRcode::validaTxId($input['valor'])) {
    $cod = 412;
    $msg .= "Valor invalido ";
    $header = $_SERVER["SERVER_PROTOCOL"] . " 412 Bad request";
}

$resposta['codigo'] = $cod;
$resposta['mensagem'] = $msg;

if ($cod == 200) {

    // Cria o objeto e carrega os dados
    $pixCode = new BRcode($input['chave_pix'], $input['nome'], $input['cidade'], $input['tx_id'], $input['valor']);

    // Chama o método para gerar a string do PIX copia e cola
    $txtPix = $pixCode->geraPixCode();
    $resposta['txtPix'] = $txtPix;
}

// Saída do response e JSON

header($header);
echo json_encode($resposta);
