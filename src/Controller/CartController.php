<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\Product;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;

#[IsGranted('ROLE_CLIENT')]
class CartController extends AbstractController
{
    #[Route('/cart', name: 'app_cart', methods: ['GET'])]
    public function index(
        Security $security,
        EntityManagerInterface $entityManager
    ): Response {
        /** @var User|null $user */
        $user = $security->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        if (!$this->canUseCart($user)) {
            $this->addFlash('danger', 'Nie można korzystać z koszyka dla nieaktywnej firmy.');

            return $this->redirectToRoute('app_products');
        }

        $cart = $this->getOrCreateCart($user, $entityManager);

        $entityManager->flush();

        return $this->render('cart/index.html.twig', [
            'cart' => $cart,
        ]);
    }

    #[Route('/cart/add/{id}', name: 'app_cart_add', methods: ['POST'])]
    public function add(
        Product $product,
        Request $request,
        Security $security,
        EntityManagerInterface $entityManager
    ): Response {
        /** @var User|null $user */
        $user = $security->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        if (!$this->canUseCart($user)) {
            $this->addFlash('danger', 'Nie można dodać produktu do koszyka dla nieaktywnej firmy.');

            return $this->redirectToRoute('app_products');
        }

        if (!$this->isProductAvailable($product)) {
            throw $this->createNotFoundException('Produkt nie jest już dostępny. Usuń go z koszyka, aby kontynuować.');
        }

        $quantity = (int) $request->request->get('quantity', 0);
        $minimumQuantity = $product->getMinimumOrderQuantity() ?? 1;

        if ($quantity < $minimumQuantity) {
            $this->addFlash('danger', 'Ilość nie może być mniejsza niż minimalna ilość zamówienia.');

            return $this->redirectToRoute('app_product_show', [
                'id' => $product->getId(),
            ]);
        }

        $cart = $this->getOrCreateCart($user, $entityManager);
        $existingItem = $this->findCartItemByProduct($cart, $product);

        if ($existingItem) {
            $existingItem->setQuantity($existingItem->getQuantity() + $quantity);
        } else {
            $cartItem = new CartItem();
            $cartItem->setCart($cart);
            $cartItem->setProduct($product);
            $cartItem->setQuantity($quantity);
            $cartItem->setUnitPriceNet($product->getPriceNet());

            $cart->addItem($cartItem);
            $entityManager->persist($cartItem);
        }

        $cart->setUpdatedAt(new \DateTimeImmutable());

        $entityManager->persist($cart);
        $entityManager->flush();

        $this->addFlash('success', 'Produkt został dodany do koszyka.');

        return $this->redirectToRoute('app_cart');
    }

