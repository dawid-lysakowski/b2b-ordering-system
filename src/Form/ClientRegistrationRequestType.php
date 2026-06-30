<?php

namespace App\Form;

use App\Entity\ClientRegistrationRequest;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ClientRegistrationRequestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('companyName', TextType::class, [
                'label' => 'Nazwa firmy',
            ])
            ->add('taxNumber', TextType::class, [
                'label' => 'NIP',
            ])
            ->add('companyEmail', EmailType::class, [
                'label' => 'Adres e-mail firmy',
            ])
            ->add('companyPhone', TelType::class, [
                'label' => 'Telefon firmy',
            ])
            ->add('userEmail', EmailType::class, [
                'label' => 'Adres e-mail użytkownika',
            ])
            ->add('userFirstName', TextType::class, [
                'label' => 'Imię użytkownika',
            ])
            ->add('userLastName', TextType::class, [
                'label' => 'Nazwisko użytkownika',
            ])
            ->add('plainPassword', PasswordType::class, [
                'label' => 'Hasło',
                'mapped' => false,
            ])
            ->add('billingStreet', TextType::class, [
                'label' => 'Ulica',
                'mapped' => false,
            ])
            ->add('billingBuildingNumber', TextType::class, [
                'label' => 'Numer budynku',
                'mapped' => false,
            ])
            ->add('billingApartmentNumber', TextType::class, [
                'label' => 'Numer lokalu',
                'mapped' => false,
                'required' => false,
            ])
            ->add('billingPostalCode', TextType::class, [
                'label' => 'Kod pocztowy',
                'mapped' => false,
            ])
            ->add('billingCity', TextType::class, [
                'label' => 'Miasto',
                'mapped' => false,
            ])
            ->add('billingCountry', TextType::class, [
                'label' => 'Kraj',
                'mapped' => false,
                'data' => 'Polska',
            ])

            // Delivery address section
            // Display this checkbox as the first field in step 4 in the Twig template.
            ->add('deliverySameAsBilling', CheckboxType::class, [
                'label' => 'Adres dostawy jest taki sam jak adres rozliczeniowy',
                'mapped' => false,
                'required' => false,
                'data' => true,
            ])
            ->add('deliveryStreet', TextType::class, [
                'label' => 'Ulica',
                'mapped' => false,
                'required' => false,
            ])
            ->add('deliveryBuildingNumber', TextType::class, [
                'label' => 'Numer budynku',
                'mapped' => false,
                'required' => false,
            ])
            ->add('deliveryApartmentNumber', TextType::class, [
                'label' => 'Numer lokalu',
                'mapped' => false,
                'required' => false,
            ])
            ->add('deliveryPostalCode', TextType::class, [
                'label' => 'Kod pocztowy',
                'mapped' => false,
                'required' => false,
            ])
            ->add('deliveryCity', TextType::class, [
                'label' => 'Miasto',
                'mapped' => false,
                'required' => false,
            ])
            ->add('deliveryCountry', TextType::class, [
                'label' => 'Kraj',
                'mapped' => false,
                'required' => false,
                'data' => 'Polska',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ClientRegistrationRequest::class,
        ]);
    }
}