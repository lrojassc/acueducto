<?php

namespace App\Command;

use AllowDynamicProperties;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AllowDynamicProperties] #[AsCommand(
    name: 'app:update-password-with-document-number',
    description: 'Actualizar contraseña con numero de documento',
)]
class UpdatePasswordWithDocumentNumberCommand extends Command
{
    public function __construct(EntityManagerInterface $entityManager, UserRepository $userRepository, UserPasswordHasherInterface $passwordHasher)
    {
        parent::__construct();

        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
        $this->passwordHasher = $passwordHasher;
    }

    protected function configure(): void
    {
        $this->setName('Actualizar contraseña con numero de documento');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $users = $this->userRepository->findAll();
        foreach ($users as $user) {
            $password = $this->passwordHasher->hashPassword(
                $user,
                $user->getDocumentNumber()
            );
            $user->setPassword($password);
        }
        $this->entityManager->flush();
        $io->success('Actualizada la contraseña de todos los usuarios con su numero de identificacion');

        return Command::SUCCESS;
    }
}
