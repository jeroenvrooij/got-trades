<?php
// src/Controller/PrintingController.php
namespace App\Controller;

use App\Entity\Set;
use App\Service\ArtVariationsHelper;
use App\Service\CardFinder;
use App\Service\EditionHelper;
use App\Service\FoilingHelper;
use App\Service\RarityHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PrintingController extends AbstractController
{
    private CardFinder $cardFinder;

    private FoilingHelper $foilingHelper;

    private EditionHelper $editionHelper;

    private RarityHelper $rarityHelper;

    private ArtVariationsHelper $artVariationsHelper;

    public function __construct(
        CardFinder $cardFinder,
        FoilingHelper $foilingHelper,
        EditionHelper $editionHelper,
        RarityHelper $rarityHelper,
        ArtVariationsHelper $artVariationsHelper,
    ) {
        $this->cardFinder = $cardFinder;
        $this->foilingHelper = $foilingHelper;
        $this->editionHelper = $editionHelper;
        $this->rarityHelper = $rarityHelper;
        $this->artVariationsHelper = $artVariationsHelper;
    }

    #[Route('/printing-by-set/{setId}')]
    public function printingsBySet(
        EntityManagerInterface $entityManager,
        #[MapEntity(mapping: ['setId' => 'id'], message: 'Set could not be found')]
        Set $set
    ): Response {
        try {
            $cards = $this->cardFinder->findCardsBySet($set);
            
            return $this->render('printing/printings.html.twig', [
                'editionHelper' => $this->editionHelper,
                'foilingHelper' => $this->foilingHelper,
                'rarityHelper' => $this->rarityHelper,
                'artVariationsHelper' => $this->artVariationsHelper,
                'set' => $set, 
                'cards' => $cards,
            ]);
        } catch (\Exception $exception) {
            throw $this->createNotFoundException($exception->getMessage());
        }
    }
}