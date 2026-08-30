<?php

namespace App\Command;

use App\Entity\Category;
use App\Entity\Product;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:import-products',
    description: 'Importuje produkty z pliku data/products.json.',
)]
class ImportProductsCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private CategoryRepository $categoryRepository,
        private ProductRepository $productRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Import produktów');

        $filePath = $this->getApplication()
            ->getKernel()
            ->getProjectDir() . '/data/products.json';

        if (!file_exists($filePath)) {
            $io->error(sprintf(
                'Nie znaleziono pliku: %s',
                $filePath
            ));

            return Command::FAILURE;
        }

        $json = file_get_contents($filePath);

        if ($json === false) {
            $io->error('Nie udało się odczytać pliku products.json.');

            return Command::FAILURE;
        }

        $products = json_decode($json, true);

        if (!is_array($products)) {
            $io->error('Plik products.json zawiera niepoprawny JSON.');

            return Command::FAILURE;
        }

        $io->success(sprintf(
            'Wczytano %d produktów.',
            count($products)
        ));

        $categories = [];

        foreach ($this->categoryRepository->findAll() as $category) {
            $categories[$category->getName()] = $category;
        }

        $createdCategories = 0;
        $imported = 0;
        $skipped = 0;

        foreach ($products as $item) {

            if (!isset($categories[$item['category']])) {

                $category = new Category();

                $category
                    ->setName($item['category'])
                    ->setDescription(sprintf(
                        'Kategoria %s.',
                        $item['category']
                    ))
                    ->setIsActive(true);

                $this->entityManager->persist($category);

                $categories[$item['category']] = $category;

                $createdCategories++;

                $io->text(sprintf(
                    'Utworzono kategorię: %s',
                    $item['category']
                ));
            }

            if ($this->productRepository->findOneBy([
                'sku' => $item['sku'],
            ])) {

                $skipped++;

                continue;
            }

            $product = new Product();

            $product
                ->setName($item['name'])
                ->setSku($item['sku'])
                ->setDescription($item['description'])
                ->setCategory($categories[$item['category']])
                ->setPriceNet($item['priceNet'])
                ->setVatRate($item['vatRate'])
                ->setUnit($item['unit'])
                ->setMinimumOrderQuantity($item['minimumOrderQuantity'])
                ->setIsActive(true)
                ->setCreatedAt(new \DateTimeImmutable());

            $this->entityManager->persist($product);

            $imported++;
        }

        $this->entityManager->flush();

        $io->newLine();

        $io->success(sprintf(
            'Utworzono %d kategorii.',
            $createdCategories
        ));

        $io->success(sprintf(
            'Zaimportowano %d produktów.',
            $imported
        ));

        if ($skipped > 0) {
            $io->note(sprintf(
                'Pominięto %d produktów, ponieważ już istnieją.',
                $skipped
            ));
        }

        return Command::SUCCESS;
    }
}