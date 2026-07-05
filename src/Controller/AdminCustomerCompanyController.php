<?php

namespace App\Controller;

use App\Entity\CustomerCompany;
use App\Entity\Address;
use App\Entity\User;
use App\Form\CustomerCompanyType;
use App\Form\AddressType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
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

    #[Route('/admin/customer-companies/{id}/toggle-active', name: 'app_admin_customer_company_toggle_active', methods: ['POST'])]
    public function toggleActive(
        CustomerCompany $customerCompany,
        EntityManagerInterface $entityManager
    ): Response {
        $customerCompany->setIsActive(!$customerCompany->isActive());
        $customerCompany->setUpdatedAt(new \DateTimeImmutable());

        $entityManager->flush();

        if ($customerCompany->isActive()) {
            $this->addFlash('success', 'Firma klienta została aktywowana.');
        } else {
            $this->addFlash('success', 'Firma klienta została dezaktywowana.');
        }

        return $this->redirectToRoute('app_admin_customer_company_show', [
            'id' => $customerCompany->getId(),
        ]);
    }

    #[Route('/admin/customer-companies/{id}/edit', name: 'app_admin_customer_company_edit')]
    public function edit(
        CustomerCompany $customerCompany,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $form = $this->createForm(CustomerCompanyType::class, $customerCompany);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $customerCompany->setUpdatedAt(new \DateTimeImmutable());

            $entityManager->flush();

            $this->addFlash('success', 'Dane firmy klienta zostały zaktualizowane.');

            return $this->redirectToRoute('app_admin_customer_company_show', [
                'id' => $customerCompany->getId(),
            ]);
        }

        return $this->render('admin_customer_company/edit.html.twig', [
            'customerCompany' => $customerCompany,
            'form' => $form,
        ]);
    }

    #[Route('/admin/customer-companies/{companyId}/addresses/{addressId}/edit', name: 'app_admin_customer_company_address_edit')]
    public function editAddress(
        int $companyId,
        int $addressId,
        EntityManagerInterface $entityManager,
        Request $request
    ): Response {
        $customerCompany = $entityManager
            ->getRepository(CustomerCompany::class)
            ->find($companyId);

        if (!$customerCompany) {
            throw $this->createNotFoundException('Firma klienta nie została znaleziona.');
        }

        $address = $entityManager
            ->getRepository(Address::class)
            ->find($addressId);

        if (!$address || $address->getCompany()?->getId() !== $customerCompany->getId()) {
            throw $this->createNotFoundException('Adres nie został znaleziony dla wskazanej firmy.');
        }

        $form = $this->createForm(AddressType::class, $address);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $customerCompany->setUpdatedAt(new \DateTimeImmutable());

            $entityManager->flush();

            $this->addFlash('success', 'Adres firmy został zaktualizowany.');

            return $this->redirectToRoute('app_admin_customer_company_show', [
                'id' => $customerCompany->getId(),
            ]);
        }

        return $this->render('admin_customer_company/edit_address.html.twig', [
            'customerCompany' => $customerCompany,
            'address' => $address,
            'form' => $form,
        ]);
    }

    #[Route('/admin/customer-companies/{companyId}/users/{userId}/toggle-active', name: 'app_admin_customer_company_user_toggle_active', methods: ['POST'])]
    public function toggleUserActive(
        int $companyId,
        int $userId,
        EntityManagerInterface $entityManager
    ): Response {
        $customerCompany = $entityManager
            ->getRepository(CustomerCompany::class)
            ->find($companyId);

        if (!$customerCompany) {
            throw $this->createNotFoundException('Firma klienta nie została znaleziona.');
        }

        $user = $entityManager
            ->getRepository(User::class)
            ->find($userId);

        if (!$user || $user->getCompany()?->getId() !== $customerCompany->getId()) {
            throw $this->createNotFoundException('Użytkownik nie został znaleziony dla wskazanej firmy.');
        }

        $user->setIsActive(!$user->isActive());
        $user->setUpdatedAt(new \DateTimeImmutable());

        $entityManager->flush();

        if ($user->isActive()) {
            $this->addFlash('success', 'Użytkownik klienta został aktywowany.');
        } else {
            $this->addFlash('success', 'Użytkownik klienta został dezaktywowany.');
        }

        return $this->redirectToRoute('app_admin_customer_company_show', [
            'id' => $customerCompany->getId(),
        ]);
    }
}