<?php
// src/Controller/HomeController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class HomeController extends AbstractController
{
    public function __construct(
    ) {
    }

    #[Route('/')]
    #[IsGranted('ROLE_USER', message: 'Website is only accessible for logged in user')]
    public function homePage(): Response
    {
        return $this->render('home/index.html.twig', []);
    }

    #[Route('/privacy-policy')]
    #[IsGranted('ROLE_USER', message: 'Website is only accessible for logged in user')]
    public function privacyPolicy(): Response
    {
        return $this->render('privacy.html.twig', []);
    }

    #[Route('/terms-of-service')]
    #[IsGranted('ROLE_USER', message: 'Website is only accessible for logged in user')]
    public function termsOfService(): Response
    {
        return $this->render('terms.html.twig', []);
    }
}