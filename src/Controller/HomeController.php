<?php
// src/Controller/HomeController.php
namespace App\Controller;

use App\Entity\Edition;
use App\Entity\Foiling;
use App\Entity\Set;
use App\Service\CardFinder;
use App\Service\EditionHelper;
use App\Service\FoilingHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    private CardFinder $cardFinder;

    private FoilingHelper $foilingHelper;

    private EditionHelper $editionHelper;

    public function __construct(
        CardFinder $cardFinder,
        FoilingHelper $foilingHelper,
        EditionHelper $editionHelper
    ) {
        $this->cardFinder = $cardFinder;
        $this->foilingHelper = $foilingHelper;
        $this->editionHelper = $editionHelper;
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
        EntityManagerInterface $entityManager,
        #[MapEntity(mapping: ['id' => 'id'], message: 'Set could not be found')]
        Set $set
    ): Response {
        try {
            $cards = $this->cardFinder->findPrintingsBySet($set);
            
            return $this->render('home/printings.html.twig', [
                'editionHelper' => $this->editionHelper,
                'foilingHelper' => $this->foilingHelper,
                'set' => $set, 
                'cards' => $cards,
            ]);
        } catch (\Exception $exception) {
            throw $this->createNotFoundException($exception->getMessage());
        }
    }
}