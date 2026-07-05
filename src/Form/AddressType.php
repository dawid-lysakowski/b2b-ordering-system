<?php

namespace App\Form;

use App\Entity\Address;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddressType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('street', TextType::class, [
                'label' => 'Ulica',
            ])
            ->add('buildingNumber', TextType::class, [
                'label' => 'Numer budynku',
            ])
            ->add('apartmentNumber', TextType::class, [
                'label' => 'Numer lokalu',
                'required' => false,
            ])
            ->add('postalCode', TextType::class, [
                'label' => 'Kod pocztowy',
            ])
            ->add('city', TextType::class, [
                'label' => 'Miasto',
            ])
            ->add('country', TextType::class, [
                'label' => 'Kraj',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Address::class,
        ]);
    }
}