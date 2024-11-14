<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\CustomerRepository;
class CustomerController extends AbstractController
{
    #[Route('/', name: 'app_customer')]
    public function index(CustomerRepository $customerRepository): Response
    {
        $topMedications = $customerRepository->findTopMedications(30);
        $topCountriesByGroup = $customerRepository->findTopCountriesByGroup();

        return $this->render('customer/index.html.twig', [
            'topMedications' => $topMedications,
            'topCountriesByGroup' => $topCountriesByGroup,
        ]);
    }
}
