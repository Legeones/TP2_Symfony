<?php

namespace App\Controller;

use App\Form\TicketFilterType;
use App\Repository\TicketRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
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
        $totalTickets = $ticketRepository->getTotalNumberOfTickets();

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

        $tickets = $ticketRepository->filterTickets($form);


        return $this->render('dashboard/index.html.twig', [
            'tickets' => $tickets,
            'form' => $form->createView(),
            'controller_name' => 'DashboardController',
            'totalTickets' => $totalTickets,
            'chartStatusCountLabels' => $labelsStatusCount,
            'chartStatusCountValues' => $valuesStatusCount,
            'chartExpiredTicketsLabels' => $labelsExpiredTickets,
            'chartExpiredTicketValues' => $valuesExpiredTickets,
            'chartTicketsByMonthLabels' => $labelsTicketsByMonth,
            'chartTicketsByMonthValues' => $valuesTicketsByMonth,
        ]);
    }
}