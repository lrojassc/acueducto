<?php

namespace App\Command;

use AllowDynamicProperties;
use App\Repository\SubscriptionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AllowDynamicProperties] #[AsCommand(
    name: 'app:update-full-payment',
    description: 'Cambiar el pago completo para todos los servicios',
)]
class UpdateFullPaymentService extends Command
{

    public function __construct(EntityManagerInterface $entityManager, SubscriptionRepository $subscriptionRepository)
    {
        parent::__construct();

        $this->entityManager = $entityManager;
        $this->subscriptionRepository = $subscriptionRepository;
    }

    protected function configure(): void
    {
        $this->setName('Cambiar el pago completo para todos los servicios');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $subscriptions = $this->subscriptionRepository->findAll();
        foreach ($subscriptions as $service) {
            $service->setFullPayment(true);
        }
        $this->entityManager->flush();
        $io->success('Todos los servicios fueron actualizados correctamente.');

        return Command::SUCCESS;
    }
}