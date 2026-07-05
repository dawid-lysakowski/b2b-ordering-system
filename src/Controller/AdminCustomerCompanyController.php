<?php

namespace App\Controller;

use App\Entity\CustomerCompany;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AdminCustomerCompanyController extends AbstractController
{
    #[Route('/admin/customer-companies', name: 'app_admin_customer_companies')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $customerCompanies = $entityManager
            ->getRepository(CustomerCompany::class)
            ->findBy([], ['createdAt' => 'DESC']);

        return $this->render('admin_customer_company/index.html.twig', [
            'customerCompanies' => $customerCompanies,
        ]);
    }

    #[Route('/admin/customer-companies/{id}', name: 'app_admin_customer_company_show')]
    public function show(CustomerCompany $customerCompany): Response
    {
        return $this->render('admin_customer_company/show.html.twig', [
            'customerCompany' => $customerCompany,
        ]);
    }
}