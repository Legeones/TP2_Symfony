<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\TicketRepository;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(TicketRepository $ticketRepository): Response
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

        return $this->render('dashboard/index.html.twig', [
            'tickets' => $ticketRepository->findAll(),
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
