<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Knp\Component\Pager\PaginatorInterface;

#[Route('/')]
class ProductController extends AbstractController
{
    #[Route('/', name: 'app_product_index', methods: ['GET'])]
    public function index(Request $request, ProductRepository $productRepository, PaginatorInterface $paginator, LoggerInterface $logger): Response
    {
        $allProductsQuery = $productRepository->createQueryBuilder('p')->getQuery();
        $logger->info('Index Page');
        // Paginate the results of the query
        $products = $paginator->paginate(
        // Doctrine Query, not results
            $allProductsQuery,
            // Define the page parameter
            $request->query->getInt('page', 1),
            // Items per page
            5
        );

        // Render the twig view
        return $this->render('product/index.html.twig', [
            'products' => $products,
        ]);
    }

    #[Route('/new', name: 'app_product_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, LoggerInterface $logger): Response
    {
        $product = new Product();
        $product->setCreatedDatetime(new \DateTime('now',new DateTimeZone('Asia/Singapore')));
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($product);
            $entityManager->flush();
            $logger->info('Created Product');
            return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('product/new.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_product_show', methods: ['GET'])]
    public function show(Product $product): Response
    {
        return $this->render('product/show.html.twig', [
            'product' => $product,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_product_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Product $product, EntityManagerInterface $entityManager,LoggerInterface $logger): Response
    {
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $logger->info('Updated Product');
            return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('product/edit.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_product_delete', methods: ['POST'])]
    public function delete(Request $request, Product $product, EntityManagerInterface $entityManager,LoggerInterface $logger): Response
    {
        if ($this->isCsrfTokenValid('delete'.$product->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($product);
            $entityManager->flush();
            $logger->info('Deleted Product');
        }

        return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/export', name: 'app_product_export', methods: ['GET', 'POST'])]
    public function export(){
        dump('oe');exit;
        $response = new Response();
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="products.csv"');

// Generate CSV content
        $handle = fopen('php://output', 'w+');
        fputcsv($handle, ['ID', 'Name', 'Description', 'Price', 'Stock Quantity', 'Created At']);
        $products = $productRepository->findAll();
// Fetch products and write to CSV
        foreach ($products as $product) {
            fputcsv($handle, [
                $product->getId(),
                $product->getName(),
                $product->getDescription(),
                $product->getPrice(),
                $product->getStockQuantity(),
                $product->getCreatedAt()->format('Y-m-d H:i:s'),
            ]);
        }
        fclose($handle);

        return $response;

//        $sessionuser = $this->session->get('user');
//        $user = $this->finduser($sessionuser);
//        //search all the datas of type Object
//        $datas = $this->getDoctrine()->getRepository(Object::class)-> findBy(['Event'=> $user->getEvent()]);
//        // normalization and encoding of $datas
//        $encoders = [new CsvEncoder()];
//        $normalizers = array(new ObjectNormalizer());
//        $serializer = new Serializer($normalizers, $encoders);
//        $csvContent = $serializer->serialize($datas, 'csv');
//
//        $response = new Response($csvContent);
//        $response->headers->set('Content-Encoding', 'UTF-8');
//        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
//        $response->headers->set('Content-Disposition', 'attachment; filename=sample.csv');
//        return $response;
    }
}
