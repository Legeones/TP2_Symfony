<?php

namespace App\EventListener;

use App\Entity\Ticket;
use App\Entity\TicketStatusHistory;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Events;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Event\ViewEvent;

#[AsEntityListener(event: Events::postUpdate, method: 'updateHistorique', entity: Ticket::class)]
final readonly class TicketListener
{

    public function __construct(private Security $security, private EntityManagerInterface $entityManager)
    {
    }
    public function updateHistorique(Ticket $ticket): void
    {
        $user = $this->security->getUser(); // Récupère l'utilisateur actuel

        // Prépare la description de la modification
        $modification = 'Modification du ticket';

        $history = new TicketStatusHistory();
        $history->setTicketId($ticket);
        $history->setChangedBy($user);
        $history->setStatus($ticket->getStatus());
        $history->setChangedAt(new \DateTimeImmutable());
        $history->setComment($modification);


        $this->entityManager->persist($history);
        $this->entityManager->flush();
    }
}
