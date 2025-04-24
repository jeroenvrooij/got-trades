<?php
// src/Controller/HomeController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    public function __construct(
    ) {
    }

    #[Route('/')]
    public function homePage(): Response
    {
        return $this->render('home/index.html.twig', []);
    }

    #[Route('/privacy-policy')]
    public function privacyPolicy(): Response
    {
        return $this->render('privacy.html.twig', []);
    }

    #[Route('/terms-of-service')]
    public function termsOfService(): Response
    {
        return $this->render('terms.html.twig', []);
    }
}