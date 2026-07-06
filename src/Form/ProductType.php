<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Product;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\PercentType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\File;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nazwa produktu',
            ])
            ->add('sku', TextType::class, [
                'label' => 'SKU',
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Opis',
                'required' => false,
                'attr' => [
                    'rows' => 4,
                ],
            ])
            ->add('priceNet', MoneyType::class, [
                'label' => 'Cena netto',
                'currency' => 'PLN',
            ])
            ->add('vatRate', PercentType::class, [
                'label' => 'Stawka VAT',
                'type' => 'integer',
                'scale' => 2,
            ])
            ->add('unit', TextType::class, [
                'label' => 'Jednostka',
            ])
            ->add('minimumOrderQuantity', NumberType::class, [
                'label' => 'Minimalna ilość',
                'scale' => 0,
            ])
            ->add('category', EntityType::class, [
                'label' => 'Kategoria',
                'class' => Category::class,
                'choice_label' => 'name',
                'placeholder' => 'Wybierz kategorię',
            ])
            ->add('imageFiles', FileType::class, [
                'label' => 'Zdjęcia produktu',
                'mapped' => false,
                'required' => false,
                'multiple' => true,
                'constraints' => [
                    new All([
                        new File([
                            'maxSize' => '5M',
                            'mimeTypes' => [
                                'image/jpeg',
                                'image/png',
                                'image/webp',
                            ],
                            'mimeTypesMessage' => 'Dodaj plik graficzny w formacie JPG, PNG lub WebP.',
                        ]),
                    ]),
                ],
                'attr' => [
                    'accept' => 'image/jpeg,image/png,image/webp',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}