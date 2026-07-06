<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\ProductImage;
use App\Form\ProductType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

final class AdminProductController extends AbstractController
{
    #[Route('/admin/products', name: 'app_admin_products')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $products = $entityManager
            ->getRepository(Product::class)
            ->findBy([], ['id' => 'DESC']);

        return $this->render('admin_product/index.html.twig', [
            'products' => $products,
        ]);
    }

    #[Route('/admin/products/new', name: 'app_admin_product_new')]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger
    ): Response {
        $product = new Product();
        $product->setIsActive(true);
        $product->setCreatedAt(new \DateTimeImmutable());

        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->handleProductImagesUpload($form, $product, $slugger, $entityManager);

            $entityManager->persist($product);
            $entityManager->flush();

            $this->addFlash('success', 'Produkt został dodany.');

            return $this->redirectToRoute('app_admin_products');
        }

        return $this->render('admin_product/form.html.twig', [
            'form' => $form,
            'product' => $product,
            'title' => 'Nowy produkt',
            'buttonLabel' => 'Dodaj produkt',
        ]);
    }

    #[Route('/admin/products/{id}/edit', name: 'app_admin_product_edit')]
    public function edit(
        Product $product,
        Request $request,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger
    ): Response {
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $product->setUpdatedAt(new \DateTimeImmutable());

            $this->handleProductImagesUpload($form, $product, $slugger, $entityManager);

            $entityManager->flush();

            $this->addFlash('success', 'Produkt został zaktualizowany.');

            return $this->redirectToRoute('app_admin_products');
        }

        return $this->render('admin_product/form.html.twig', [
            'form' => $form,
            'product' => $product,
            'title' => 'Edycja produktu',
            'buttonLabel' => 'Zapisz zmiany',
        ]);
    }

    #[Route('/admin/products/{id}/toggle-active', name: 'app_admin_product_toggle_active', methods: ['POST'])]
    public function toggleActive(
        Product $product,
        EntityManagerInterface $entityManager
    ): Response {
        $product->setIsActive(!$product->isActive());
        $product->setUpdatedAt(new \DateTimeImmutable());

        $entityManager->flush();

        if ($product->isActive()) {
            $this->addFlash('success', 'Produkt został aktywowany.');
        } else {
            $this->addFlash('success', 'Produkt został dezaktywowany.');
        }

        return $this->redirectToRoute('app_admin_products');
    }

    private function handleProductImagesUpload(
        $form,
        Product $product,
        SluggerInterface $slugger,
        EntityManagerInterface $entityManager
    ): void {
        /** @var UploadedFile[]|null $imageFiles */
        $imageFiles = $form->get('imageFiles')->getData();

        if (!$imageFiles) {
            return;
        }

        $currentMaxPosition = 0;

        foreach ($product->getImages() as $image) {
            if ($image->getPosition() !== null && $image->getPosition() > $currentMaxPosition) {
                $currentMaxPosition = $image->getPosition();
            }
        }

        foreach ($imageFiles as $imageFile) {
            $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = (string) $slugger->slug($originalFilename);
            $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

            try {
                $imageFile->move(
                    $this->getParameter('kernel.project_dir') . '/public/uploads/products',
                    $newFilename
                );
            } catch (FileException) {
                $this->addFlash('danger', 'Nie udało się zapisać jednego ze zdjęć produktu.');
                continue;
            }

            $currentMaxPosition++;

            $productImage = new ProductImage();
            $productImage->setProduct($product);
            $productImage->setFilename($newFilename);
            $productImage->setPosition($currentMaxPosition);
            $productImage->setCreatedAt(new \DateTimeImmutable());

            $product->addImage($productImage);

            $entityManager->persist($productImage);
        }
    }
}