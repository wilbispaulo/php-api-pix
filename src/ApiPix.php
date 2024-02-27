<?php

namespace wilbispaulo\ApiPix;

use chillerlan\QRCode\QRCode;
use wilbispaulo\BRcode\BRcode;
use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Common\Version;

class ApiPix
{
    private $statusCode = 200;
    private $body = [];
    private $headers = [];

    public function __construct(
        private array $input
    ) {
        $parseArray = $this->parseArray($input);
        $this->statusCode = $parseArray['statusCode'];
        $this->body['statusCode'] = $this->statusCode;
        $this->body['msg'] = $parseArray['msg'];
        $this->headers[$parseArray['headerIndex']] = $parseArray['headerBody'];
    }

    private function parseArray(array $arr): array
    {
        $result = [];
        $result['statusCode'] = 200;
        $result['msg'] = 'ok';
        $serverProt = substr($_SERVER["SERVER_PROTOCOL"], strrpos($_SERVER["SERVER_PROTOCOL"], '/') + 1);
        if (array_key_exists('chave', $arr)) {
            if (!BRcode::validaChave($arr['chave'])) {
                $result['msg'] = "Chave Pix invalida ";
                $result['statusCode'] = 412;
            }
        } else {
            $result['msg'] = "Chave Pix ausente ";
            $result['statusCode'] = 412;
        }

        if (array_key_exists('nome', $arr)) {
            if (!BRcode::validaNome($arr['nome'])) {
                $result['msg'] = "Nome invalido ";
                $result['statusCode'] = 412;
            }
        } else {
            $result['msg'] = "Nome ausente ";
            $result['statusCode'] = 412;
        }

        if (array_key_exists('municipio', $arr)) {
            if (!BRcode::validaCidade($arr['municipio'])) {
                $result['msg'] = "Municipio invalido ";
                $result['statusCode'] = 412;
            }
        } else {
            $result['msg'] = "Municipio ausente ";
            $result['statusCode'] = 412;
        }

        if (array_key_exists('txId', $arr)) {
            if (!BRcode::validaTxId($arr['txId'])) {
                $result['msg'] = "TxId invalido ";
                $result['statusCode'] = 412;
            }
        }

        if (array_key_exists('valor', $arr)) {
            if (!BRcode::validaValor($arr['valor'])) {
                $result['msg'] = "Valor invalido ";
                $result['statusCode'] = 412;
            }
        } else {
            $this->input['valor'] =  "";
        }

        if ($result['statusCode'] === 412) {
            $result['headerIndex'] = 'HTTP/';
            $result['headerBody'] = $serverProt . " 412 Bad request";
        } else {
            $result['headerIndex'] = 'Content-Type:';
            $result['headerBody'] = 'application/json';
        }

        return $result;
    }

    public function createPixTxt(): array
    {
        if ($this->statusCode === 200) {
            $pixCode = new BRcode($this->input['chave'], $this->input['nome'], $this->input['municipio'], $this->input['txId'], $this->input['valor']);

            $this->body['pixTxt'] = $pixCode->geraPixCode();
        }

        return ['statusCode' => $this->statusCode, 'body' => $this->body, 'headers' => $this->headers];
    }

    public static function createPixQRCode(string $pixTxt): array
    {
        $statusCode = 200;
        $msg = 'ok';

        $serverProt = substr($_SERVER["SERVER_PROTOCOL"], strrpos($_SERVER["SERVER_PROTOCOL"], '/') + 1);

        if (!self::checkPixTxt($pixTxt)) {
            $statusCode = 412;
            $msg = 'PixTxt inválido';
            $header['HTTP/'] = $serverProt . ' 412 Bad request';
        } else {
            $header['Content-Type:'] = 'application/json';
        }

        if ($statusCode === 200) {
            $body['base64QR'] = self::createQRCode($pixTxt);
        }
        $headers = $header;
        $body['statusCode'] = $statusCode;
        $body['msg'] = $msg;
        return ['body' => $body, 'headers' => $headers];
    }

    public static function checkPixTxt(string $pixTxt)
    {
        $body = substr($pixTxt, 0, -4);
        $crc = substr($pixTxt, -4);
        $crc16 = CRC16($body, POLINOMIO, INICIAL, TIPO);

        return ($crc === $crc16);
    }

    public static function createQRCode(string $txt): string
    {
        $options = new QROptions;
        $options->version = Version::AUTO;
        $options->eccLevel = EccLevel::H;

        return (new QRCode($options))->render($txt);;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getBody(): array
    {
        return $this->body;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }
}
