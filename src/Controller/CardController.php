<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

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
}