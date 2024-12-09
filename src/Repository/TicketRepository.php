<?php

namespace App\Repository;

use App\Entity\Ticket;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

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

    //    /**
    //     * @return Ticket[] Returns an array of Ticket objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('t.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Ticket
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
