<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\TicketRepository;

class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(TicketRepository $ticketRepository): Response
    {
        return $this->render('dashboard/index.html.twig', [
            'tickets' => $ticketRepository->findAll(),
            'controller_name' => 'DashboardController',
        ]);
    }


}
