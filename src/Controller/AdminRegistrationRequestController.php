<?php

namespace App\Controller;

use App\Entity\ClientRegistrationRequest;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AdminRegistrationRequestController extends AbstractController
{
    #[Route('/admin/registration-requests', name: 'app_admin_registration_requests')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $registrationRequests = $entityManager
            ->getRepository(ClientRegistrationRequest::class)
            ->findBy(
                ['status' => 'pending'],
                ['createdAt' => 'DESC']
            );

        return $this->render('admin_registration_request/index.html.twig', [
            'registrationRequests' => $registrationRequests,
        ]);
    }

    #[Route('/admin/registration-requests/{id}', name: 'app_admin_registration_request_show')]
    public function show(ClientRegistrationRequest $registrationRequest): Response
    {
        return $this->render('admin_registration_request/show.html.twig', [
            'registrationRequest' => $registrationRequest,
        ]);
    }

    #[Route('/admin/registration-requests/{id}/reject', name: 'app_admin_registration_request_reject', methods: ['POST'])]
    public function reject(
        ClientRegistrationRequest $registrationRequest,
        EntityManagerInterface $entityManager
    ): Response {
        if ($registrationRequest->getStatus() !== 'pending') {
            $this->addFlash('warning', 'To zgłoszenie zostało już zweryfikowane.');

            return $this->redirectToRoute('app_admin_registration_request_show', [
                'id' => $registrationRequest->getId(),
            ]);
        }

        $registrationRequest->setStatus('rejected');
        $registrationRequest->setReviewedAt(new \DateTimeImmutable());

        $entityManager->flush();

        $this->addFlash('success', 'Zgłoszenie zostało odrzucone.');

        return $this->redirectToRoute('app_admin_registration_requests');
    }
}