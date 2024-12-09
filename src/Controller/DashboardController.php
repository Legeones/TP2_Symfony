<?php

namespace App\Controller;

use App\Form\TicketFilterType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\TicketRepository;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class DashboardController extends AbstractController
{
    #[Route('/', name: 'app_dashboard')]
    public function index(Request $request, TicketRepository $ticketRepository): Response
    {
        $dataStatusCount = $ticketRepository->ticketByStatusCount();
        $dataExpiredTickets = $ticketRepository->ticketByDateOverpassedCount();
        $dataTicketsByMonth = $ticketRepository->ticketCreatedByMonthCount();


        // Prepare labels and values for the chart
        $labelsStatusCount = [];
        $valuesStatusCount = [];

        $labelsExpiredTickets = [];
        $valuesExpiredTickets = [];

        $labelsTicketsByMonth = [];
        $valuesTicketsByMonth = [];

        foreach ($dataStatusCount as $row) {
            $labelsStatusCount[] = $row['status']->name;       // Use the status as label
            $valuesStatusCount[] = $row['nbTicket'];     // Use the count as data
        }

        foreach ($dataExpiredTickets as $row) {
            $labelsExpiredTickets[] = $row['priority'];
            $valuesExpiredTickets[] = $row['nbTicket'];
        }

        foreach ($dataTicketsByMonth as $row) {
            $labelsTicketsByMonth[] = $row['monthYear'];
            $valuesTicketsByMonth[] = $row['nbTicket'];
        }

        $form = $this->createForm(TicketFilterType::class);
        $form->handleRequest($request);

        // Default query to retrieve all tickets
        $queryBuilder = $ticketRepository->createQueryBuilder('t');

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            // Filter by title
            if (!empty($data['title'])) {
                $queryBuilder->andWhere('t.title LIKE :title')
                    ->setParameter('title', '%' . $data['title'] . '%');
            }

            // Filter by priority
            if (!empty($data['priority'])) {
                $queryBuilder->andWhere('t.priority = :priority')
                    ->setParameter('priority', $data['priority']);
            }

            // Filter by status
            if (!empty($data['status'])) {
                $queryBuilder->andWhere('t.status = :status')
                    ->setParameter('status', $data['status']);
            }
        }

        // Execute the query and get the results
        $tickets = $queryBuilder->getQuery()->getResult();

        return $this->render('dashboard/index.html.twig', [
            'tickets' => $tickets,
            'form' => $form->createView(),
            'controller_name' => 'DashboardController',
            'chartStatusCountLabels' => $labelsStatusCount,
            'chartStatusCountValues' => $valuesStatusCount,
            'chartExpiredTicketsLabels' => $labelsExpiredTickets,
            'chartExpiredTicketValues' => $valuesExpiredTickets,
            'chartTicketsByMonthLabels' => $labelsTicketsByMonth,
            'chartTicketsByMonthValues' => $valuesTicketsByMonth,
        ]);
    }


}
