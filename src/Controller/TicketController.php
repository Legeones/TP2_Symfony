<?php

namespace App\Controller;

use App\Entity\Status;
use App\Entity\Ticket;
use App\Form\TicketFilterType;
use App\Form\TicketType;
use App\Repository\TicketRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[IsGranted('ROLE_USER')]
#[Route('/ticket')]
final class TicketController extends AbstractController
{
    #[Route('/mine', name: 'app_ticket_user', methods: ['GET', 'POST'])]
    public function index(Request $request, TicketRepository $ticketRepository, TokenStorageInterface $tokenStorage): Response
    {
        $user = $tokenStorage->getToken()->getUser();
        $form = $this->createForm(TicketFilterType::class);
        $form->handleRequest($request);

        // Default query to retrieve tickets owned by the user
        $tickets = $ticketRepository->filterMyTickets($form, $user);

        return $this->render('ticket/index.html.twig', [
            'tickets' => $tickets,
            'form' => $form->createView(),
            'controller_name' => 'TicketController',
        ]);
    }

    #[Route('/new', name: 'app_ticket_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $ticket = new Ticket();
        $ticket->setOwnedby($this->getUser());
        $form = $this->createForm(TicketType::class, $ticket);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $ticket->setDeadlineBasedOnPriority();
            $entityManager->persist($ticket);
            $entityManager->flush();

            return $this->redirectToRoute('app_dashboard', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('ticket/new.html.twig', [
            'ticket' => $ticket,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_ticket_show', methods: ['GET'])]
    public function show(Ticket $ticket): Response
    {
        return $this->render('ticket/show.html.twig', [
            'ticket' => $ticket,
        ]);
    }

    #[IsGranted('ROLE_SUPPORT')]
    #[Route('/{id}/edit', name: 'app_ticket_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Ticket $ticket, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(TicketType::class, $ticket);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_dashboard', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('ticket/edit.html.twig', [
            'ticket' => $ticket,
            'form' => $form,
        ]);
    }

    #[IsGranted("ROLE_ADMIN")]
    #[Route('/{id}', name: 'app_ticket_delete', methods: ['POST'])]
    public function delete(Request $request, Ticket $ticket, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$ticket->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($ticket);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_dashboard', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/close', name: 'app_ticket_close', methods: ['POST'])]
    public function close(Request $request, Ticket $ticket, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('close'.$ticket->getId(), $request->request->get('_token'))) {
            $ticket->setStatus(Status::Closed);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_dashboard', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/assign', name: 'app_ticket_assign', methods: ['POST'])]
    public function assign(Request $request, Ticket $ticket, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('assign'.$ticket->getId(), $request->request->get('_token'))) {
            $ticket->setAssignedTo($this->getUser());
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_dashboard', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/resolve', name: 'app_ticket_resolve', methods: ['POST'])]
    public function resolve(Request $request, Ticket $ticket, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('resolve'.$ticket->getId(), $request->request->get('_token'))) {
            $ticket->setStatus(Status::Resolved);
            $ticket->setResolveAt(new \DateTimeImmutable());
            $entityManager->flush();
        }
        return $this->redirectToRoute('app_dashboard', [], Response::HTTP_SEE_OTHER);
    }
}