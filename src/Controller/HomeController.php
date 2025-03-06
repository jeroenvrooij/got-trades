<?php
// src/Controller/HomeController.php
namespace App\Controller;

use App\Entity\Set;
use App\Service\CardFinder;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    private CardFinder $cardFinder;

    public function __construct(
        CardFinder $cardFinder,
    ) {
        $this->cardFinder = $cardFinder;
    }

    #[Route('/')]
    public function helloWorld(): Response
    {
        try {
            $cards = $this->cardFinder->findCardsBySet();

            return $this->render('home/helloworld.html.twig', ['cards' => $cards]);
        } catch (\Exception $exception) {
            throw $this->createNotFoundException($exception->getMessage());
        }
    }

    #[Route('/printing-by-set/{id}')]
    public function printingsBySet(
        #[MapEntity(mapping: ['id' => 'id'], message: 'Set could not be found')]
        Set $set
    ): Response {
        try {
            $cards = $this->cardFinder->findPrintingsBySet($set);

            return $this->render('home/printings.html.twig', ['cards' => $cards]);
        } catch (\Exception $exception) {
            throw $this->createNotFoundException($exception->getMessage());
        }
    }
}