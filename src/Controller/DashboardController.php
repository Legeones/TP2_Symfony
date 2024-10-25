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
        $data = $ticketRepository->pieChartData();

        // Prepare labels and values for the chart
        $labels = [];
        $values = [];

        foreach ($data as $row) {
            $labels[] = $row['status']->name;       // Use the status as label
            $values[] = $row['nbTicket'];     // Use the count as data
        }

        $data = [
            'chartLabels' => $labels,
            'chartValues' => $values,
        ];

        return $this->render('dashboard/index.html.twig', [
            'tickets' => $ticketRepository->findAll(),
            'controller_name' => 'DashboardController',
            'chartLabels' => $data['chartLabels'],
            'chartValues' => $data['chartValues'],
        ]);
    }


}
