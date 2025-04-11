<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class CardController extends AbstractController
{
    #[Route('/cards/set-overview')]
    public function setOverviewPage(
    ): Response {
        try {
            return $this->render('card/set_overview.html.twig', []);
        } catch (\Exception $exception) {
            throw $this->createNotFoundException($exception->getMessage());
        }
    }

    #[Route('/cards/class-overview')]
    public function classOverviewPage(
    ): Response {
        try {
            return $this->render('card/class_overview.html.twig', []);
        } catch (\Exception $exception) {
            throw $this->createNotFoundException($exception->getMessage());
        }
    }
}