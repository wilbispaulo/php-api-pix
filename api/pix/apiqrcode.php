<?php

/**
 * API PIX para geração de 'QRCode' 
 * @author: Wilbis W. Paulo
 * @copyright: 2023
 * @license: MIT
 * 
 * @ENTRADA
 * Requisição POST em http://www.wilbispaulo.com.br/api/apipix/
 * BODY:JSON
 * 'PIX code'
 * 
 * @SAIDA
 * Response 200:JSON
 * 'base64QR'(svg+xml)
 * 
 */

use chillerlan\QRCode\Common\Version;
use chillerlan\QRCode\{QRCode, QROptions};
use chillerlan\QRCode\Common\EccLevel;

// use chillerlan\QRCode\Output\QROutputInterface;

require_once(dirname(__FILE__, 3) . "/vendor/autoload.php");

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
$header = $_SERVER["SERVER_PROTOCOL"] . " 200 Ok";

if (!is_array($input)) {
    $cod = 412;
    $msg .= "JSON ausente";
    $header = $_SERVER["SERVER_PROTOCOL"] . " 412 Bad request";
} else {
    if (!array_key_exists('version', $input)) {
        $input['version'] = Version::AUTO;
    }

    if (!array_key_exists('txt_pix', $input)) {
        $cod = 412;
        $msg .= "Pix copia e cola ausente";
        $header = $_SERVER["SERVER_PROTOCOL"] . " 412 Bad request";
    }
}

$resposta['codigo'] = $cod;
$resposta['mensagem'] = $msg;


if ($cod === 200) {
    $options = new QROptions;
    $options->version = (int)$input['version'];
    $options->eccLevel = EccLevel::H;

    // Cria o objeto e renderiza como imagem svg+xml codificada em uma string base64
    $base64QR = (new QRCode($options))->render($input['txt_pix']);
    $resposta['base64QR'] = $base64QR;
}

// Saída do response e JSON
header($header);
echo json_encode($resposta);
