<?php

namespace App\Controller;

use App\Entity\ClientRegistrationRequest;
use App\Entity\User;
use App\Form\ClientRegistrationRequestType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

final class RegistrationRequestController extends AbstractController
{
    #[Route('/register', name: 'app_register_request')]
    public function index(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $registrationRequest = new ClientRegistrationRequest();

        $form = $this->createForm(ClientRegistrationRequestType::class, $registrationRequest);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $registrationRequest->setStatus('pending');
            $registrationRequest->setCreatedAt(new \DateTimeImmutable());

            $plainPassword = $form->get('plainPassword')->getData();

            $registrationRequest->setPasswordHash(
                $passwordHasher->hashPassword(
                    new User(),
                    $plainPassword
                )
            );

            $billingAddressData = [
                'street' => $form->get('billingStreet')->getData(),
                'buildingNumber' => $form->get('billingBuildingNumber')->getData(),
                'apartmentNumber' => $form->get('billingApartmentNumber')->getData(),
                'postalCode' => $form->get('billingPostalCode')->getData(),
                'city' => $form->get('billingCity')->getData(),
                'country' => $form->get('billingCountry')->getData(),
            ];

            $registrationRequest->setBillingAddressData($billingAddressData);

            $deliverySameAsBilling = $form->get('deliverySameAsBilling')->getData();

            if ($deliverySameAsBilling) {
                $registrationRequest->setDeliveryAddressData($billingAddressData);
            } else {
                $registrationRequest->setDeliveryAddressData([
                    'street' => $form->get('deliveryStreet')->getData(),
                    'buildingNumber' => $form->get('deliveryBuildingNumber')->getData(),
                    'apartmentNumber' => $form->get('deliveryApartmentNumber')->getData(),
                    'postalCode' => $form->get('deliveryPostalCode')->getData(),
                    'city' => $form->get('deliveryCity')->getData(),
                    'country' => $form->get('deliveryCountry')->getData(),
                ]);
            }

            $entityManager->persist($registrationRequest);
            $entityManager->flush();

            $this->addFlash('success', 'Zgłoszenie rejestracyjne zostało wysłane do weryfikacji.');

            return $this->redirectToRoute('app_home');
        }

        return $this->render('registration_request/index.html.twig', [
            'form' => $form,
        ]);
    }
}