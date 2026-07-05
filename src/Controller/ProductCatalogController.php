<?php

namespace App\Controller;

use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ProductCatalogController extends AbstractController
{
    #[Route('/products', name: 'app_products')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $products = $entityManager
            ->getRepository(Product::class)
            ->createQueryBuilder('p')
            ->join('p.category', 'c')
            ->andWhere('p.isActive = :active')
            ->andWhere('c.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('p.name', 'ASC')
            ->getQuery()
            ->getResult();

        return $this->render('product_catalog/index.html.twig', [
            'products' => $products,
        ]);
    }
}