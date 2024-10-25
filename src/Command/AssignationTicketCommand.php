<?php

namespace App\Command;

use AllowDynamicProperties;
use App\Entity\Priority;
use App\Entity\Status;
use App\Entity\Ticket;
use App\Repository\TicketRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AllowDynamicProperties]
#[AsCommand(
    name: 'app:AssignationTicket',
    description: 'Add a short description for your command',
)]
class AssignationTicketCommand extends Command
{
    protected static $defaultName = 'app:AssignationTicket';

    public function __construct(
        TicketRepository       $ticketRepository,
        UserRepository         $userRepository,
        EntityManagerInterface $entityManager
    )
    {
        parent::__construct();
        $this->ticketRepository = $ticketRepository;
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Assigne automatiquement les tickets non affectés');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $tickets = $this->ticketRepository->findAll();
        $users = $this->userRepository->findByRole('ROLE_SUPPORT');

        if (empty($tickets)) {
            $io->success('Aucun ticket à traiter.');

            return Command::SUCCESS;
        }
        if (empty($users)) {
            $io->error('Aucun utilisateur trouvé.');

            return Command::FAILURE;
        }
        foreach ($tickets as $ticket) {
            $deadline = $ticket->getDeadline();
            $createdAt = $ticket->getCreatedAt();
            $halfwayDate = (clone $createdAt)->modify('+' . ($deadline->diff($createdAt)->days / 2) . ' days');

            if (new \DateTimeImmutable() >= $halfwayDate && $ticket->getAssignedTo() === null) {
                $user = $users[array_rand($users)];
                $ticket->setAssignedTo($user);
                $this->entityManager->persist($ticket);
            }
        }

        $this->entityManager->flush();

        return Command::SUCCESS;
    }
}
