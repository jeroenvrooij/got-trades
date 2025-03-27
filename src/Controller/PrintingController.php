<?php
// src/Controller/PrintingController.php
namespace App\Controller;

use App\Entity\Set;
use App\Entity\UserCardPrintings;
use App\Service\ArtVariationsHelper;
use App\Service\CardFinder;
use App\Service\EditionHelper;
use App\Service\FoilingHelper;
use App\Service\RarityHelper;
use App\Service\UserCollectionManager;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\UX\Turbo\TurboBundle;

class PrintingController extends AbstractController
{
    private CardFinder $cardFinder;

    private FoilingHelper $foilingHelper;

    private EditionHelper $editionHelper;

    private RarityHelper $rarityHelper;

    private ArtVariationsHelper $artVariationsHelper;

    private UserCollectionManager $userCollectionManager;

    public function __construct(
        CardFinder $cardFinder,
        FoilingHelper $foilingHelper,
        EditionHelper $editionHelper,
        RarityHelper $rarityHelper,
        ArtVariationsHelper $artVariationsHelper,
        UserCollectionManager $userCollectionManager,
    ) {
        $this->cardFinder = $cardFinder;
        $this->foilingHelper = $foilingHelper;
        $this->editionHelper = $editionHelper;
        $this->rarityHelper = $rarityHelper;
        $this->artVariationsHelper = $artVariationsHelper;
        $this->userCollectionManager = $userCollectionManager;
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
            $foiling = $request->query->get('foiling-filter', '');

            $cards = $this->cardFinder->findCardsBySetAndFoiling($set, $foiling);

            if (TurboBundle::STREAM_FORMAT === $request->getPreferredFormat()) {
                $request->setRequestFormat(TurboBundle::STREAM_FORMAT);
               
                return $this->renderBlock('printing/printings_table.html.twig', 'printing_table', [
                    'editionHelper' => $this->editionHelper,
                    'foilingHelper' => $this->foilingHelper,
                    'rarityHelper' => $this->rarityHelper,
                    'artVariationsHelper' => $this->artVariationsHelper,
                    'userCollectionManager' => $this->userCollectionManager,
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
                'userCollectionManager' => $this->userCollectionManager,
                'set' => $set, 
                'cards' => $cards,
                'foiling' => $foiling,
            ]);
        } catch (\Exception $exception) {
            dump($exception);die();
            return $this->renderBlock('printing/empty_table.html.twig', 'printing_table', [
            ]);
        }
    }

    #[Route('/update-user-collection', methods: ['POST'])]
    #[IsGranted('ROLE_USER', message: 'Updating collection is for logged in users')]
    public function updateUserCollection(
        Request $request,
        EntityManagerInterface $entityManager,
        LoggerInterface $logger
    ): Response {
        try {
            $data = json_decode($request->getContent(), true);
            
            $amount = $data['amount'] ?? null;

            if ($amount !== null) {
                $userCardPrintings = $entityManager->getRepository(UserCardPrintings::class)->find([
                    'user' => $this->getUser(),
                    'cardPrintingUniqueId' => $data['id'],
                ]);
                if (null === $userCardPrintings) {
                    $userCardPrintings = new UserCardPrintings();
                    $userCardPrintings->setUser($this->getUser());
                    $userCardPrintings->setCardPrintingUniqueId($data['id']);
                    $entityManager->persist($userCardPrintings);
                }
                if ('0' === $data['amount']) {
                    $entityManager->remove($userCardPrintings);
                } else {
                    $userCardPrintings->setCollectionAmount($data['amount']);
                }
                $entityManager->flush();
            }

            $this->addFlash('success', 'Quantity updated successfully!');
            return new JsonResponse(['success' => true]);
        } catch (\Exception $exception) {
            dump($exception);die();
        }
    }

    #[Route('/flash-messages', name: 'flash_messages')]
    public function flashMessages(Request $request): Response
    {
        return $this->render('partials/_flash_messages.html.twig');
    }
}