<?php

namespace App\Controller;

use App\Entity\CustomerOrder;
use App\Repository\CustomerOrderRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Doctrine\ORM\EntityManagerInterface;

#[Route('/admin/orders')]
final class AdminOrderController extends AbstractController
{
    #[Route('', name: 'app_admin_orders', methods: ['GET'])]
    public function index(CustomerOrderRepository $orderRepository): Response
    {
        return $this->render('admin_order/index.html.twig', [
            'orders' => $orderRepository->findBy([], ['createdAt' => 'DESC']),
        ]);
    }

    #[Route('/{id}', name: 'app_admin_order_show', methods: ['GET'])]
    public function show(CustomerOrder $order): Response
    {
        return $this->render('admin_order/show.html.twig', [
            'order' => $order,
        ]);
    }

    #[Route('/{id}/status', name: 'app_admin_order_status', methods: ['POST'])]
    public function updateStatus(
        CustomerOrder $order,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {

        $status = $request->request->get('status');

        $allowedStatuses = [
            CustomerOrder::STATUS_NEW,
            CustomerOrder::STATUS_ACCEPTED,
            CustomerOrder::STATUS_IN_PROGRESS,
            CustomerOrder::STATUS_COMPLETED,
            CustomerOrder::STATUS_CANCELLED,
        ];

        if (!in_array($status, $allowedStatuses, true)) {
            throw $this->createNotFoundException('Nieprawidłowy status.');
        }

        $order->setStatus($status);
        $order->setUpdatedAt(new \DateTimeImmutable());

        $entityManager->flush();

        $this->addFlash('success', 'Status zamówienia został zaktualizowany.');

        return $this->redirectToRoute('app_admin_order_show', [
            'id' => $order->getId(),
        ]);
    }
}