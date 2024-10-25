<?php

namespace App\EventListener;

use App\Entity\Ticket;
use App\Entity\TicketStatusHistory;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Events;
use Symfony\Bundle\SecurityBundle\Security;

#[AsEntityListener(event: Events::postUpdate, entity: Ticket::class)]
final class TicketListener
{
    private bool $updating;

    public function __construct(private Security $security, private EntityManagerInterface $entityManager)
    {
        $this->updating = false;
    }

    public function postUpdate(Ticket $ticket): void
    {
        if ($this->updating) {
            return;
        }

        $this->updating = true;

        $user = $this->security->getUser(); // Récupère l'utilisateur actuel

        // Prépare la description de la modification
        $modification = 'Modification du ticket';

        $history = new TicketStatusHistory();
        $history->setTicketId($ticket);
        $history->setChangedBy($user);
        $history->setStatus($ticket->getStatus());
        $history->setChangedAt(new \DateTimeImmutable());
        $history->setComment($modification);
        $ticket->setUpdatedat(new \DateTimeImmutable());

        $this->entityManager->persist($history);
        $this->entityManager->flush();

        $this->updating = false;
    }
}