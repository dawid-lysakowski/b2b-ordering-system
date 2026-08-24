<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ProductCatalogController extends AbstractController
{
    #[Route('/products', name: 'app_products')]
    public function index(
        Request $request,
        ProductRepository $productRepository,
        CategoryRepository $categoryRepository
    ): Response {
        $search = trim((string) $request->query->get('search', ''));
        $sort = $request->query->get('sort', 'name_asc');

        $categoryId = $request->query->get('category');
        $category = null;

        if ($categoryId) {
            $category = $categoryRepository->find($categoryId);
        }

        $minPrice = $request->query->get('minPrice');
        $maxPrice = $request->query->get('maxPrice');

        $minPrice = is_numeric($minPrice) ? (float) $minPrice : null;
        $maxPrice = is_numeric($maxPrice) ? (float) $maxPrice : null;

        $products = $productRepository->findFiltered(
            $search,
            $category,
            $minPrice,
            $maxPrice,
            $sort,
            true
        );

        return $this->render('product_catalog/index.html.twig', [
            'products' => $products,
            'categories' => $categoryRepository->findBy(
                ['isActive' => true],
                ['name' => 'ASC']
            ),
            'search' => $search,
            'selectedCategory' => $category,
            'minPrice' => $minPrice,
            'maxPrice' => $maxPrice,
            'sort' => $sort,
        ]);
    }

    #[Route('/products/{id}', name: 'app_product_show')]
    public function show(Product $product): Response
    {
        if (!$product->isActive() || !$product->getCategory()->isActive()) {
            throw $this->createNotFoundException('Produkt nie jest dostępny.');
        }

        return $this->render('product_catalog/show.html.twig', [
            'product' => $product,
        ]);
    }
}