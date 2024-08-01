<?php
// src/Controller/ExportController.php
namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ExportController extends AbstractController
{
#[Route('/export', name: 'export_csv')]
    public function export(ProductRepository $productRepository): Response
    {
// Render the Twig template to get the CSV content
        $csvContent = $this->renderView('product/export.csv.twig', [
            'data' => $productRepository->findAll(),
        ]);

// Create a Response with CSV headers
        $response = new Response($csvContent);
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="export.csv"');

        return $response;
    }
}
