<?php

namespace App\Controller;

use App\Entity\Address;
use App\Entity\Cart;
use App\Entity\CustomerCompany;
use App\Entity\User;
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

    #[Route('/admin/registration-requests/{id}/approve', name: 'app_admin_registration_request_approve', methods: ['POST'])]
    public function approve(
        ClientRegistrationRequest $registrationRequest,
        EntityManagerInterface $entityManager
    ): Response {
        if ($registrationRequest->getStatus() !== 'pending') {
            $this->addFlash('warning', 'To zgłoszenie zostało już zweryfikowane.');

            return $this->redirectToRoute('app_admin_registration_request_show', [
                'id' => $registrationRequest->getId(),
            ]);
        }

        $company = new CustomerCompany();
        $company->setName($registrationRequest->getCompanyName());
        $company->setTaxNumber($registrationRequest->getTaxNumber());
        $company->setEmail($registrationRequest->getCompanyEmail());
        $company->setPhone($registrationRequest->getCompanyPhone());
        $company->setIsActive(true);
        $company->setCreatedAt(new \DateTimeImmutable());

        $entityManager->persist($company);

        $billingAddressData = $registrationRequest->getBillingAddressData();
        $deliveryAddressData = $registrationRequest->getDeliveryAddressData();

        if ($billingAddressData == $deliveryAddressData) {
            $address = $this->createAddressFromData($billingAddressData, 'both', $company);
            $entityManager->persist($address);
        } else {
            $billingAddress = $this->createAddressFromData($billingAddressData, 'billing', $company);
            $deliveryAddress = $this->createAddressFromData($deliveryAddressData, 'delivery', $company);

            $entityManager->persist($billingAddress);
            $entityManager->persist($deliveryAddress);
        }

        $client = new User();
        $client->setEmail($registrationRequest->getUserEmail());
        $client->setPassword($registrationRequest->getPasswordHash());
        $client->setRoles(['ROLE_CLIENT']);
        $client->setFirstName($registrationRequest->getUserFirstName());
        $client->setLastName($registrationRequest->getUserLastName());
        $client->setIsActive(true);
        $client->setCreatedAt(new \DateTimeImmutable());
        $client->setCompany($company);

        $entityManager->persist($client);

        $cart = new Cart();
        $cart->setClient($client);
        $cart->setCreatedAt(new \DateTimeImmutable());

        $entityManager->persist($cart);

        $registrationRequest->setStatus('approved');
        $registrationRequest->setReviewedAt(new \DateTimeImmutable());

        $entityManager->flush();

        $this->addFlash('success', 'Zgłoszenie zostało zaakceptowane. Utworzono firmę, konto klienta oraz koszyk.');

        return $this->redirectToRoute('app_admin_registration_requests');
    }

    private function createAddressFromData(
        array $addressData,
        string $addressType,
        CustomerCompany $company
    ): Address {
        $address = new Address();
        $address->setStreet($addressData['street'] ?? '');
        $address->setBuildingNumber($addressData['buildingNumber'] ?? '');
        $address->setApartmentNumber($addressData['apartmentNumber'] ?? null);
        $address->setPostalCode($addressData['postalCode'] ?? '');
        $address->setCity($addressData['city'] ?? '');
        $address->setCountry($addressData['country'] ?? '');
        $address->setAddressType($addressType);
        $address->setCompany($company);

        return $address;
    }
}