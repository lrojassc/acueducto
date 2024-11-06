<?php

namespace App\Command;

use AllowDynamicProperties;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AllowDynamicProperties] #[AsCommand(
    name: 'app:create-admin-user',
    description: 'Crear el primer usuario Admin para el sistema de facturacion',
)]
class CreateAdminUserCommand extends Command
{

    public function __construct(EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher)
    {
        parent::__construct();

        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('name', InputArgument::REQUIRED, 'Nombre Completo')
            ->addArgument('document', InputArgument::REQUIRED, 'Numero de documento')
            ->addArgument('email', InputArgument::REQUIRED, 'Correo')
            ->addArgument('phone', InputArgument::REQUIRED, 'Numero de telefono')
            ->addArgument('address', InputArgument::REQUIRED, 'Dirección')
            ->addArgument('city', InputArgument::REQUIRED, 'Ciudad')
            ->addArgument('municipality', InputArgument::REQUIRED, 'Municipio')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $user = new User();
        $user->setName($input->getArgument('name'));
        $user->setDocumentType('CC');
        $user->setDocumentNumber($input->getArgument('document'));
        $user->setEmail($input->getArgument('email'));
        $user->setRoles(['ROLE_ADMIN']);
        $user->setPhoneNumber($input->getArgument('phone'));
        $user->setPaidSubscription('PAGADA');
        $user->setAddress($input->getArgument('address'));
        $user->setCity($input->getArgument('city'));
        $user->setMunicipality($input->getArgument('municipality'));
        $password = $this->passwordHasher->hashPassword(
            $user,
            $user->getDocumentNumber()
        );
        $user->setPassword($password);
        $user->setStatus('ACTIVO');
        $user->setCreatedAt(new \DateTime());
        $user->setUpdatedAt(new \DateTime());
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $io->success('Ha creado el usuario administrador del sistema.');

        return Command::SUCCESS;
    }
}
