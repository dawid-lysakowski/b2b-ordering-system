<?php

namespace App\Command;

use App\Entity\ProductImage;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\String\Slugger\AsciiSlugger;

#[AsCommand(
    name: 'app:download-product-images',
    description: 'Pobiera zdjęcia produktów z SerpAPI.',
)]
class DownloadProductImagesCommand extends Command
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private EntityManagerInterface $entityManager,
        private ProductRepository $productRepository,
        private ParameterBagInterface $parameterBag,
        private string $serpApiKey,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Pobieranie zdjęć produktów');

        $products = $this->productRepository->findBy([], [
            'id' => 'ASC',
        ]);

        if (!$products) {
            $io->warning('Brak produktów.');

            return Command::SUCCESS;
        }

        $imagesDirectory = $this->parameterBag->get('kernel.project_dir')
            . '/public/uploads/products';

        if (!is_dir($imagesDirectory)) {
            mkdir($imagesDirectory, 0777, true);
        }

        $progressBar = $io->createProgressBar(count($products));

        $downloaded = 0;
        $skipped = 0;
        $errors = 0;

        $progressBar->start();

        foreach ($products as $product) {

            if ($product->getMainImage()) {
                $skipped++;
                $progressBar->advance();
                continue;
            }

            try {

                $response = $this->httpClient->request(
                    'GET',
                    'https://serpapi.com/search.json',
                    [
                        'timeout' => 20,
                        'query' => [
                            'engine' => 'google_images',
                            'q' => $product->getName(),
                            'api_key' => $this->serpApiKey,
                        ],
                    ]
                );

                $data = $response->toArray(false);

                $imageContent = null;
                $filenameExtension = null;

                $results = array_slice($data['images_results'] ?? [], 0, 5);

                foreach ($results as $result) {

                    if (!isset($result['original'])) {
                        continue;
                    }

                    try {

                        $imageResponse = $this->httpClient->request(
                            'GET',
                            $result['original'],
                            [
                                'timeout' => 20,
                                'headers' => [
                                    'User-Agent' => 'Mozilla/5.0',
                                ],
                            ]
                        );

                        if ($imageResponse->getStatusCode() !== 200) {
                            continue;
                        }

                        $contentType = strtolower(
                            $imageResponse->getHeaders(false)['content-type'][0] ?? ''
                        );

                        if (!str_starts_with($contentType, 'image/')) {
                            continue;
                        }

                        $filenameExtension = match (true) {
                            str_contains($contentType, 'jpeg') => 'jpg',
                            str_contains($contentType, 'jpg') => 'jpg',
                            str_contains($contentType, 'png') => 'png',
                            str_contains($contentType, 'webp') => 'webp',
                            default => null,
                        };

                        if ($filenameExtension === null) {
                            continue;
                        }

                        $imageContent = $imageResponse->getContent();

                        break;

                    } catch (\Throwable) {

                        continue;

                    }

                }

                if ($imageContent === null) {
                    $errors++;
                    $progressBar->advance();
                    continue;
                }

                $slugger = new AsciiSlugger();

                $slug = $slugger
                    ->slug($product->getName())
                    ->lower();

                $filename = sprintf(
                    '%d_%s.%s',
                    $product->getId(),
                    $slug,
                    $filenameExtension
                );

                file_put_contents(
                    $imagesDirectory . '/' . $filename,
                    $imageContent
                );

                $image = new ProductImage();

                $image
                    ->setFilename($filename)
                    ->setDescription($product->getName())
                    ->setPosition(1)
                    ->setCreatedAt(new \DateTimeImmutable());

                $product->addImage($image);

                $this->entityManager->persist($image);

                $downloaded++;

                usleep(500000);

            } catch (\Throwable $exception) {

                $errors++;

                $io->writeln(sprintf(
                    '<error>%s</error>: %s',
                    $product->getName(),
                    $exception->getMessage()
                ));

            }

            $progressBar->advance();
        }

        $this->entityManager->flush();

        $progressBar->finish();

        $io->newLine(2);

        $io->success(sprintf(
            "Pobrano: %d\nPominięto: %d\nBłędy: %d",
            $downloaded,
            $skipped,
            $errors
        ));

        return Command::SUCCESS;
    }
}