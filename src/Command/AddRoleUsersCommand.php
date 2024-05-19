<?php

namespace App\Command;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:add-role-users',
    description: 'Agregar rol a todos los usuarios registrados',
)]
class AddRoleUsersCommand extends Command
{
    public function __construct(EntityManagerInterface $entityManager, UserRepository $userRepository)
    {
        parent::__construct();

        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
    }

    protected function configure(): void
    {
        $this->setName('Aignar rol a totos los usuarios');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $users = $this->userRepository->findAll();
        foreach ($users as $user) {
            $user->setRoles(['ROLE_USER']);
        }
        $this->entityManager->flush();
        $io->success('Todos los usuarios fueron actualizados de rol correctamente.');

        return Command::SUCCESS;
    }
}
