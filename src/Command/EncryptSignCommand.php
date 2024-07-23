<?php

namespace App\Command;

use App\Service\GPGService;
use AllowDynamicProperties;
use phpseclib3\Crypt\PublicKeyLoader;
use phpseclib3\Net\SFTP;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AllowDynamicProperties] #[AsCommand(
    name: 'app:encrypt-sign',
    description: 'Encriptar',
)]
class EncryptSignCommand extends Command
{
    public function __construct(GPGService $GPGService)
    {
        parent::__construct();

        $this->gpgService = $GPGService;
    }

    protected function configure(): void
    {
        $this->setName('Encrypts a given text using GPG');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $encrypted = $this->gpgService->encryptAndSignFile('/var/www/html/');
        if ($encrypted['success']) {
            $encrypted_file = $encrypted['encrypted'];
            $remoteFile = '/home/u317846255/domains/pagos-acueducto-quituro.cloud/public_html/prueba_' . getdate()[0] . '.gpg';
            $privateKey = PublicKeyLoader::loadPrivateKey(file_get_contents('id_rsa'));
            // Conectar al servidor SFTP
            $sftp = new SFTP('pagos-acueducto-quituro.cloud', '65002');
            $login = $sftp->login('u317846255', $privateKey);
            $ftp = new SFTP('pagos-acueducto-quituro.cloud', '65002');

            if (!$ftp->login('u317846255', 'Gs78kz9lg3*')) {
                die('Autenticación fallida.');
            }

            // Verificar si el archivo remoto ya existe
            $remoteFileExists = $ftp->file_exists($remoteFile);
            if ($remoteFileExists) {
                die('El archivo remoto ya existe.');
            }

            if (!$ftp->put($remoteFile, $encrypted_file, FTP_BINARY)) {
                die('No se pudo subir el archivo.');
            }

            $remoteFile2 = '/home/u317846255/domains/pagos-acueducto-quituro.cloud/public_html/prueba2_' . getdate()[0] . '.gpg';
            if (!$sftp->put($remoteFile2, $encrypted_file, FTP_BINARY)) {
                die('No se pudo subir el archivo.');
            }

            $io->success('Archivo almacenado correctamente.');

        } else {
            $io->error('Ocurrio un error al encriptar y firmar');
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
