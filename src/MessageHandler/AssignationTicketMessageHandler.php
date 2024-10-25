<?php

namespace App\MessageHandler;

use App\Message\AssignationTicketMessage;
use http\Message;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Contracts\Cache\CacheInterface;

#[AsMessageHandler]
final readonly class AssignationTicketMessageHandler
{
    public function __construct(
        private Application     $application,
        private LoggerInterface $logger
    )
    {
    }

    public function __invoke(AssignationTicketMessage $message): void
    {
        $this->logger->info('Début de l\'exécution de la commande AssignationTicketCommand.');

        $input = new ArrayInput(['command' => 'app:AssignationTicket']);
        $output = new NullOutput();
        $this->application->run($input, $output);
        dump('AssignationTicketCommand exécutée.');

        $this->logger->info('Fin de l\'exécution de la commande AssignationTicketCommand.');
    }
}
