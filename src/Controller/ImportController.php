<?php
// src/Controller/ImportController.php
namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;
use stdClass;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ImportController extends AbstractController
{
    #[Route('/import', name: 'import_csv')]
    public function import(Request $request, ValidatorInterface $validator, EntityManagerInterface $entityManager): Response
    {
        $error = null;
        $errorMessages = [];

        if ($request->isMethod('POST')) {
            $file = $request->files->get('csv_file');
            if ($file) {
                $filePath = $file->getRealPath();

                // Handle the CSV import
                try {
                    $data = $this->parseCsvFile($filePath);

                    // Process the data (e.g., save to database)
                    foreach ($data as $row){
                        $product = new Product();
                        $product->setPrice((float)$row[2]);
                        $product->setStockQuantity((int)$row[3]);
                        $product->setCreatedDatetime(new \DateTime('now', new DateTimeZone('Asia/Singapore')));
                        // Validate the product data
                        $errors = $validator->validate($product);

                        if (count($errors) > 0) {
                            foreach ($errors as $error) {
                                $errorMessages[] = $error->getMessage();
                            }
                        } else {
                            // Persist valid data
                            $entityManager->persist($product);
                        }
                    }

                    // Redirect or inform the user about the success
                    // Flush all valid products to the database
                    if (empty($errorMessages)) {
                        $entityManager->flush();
                        return $this->redirectToRoute('import_csv_success');
                    }

                } catch (\Exception $e) {
                    $error = $e->getMessage();
                }
            }
        }

        return $this->render('product/import.html.twig', ['error' => $errorMessages]);
    }

    private function parseCsvFile(string $filePath): array
    {
        if (!file_exists($filePath) || !is_readable($filePath)) {
            throw new \Exception('File not found or not readable.');
        }

        $data = [];
        if (($handle = fopen($filePath, 'r')) !== false) {
            $header = fgetcsv($handle);
            if ($header === false) {
                throw new \Exception('Error reading CSV header.');
            }
            while (($row = fgetcsv($handle)) !== false) {
                if (count($row) === count($header)) {
                    $data[] = $row;
                }
            }

            fclose($handle);
        }
        return $data;
    }

    #[Route('/import/success', name: 'import_csv_success')]
    public function success(): Response
    {
        return new Response('CSV imported successfully.');
    }
}
