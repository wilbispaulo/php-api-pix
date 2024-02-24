<?php

use wilbispaulo\BRcode\BRcode;

require_once dirname(__FILE__, 2) . "/vendor/autoload.php";

// header("Content-Type: application/json; charset=UTF-8");

// date_default_timezone_set("America/Sao_Paulo");

// $requestMethod = $_SERVER['REQUEST_METHOD'];

// if ($requestMethod !== 'GET') {
//     header($_SERVER["SERVER_PROTOCOL"] . " 405 Method not allowed");
//     exit("Método não permitido");
// }



// **********************************************************************************************
// $args is used to pass parameters from call
// $args must be ['chave'=>'', 'nome'=>'', 'municipio'=>'', 'txId'=>'', 'valor'=>''] structure
// ***********************************************************************************************





$input = $args;

// Inicializa código, mensagem e header de resposta como OK

$statusCode = 200;
$body = [];
$headers = [];
$serverProt = substr($_SERVER["SERVER_PROTOCOL"], strrpos($_SERVER["SERVER_PROTOCOL"], '/') + 1);

// $header = $_SERVER["SERVER_PROTOCOL"] . " 200 OK";
$body['msg'] = 'ok';

if (!array_key_exists('chave', $input)) {
    $cod = 412;
    $body['msg'] = "Chave Pix ausente ";
    $headers['HTTP/'] = $serverProt . " 412 Bad request";
} elseif (!BRcode::validaChave($input['chave'])) {
    $cod = 412;
    $body['msg'] = "Chave Pix invalida ";
    $headers['HTTP/'] = $serverProt . " 412 Bad request";
}
if (!array_key_exists('nome', $input)) {
    $cod = 412;
    $body['msg'] = "Nome ausente ";
    $headers['HTTP/'] = $serverProt . " 412 Bad request";
} elseif (!BRcode::validaNome($input['nome'])) {
    $cod = 412;
    $body['msg'] = "Nome invalido ";
    $headers['HTTP/'] = $serverProt . " 412 Bad request";
}
if (!array_key_exists('municipio', $input)) {
    $cod = 412;
    $body['msg'] = "Município ausente ";
    $headers['HTTP/'] = $serverProt . " 412 Bad request";
} elseif (!BRcode::validaCidade($input['municipio'])) {
    $cod = 412;
    $body['msg'] = "Município inválido ";
    $headers['HTTP/'] = $serverProt . " 412 Bad request";
}
if (!array_key_exists('txId', $input)) {
    $input['txId'] = "***";
} elseif (!BRcode::validaTxId($input['txId'])) {
    $cod = 412;
    $body['msg'] = "TxId invalido ";
    $headers['HTTP/'] = $serverProt . " 412 Bad request";
}
if (!array_key_exists('valor', $input)) {
    $input['valor'] = "";
} elseif (!BRcode::validaTxId($input['valor'])) {
    $cod = 412;
    $body['msg'] = "Valor invalido ";
    $headers['HTTP/'] = $serverProt . " 412 Bad request";
}

$body['statusCode'] = $statusCode;

if ($statusCode === 200) {

    // Cria o objeto e carrega os dados
    $pixCode = new BRcode($input['chave'], $input['nome'], $input['municipio'], $input['txId'], $input['valor']);

    // Chama o método para gerar a string do PIX copia e cola
    $txtPix = $pixCode->geraPixCode();
    $body['txtPix'] = $txtPix;

    // Set headers
    $headers['Content-Type:'] = 'application/json';
}

// Saída do response e JSON
// $body['']
