<?php

namespace App\Controller;

use App\Entity\CustomerOrder;
use App\Entity\OrderItem;
use App\Entity\User;
use App\Entity\Address;
use App\Service\OrderNumberGenerator;
use App\Repository\CustomerOrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_CLIENT')]
final class OrderController extends AbstractController
{
    #[Route('/orders/create', name: 'app_order_create', methods: ['POST'])]
    public function create(
        Security $security,
        EntityManagerInterface $entityManager,
        OrderNumberGenerator $orderNumberGenerator
    ): Response {
        /** @var User|null $user */
        $user = $security->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        $cart = $user->getCart();

        if (!$cart || $cart->getItems()->isEmpty()) {
            $this->addFlash('danger', 'Koszyk jest pusty.');

            return $this->redirectToRoute('app_cart');
        }

        $company = $user->getCompany();

        if (!$company) {
            throw $this->createAccessDeniedException();
        }

        foreach ($cart->getItems() as $cartItem) {
            $product = $cartItem->getProduct();

            if (!$product->isActive()) {
                $this->addFlash(
                    'danger',
                    sprintf(
                        'Produkt "%s" nie jest już dostępny. Usuń go z koszyka, aby złożyć zamówienie.',
                        $product->getName()
                    )
                );

                return $this->redirectToRoute('app_cart');
            }

            if (
                $product->getCategory()
                && !$product->getCategory()->isActive()
            ) {
                $this->addFlash(
                    'danger',
                    sprintf(
                        'Kategoria produktu "%s" jest nieaktywna.',
                        $product->getName()
                    )
                );

                return $this->redirectToRoute('app_cart');
            }

            if ($cartItem->getQuantity() < $product->getMinimumOrderQuantity()) {
                $this->addFlash(
                    'danger',
                    sprintf(
                        'Minimalna ilość zamówienia dla produktu "%s" wynosi %s.',
                        $product->getName(),
                        $product->getMinimumOrderQuantity()
                    )
                );

                return $this->redirectToRoute('app_cart');
            }
        }

        $order = new CustomerOrder();

        $order->setOrderNumber(
            $orderNumberGenerator->generate()
        );

        $order->setCompany($company);
        $order->setClient($user);

        $order->setStatus(CustomerOrder::STATUS_NEW);

        $order->setCompanyName($company->getName());
        $order->setCompanyTaxNumber($company->getTaxNumber());

        $order->setCreatedAt(new \DateTimeImmutable());

        $order->setCustomerComment(null);

        $order->setDeliveryAddressSnapshot(
            $this->getDeliveryAddressSnapshot($user)
        );

        $totalNet = 0;
        $totalGross = 0;

        foreach ($cart->getItems() as $cartItem) {

            $product = $cartItem->getProduct();

            $item = new OrderItem();

            $item->setCustomerOrder($order);

            $item->setProductName($product->getName());
            $item->setProductSku($product->getSku());

            $item->setQuantity($cartItem->getQuantity());

            $item->setUnitPriceNet($cartItem->getUnitPriceNet());

            $item->setVatRate($product->getVatRate());

            $item->setTotalNet(number_format($cartItem->getTotalNet(), 2, '.', ''));

            $item->setTotalGross(number_format($cartItem->getTotalGross(), 2, '.', ''));

            $order->addItem($item);

            $entityManager->persist($item);

            $totalNet += $cartItem->getTotalNet();
            $totalGross += $cartItem->getTotalGross();
        }

        $order->setTotalNet(number_format($totalNet, 2, '.', ''));

        $order->setTotalGross(number_format($totalGross, 2, '.', ''));

        $entityManager->persist($order);

        foreach ($cart->getItems()->toArray() as $cartItem) {
            $cart->removeItem($cartItem);
            $entityManager->remove($cartItem);
        }

        $entityManager->flush();

        $this->addFlash('success', 'Zamówienie zostało złożone.');

        return $this->redirectToRoute('app_orders');
    }

    #[Route('/orders', name: 'app_orders', methods: ['GET'])]
    public function index(
        Security $security,
        CustomerOrderRepository $orderRepository
    ): Response {
        /** @var User|null $user */
        $user = $security->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        $company = $user->getCompany();

        if (!$company) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('order/index.html.twig', [
            'orders' => $orderRepository->findBy(
                ['company' => $company],
                ['createdAt' => 'DESC']
            ),
        ]);
    }

    #[Route('/orders/{id}', name: 'app_order_show', methods: ['GET'])]
    public function show(
        CustomerOrder $order,
        Security $security
    ): Response {
        /** @var User|null $user */
        $user = $security->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        if ($order->getCompany() !== $user->getCompany()) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('order/show.html.twig', [
            'order' => $order,
        ]);
    }

    private function getDeliveryAddressSnapshot(User $user): ?string
    {
        $company = $user->getCompany();

        if (!$company) {
            return null;
        }

        foreach ($company->getAddresses() as $address) {
            if (
                $address->getAddressType() === Address::TYPE_DELIVERY ||
                $address->getAddressType() === Address::TYPE_BOTH
            ) {
                return $address->getFormattedAddress();
            }
        }

        return null;
    }
}