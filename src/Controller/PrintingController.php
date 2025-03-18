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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

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
    #[IsGranted('ROLE_USER', message: 'Viewing sets is only for logged in users')]
    public function printingsBySet(
        Request $request,
        EntityManagerInterface $entityManager,
        #[MapEntity(mapping: ['setId' => 'id'], message: 'Set could not be found')]
        Set $set
    ): Response {
        try {
            $foiling = $request->query->get('foiling-filter');

            $cards = $this->cardFinder->findCardsBySet($set, $foiling);

            if ($request->headers->get('Turbo-Frame') === 'printing_table') {
                return $this->render('printing/printings_table.html.twig', [
                    'editionHelper' => $this->editionHelper,
                    'foilingHelper' => $this->foilingHelper,
                    'rarityHelper' => $this->rarityHelper,
                    'artVariationsHelper' => $this->artVariationsHelper,
                    'set' => $set, 
                    'cards' => $cards,
                    'foiling' => $foiling,
                ]);
            }

            return $this->render('printing/printings.html.twig', [
                'editionHelper' => $this->editionHelper,
                'foilingHelper' => $this->foilingHelper,
                'rarityHelper' => $this->rarityHelper,
                'artVariationsHelper' => $this->artVariationsHelper,
                'set' => $set, 
                'cards' => $cards,
                'foiling' => $foiling,
            ]);
        } catch (\Exception $exception) {
            return $this->render('printing/empty_table.html.twig', [
            ]);
        }
    }
}