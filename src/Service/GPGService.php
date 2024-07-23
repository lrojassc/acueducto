<?php

namespace App\Service;

use PHPUnit\Util\Exception;

class GPGService
{

    public function encryptAndSignFile(string $filePath): array
    {
        $gpg = new \gnupg();
        $encryptedFilePath = $filePath . 'encrypted_' . getdate()[0] . '.gpg';

        try {
            // Leer la clave pública desde el archivo
            $publicKey = file_get_contents('public.key');
            if ($publicKey === false) {
                die('Error al leer el archivo de clave pública');
            } else {
                // Importar la clave pública
                $publicInfo = $gpg->import($publicKey);

            }

            // Leer la clave privada desde el archivo
            $privateKey = file_get_contents('private.key');
            if ($privateKey === false) {
                die('Error al leer el archivo de clave privada');
            } else {
                // Importar la clave privada
                $privateInfo = $gpg->import($privateKey);
                if ($privateInfo === false) {
                    die('Error al importar la clave privada');
                }
            }

            $text = file_get_contents('mensaje.txt');

            // Establecer la clave pública para cifrar
            $gpg->addencryptkey($publicInfo['fingerprint']);

            // Establecer la clave privada para firmar
            $gpg->addsignkey($privateInfo['fingerprint'], 'Gs78kz9lg3*aa');


            // Firmar y cifrar el texto
            $success = false;
            $signedAndEncrypted = $gpg->encryptsign($text);
            if ($signedAndEncrypted === false) {
                $signedAndEncrypted = '';
            } else {
                file_put_contents($encryptedFilePath, $signedAndEncrypted);
                $success = true;
            }

            return ['success' => $success, 'encrypted' => $signedAndEncrypted];
        } catch (\ErrorException $exception) {
            throw new \RuntimeException($exception->getMessage());
        }
    }
}