    #[Route('/cart/items/{id}/update', name: 'app_cart_item_update', methods: ['POST'])]
    public function updateItem(
        CartItem $cartItem,
        Request $request,
        Security $security,
        EntityManagerInterface $entityManager
    ): Response {
        /** @var User|null $user */
        $user = $security->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        $this->denyAccessUnlessCartItemBelongsToUser($cartItem, $user);

        $product = $cartItem->getProduct();

        if (!$product || !$this->isProductAvailable($product)) {
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Produkt nie jest już dostępny. Usuń go z koszyka, aby kontynuować.',
                ], Response::HTTP_BAD_REQUEST);
            }

            $this->addFlash('danger', 'Produkt nie jest już dostępny. Usuń go z koszyka, aby kontynuować.');

            return $this->redirectToRoute('app_cart');
        }

        $quantity = (int) $request->request->get('quantity', 0);
        $minimumQuantity = $product->getMinimumOrderQuantity() ?? 1;

        if ($quantity < $minimumQuantity) {
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Ilość nie może być mniejsza niż minimalna ilość zamówienia.',
                    'minimumQuantity' => $minimumQuantity,
                    'currentQuantity' => $cartItem->getQuantity(),
                ], Response::HTTP_BAD_REQUEST);
            }

            $this->addFlash('danger', 'Ilość nie może być mniejsza niż minimalna ilość zamówienia.');

            return $this->redirectToRoute('app_cart');
        }

        $cartItem->setQuantity($quantity);

        $cart = $cartItem->getCart();

        if ($cart) {
            $cart->setUpdatedAt(new \DateTimeImmutable());
        }

        $entityManager->flush();

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse([
                'success' => true,
                'message' => 'Ilość produktu została zaktualizowana.',
                'quantity' => $cartItem->getQuantity(),
                'itemTotalNet' => number_format($cartItem->getTotalNet(), 2, ',', ' ') . ' zł',
                'itemTotalGross' => number_format($cartItem->getTotalGross(), 2, ',', ' ') . ' zł',
                'cartTotalNet' => $cart ? number_format($cart->getTotalNet(), 2, ',', ' ') . ' zł' : '0,00 zł',
                'cartTotalGross' => $cart ? number_format($cart->getTotalGross(), 2, ',', ' ') . ' zł' : '0,00 zł',
            ]);
        }

        $this->addFlash('success', 'Ilość produktu została zaktualizowana.');

        return $this->redirectToRoute('app_cart');
    }

    #[Route('/cart/items/{id}/remove', name: 'app_cart_item_remove', methods: ['POST'])]
    public function removeItem(
        CartItem $cartItem,
        Security $security,
        EntityManagerInterface $entityManager
    ): Response {
        /** @var User|null $user */
        $user = $security->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        $this->denyAccessUnlessCartItemBelongsToUser($cartItem, $user);

        $cart = $cartItem->getCart();

        if ($cart) {
            $cart->removeItem($cartItem);
            $cart->setUpdatedAt(new \DateTimeImmutable());
        }

        $entityManager->remove($cartItem);
        $entityManager->flush();

        $this->addFlash('success', 'Produkt został usunięty z koszyka.');

        return $this->redirectToRoute('app_cart');
    }

    #[Route('/cart/clear', name: 'app_cart_clear', methods: ['POST'])]
    public function clear(
        Security $security,
        EntityManagerInterface $entityManager
    ): Response {
        /** @var User|null $user */
        $user = $security->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        $cart = $this->getOrCreateCart($user, $entityManager);

        foreach ($cart->getItems()->toArray() as $item) {
            $cart->removeItem($item);
            $entityManager->remove($item);
        }

        $cart->setUpdatedAt(new \DateTimeImmutable());

        $entityManager->flush();

        $this->addFlash('success', 'Koszyk został wyczyszczony.');

        return $this->redirectToRoute('app_cart');
    }

    private function getOrCreateCart(User $user, EntityManagerInterface $entityManager): Cart
    {
        $cart = $user->getCart();

        if ($cart) {
            return $cart;
        }

        $cart = new Cart();
        $cart->setClient($user);

        $user->setCart($cart);

        $entityManager->persist($cart);

        return $cart;
    }

    private function findCartItemByProduct(Cart $cart, Product $product): ?CartItem
    {
        foreach ($cart->getItems() as $item) {
            if ($item->getProduct() === $product) {
                return $item;
            }
        }

        return null;
    }

    private function denyAccessUnlessCartItemBelongsToUser(CartItem $cartItem, User $user): void
    {
        if (!$cartItem->getCart() || $cartItem->getCart()->getClient() !== $user) {
            throw $this->createAccessDeniedException('Nie masz dostępu do tej pozycji koszyka.');
        }
    }

    private function canUseCart(User $user): bool
    {
        $company = $user->getCompany();

        if (!$company) {
            return false;
        }

        if (!$company->isActive()) {
            return false;
        }

        return true;
    }

    private function isProductAvailable(Product $product): bool
    {
        if (!$product->isActive()) {
            return false;
        }

        $category = $product->getCategory();

        if (!$category || !$category->isActive()) {
            return false;
        }

        return true;
    }
}