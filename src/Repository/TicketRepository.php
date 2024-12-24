<?php

namespace App\Repository;

use App\Entity\Ticket;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Form\Form;
use App\Entity\User;

/**
 * @extends ServiceEntityRepository<Ticket>
 */
class TicketRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ticket::class);
    }

    public function ticketByStatusCount(): array
    {
        return $this->createQueryBuilder('ticket')
            ->select("ticket.status, COUNT(ticket.id) as nbTicket")
            ->groupBy('ticket.status')
            ->getQuery()
            ->getArrayResult();
    }

    public function ticketByDateOverpassedCount() : array
    {
        $deadline = new \DateTime();
        return $this->createQueryBuilder('ticket')
            ->select("ticket.priority, COUNT(ticket.id) as nbTicket")
            ->where('ticket.deadline < :deadline')
            ->setParameter('deadline', $deadline)
            ->groupBy('ticket.priority') // Group by ticket priority
            ->getQuery()
            ->getArrayResult();
    }

    public function ticketCreatedByMonthCount() : array
    {
        $array = $this->createQueryBuilder('ticket')
            ->select("ticket.created_at, COUNT(ticket.id) as nbTicket")
            ->groupBy('ticket.created_at')
            ->where('ticket.created_at >= :year')
            ->setParameter('year', date('Y-M-01', strtotime('-1 year')))
            ->getQuery()
            ->getArrayResult();

        // Process the results to count tickets by month
        $monthCounts = [];
        foreach ($array as $ticket) {

            // Use the format method to get the year and month
            $month = $ticket['created_at']->format('Y-m');

            // Increment the count for this month
            if (!isset($monthCounts[$month])) {
                $monthCounts[$month] = 0;
            }
            $monthCounts[$month]+=$ticket['nbTicket'];
        }

        return array_map(function($count, $month) {
            return ['monthYear' => $month, 'nbTicket' => $count];
        }, $monthCounts, array_keys($monthCounts));
    }

    public function findTicketByUser($user)
    {
        return $this->createQueryBuilder('ticket')
            ->where('ticket.owned_by = :user')
            ->setParameter('user', $user);
    }

    public function filterTickets(Form $form): array
    {
        $queryBuilder = $this->createQueryBuilder('t');

        if ($form->isSubmitted() && $form->isValid()) {
            $title = $form->get('title')->getData();
            $priority = $form->get('priority')->getData();
            $status = $form->get('status')->getData();
            $createdat = $form->get('createdat')->getData();


            if ($title) {
                $queryBuilder->andWhere('t.title LIKE :title')
                    ->setParameter('title', '%' . $title . '%');
            }
            if ($priority) {
                $queryBuilder->andWhere('t.priority = :priority')
                    ->setParameter('priority', $priority);
            }
            if ($status) {
                $queryBuilder->andWhere('t.status = :status')
                    ->setParameter('status', $status);
            }
            if ($createdat) {
                $formattedDate = $createdat->format('Y-m-d');
                $queryBuilder->andWhere('t.created_at = :createdat')
                    ->setParameter('createdat', $formattedDate);
            }
        }
        return $queryBuilder
            ->orderBy('t.created_at', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function filterMyTickets(Form $form, User $user): array
    {
        $queryBuilder = $this->createQueryBuilder('t')
            ->where('t.assignedTo = :user')
            ->setParameter('user', $user);

        if ($form->isSubmitted() && $form->isValid()) {
            $title = $form->get('title')->getData();
            $priority = $form->get('priority')->getData();
            $status = $form->get('status')->getData();
            $createdat = $form->get('createdat')->getData();


            if ($title) {
                $queryBuilder->andWhere('t.title LIKE :title')
                    ->setParameter('title', '%' . $title . '%');
            }
            if ($priority) {
                $queryBuilder->andWhere('t.priority = :priority')
                    ->setParameter('priority', $priority);
            }
            if ($status) {
                $queryBuilder->andWhere('t.status = :status')
                    ->setParameter('status', $status);
            }
            if ($createdat) {
                $formattedDate = $createdat->format('Y-m-d');
                $queryBuilder->andWhere('t.created_at = :createdat')
                    ->setParameter('createdat', $formattedDate);
            }
        }
        return $queryBuilder
            ->orderBy('t.created_at', 'DESC')
            ->getQuery()
            ->getResult();
    }


    public function getTotalNumberOfTickets(): int
    {
        return $this->createQueryBuilder('t')
            ->select('count(t.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }
}