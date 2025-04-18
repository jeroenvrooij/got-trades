<?php
// src/Controller/PrintingController.php
namespace App\Controller;

use App\Entity\Set;
use App\Entity\UserCardPrintings;
use App\Form\CardFilterFormType;
use App\Service\ArtVariationsHelper;
use App\Service\CardFinder;
use App\Service\EditionHelper;
use App\Service\FoilingHelper;
use App\Service\RarityHelper;
use App\Service\UserCollectionManager;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\UX\Turbo\TurboBundle;

class CollectionController extends AbstractController
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

    #[Route('/manage-collection-by-set/{setId}')]
    public function manageCollectionBySet(
        Request $request,
        #[MapEntity(mapping: ['setId' => 'id'], message: 'Set could not be found')]
        Set $set
    ): Response {
        $hideOwnedCards = false;
        $collectorView = false;
        $cardName = '';
        $foiling = '';
        
        $form = $this->createForm(CardFilterFormType::class);
        $form->handleRequest($request);

        $collectedCards = new ArrayCollection();
        $collectedPrintings = new ArrayCollection();
        if (null !== $this->getUser()) {
            $collectedCards = $this->userCollectionManager->getCollectedCardsBy($this->getUser(), $set);
            $collectedPrintings = $this->userCollectionManager->getCollectedPrintingsBy($this->getUser(), $set);
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();
            $hideOwnedCards = $formData['hide'];
            $collectorView = $formData['collectorView'];
            $cardName = $formData['cardName'];
            $foiling = $formData['foiling'];

            // 🔥 The magic happens here! 🔥
            if (TurboBundle::STREAM_FORMAT === $request->getPreferredFormat()) {
                // If the request comes from Turbo, set the content type as text/vnd.turbo-stream.html and only send the HTML to update
                $request->setRequestFormat(TurboBundle::STREAM_FORMAT);
                
                $cards = $this->cardFinder->findCardsBySet($set, $hideOwnedCards, $collectorView, $foiling, $cardName);

                return $this->renderBlock('collection/overview.html.twig', 'printing_table', [
                    'editionHelper' => $this->editionHelper,
                    'foilingHelper' => $this->foilingHelper,
                    'rarityHelper' => $this->rarityHelper,
                    'artVariationsHelper' => $this->artVariationsHelper,
                    'userCollectionManager' => $this->userCollectionManager,
                    'cardPrintingsTree' => $cards,
                    'userCollectedCards' => $collectedCards,
                    'userCollectedPrintings' => $collectedPrintings,
                    'collectorView' => $collectorView,
                    'pageTitle' => $set->getName(),
                ]);
            }

            // If the client doesn't support JavaScript, or isn't using Turbo, the form still works as usual.
            // Symfony UX Turbo is all about progressively enhancing your applications!
            return $this->redirectToRoute('app_collection_managecollectionbyset', ['setId', $set->getId()], Response::HTTP_SEE_OTHER);
        }

        $cards = $this->cardFinder->findCardsBySet($set, $hideOwnedCards, $collectorView, $foiling, $cardName);
        
        return $this->render('collection/overview.html.twig', [
            'editionHelper' => $this->editionHelper,
            'foilingHelper' => $this->foilingHelper,
            'rarityHelper' => $this->rarityHelper,
            'artVariationsHelper' => $this->artVariationsHelper,
            'userCollectionManager' => $this->userCollectionManager,
            'cardPrintingsTree' => $cards,
            'userCollectedCards' => $collectedCards,
            'userCollectedPrintings' => $collectedPrintings,
            'form' => $form,
            'collectorView' => $collectorView,
            'pageTitle' => $set->getName(),
        ]);
    }

    #[Route('/manage-collection-by-class/{className}')]
    public function manageCollectionByClass(
        Request $request,
        ?string $className
    ): Response {
        $allowedClasses = [
            'adjudicator',
            'assassin',
            'bard',
            'brute',
            'generic',
            'guardian',
            'illusionist',
            'mechanologist',
            'merchant',
            'ninja',
            'ranger',
            'runeblade',
            'shapeshifter',
            'warrior',
            'wizard',
        ]; 

        if (!in_array($className, $allowedClasses, true)) {
            throw $this->createNotFoundException("Invalid class: $className");
        }

        $form = $this->createForm(CardFilterFormType::class);
        $form->handleRequest($request);
        
        $collectedCards = new ArrayCollection();
        $collectedPrintings = new ArrayCollection();
        if (null !== $this->getUser()) {
            $collectedCards = $this->userCollectionManager->getCollectedCardsBy($this->getUser(), null, $className);
            $collectedPrintings = $this->userCollectionManager->getCollectedPrintingsBy($this->getUser(), null, $className);
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();
            $foiling = $formData['foiling'];
            $hideOwnedCards = $formData['hide'];
            $cardName = $formData['cardName'];
            $collectorView = $formData['collectorView'];

            // 🔥 The magic happens here! 🔥
            if (TurboBundle::STREAM_FORMAT === $request->getPreferredFormat()) {
                // If the request comes from Turbo, set the content type as text/vnd.turbo-stream.html and only send the HTML to update
                $request->setRequestFormat(TurboBundle::STREAM_FORMAT);
                $cards = $this->cardFinder->findCardsByClass($className, $hideOwnedCards, $collectorView, $foiling, $cardName);
                
                return $this->renderBlock('collection/overview.html.twig', 'printing_table', [
                    'editionHelper' => $this->editionHelper,
                    'foilingHelper' => $this->foilingHelper,
                    'rarityHelper' => $this->rarityHelper,
                    'artVariationsHelper' => $this->artVariationsHelper,
                    'userCollectionManager' => $this->userCollectionManager,
                    'cardPrintingsTree' => $cards,
                    'userCollectedCards' => $collectedCards,
                    'userCollectedPrintings' => $collectedPrintings,
                    'collectorView' => $collectorView,
                    'pageTitle' => ucfirst($className),
                ]);
            }

            // If the client doesn't support JavaScript, or isn't using Turbo, the form still works as usual.
            // Symfony UX Turbo is all about progressively enhancing your applications!
            return $this->redirectToRoute('app_collection_managecollectionbyset', ['className', $className], Response::HTTP_SEE_OTHER);
        }

        $cards = $this->cardFinder->findCardsByClass($className);
                
        return $this->render('collection/overview.html.twig', [
            'editionHelper' => $this->editionHelper,
            'foilingHelper' => $this->foilingHelper,
            'rarityHelper' => $this->rarityHelper,
            'artVariationsHelper' => $this->artVariationsHelper,
            'userCollectionManager' => $this->userCollectionManager,
            'cardPrintingsTree' => $cards,
            'userCollectedCards' => $collectedCards,
            'userCollectedPrintings' => $collectedPrintings,
            'form' => $form,
            'collectorView' => false,
            'pageTitle' => ucfirst($className),
        ]);
    }
    #[Route('/manage-promo-collection')]
    public function managePromoCollection(
        Request $request,
    ): Response {
        $form = $this->createForm(CardFilterFormType::class);
        $form->handleRequest($request);
        
        $collectedCards = new ArrayCollection();
        $collectedPrintings = new ArrayCollection();
        if (null !== $this->getUser()) {
            $collectedCards = $this->userCollectionManager->getCollectedCardsBy($this->getUser(), null, null, true);
            $collectedPrintings = $this->userCollectionManager->getCollectedPrintingsBy($this->getUser(), null, null, true);
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();
            $foiling = $formData['foiling'];
            $hideOwnedCards = $formData['hide'];
            $cardName = $formData['cardName'];

            // 🔥 The magic happens here! 🔥
            if (TurboBundle::STREAM_FORMAT === $request->getPreferredFormat()) {
                // If the request comes from Turbo, set the content type as text/vnd.turbo-stream.html and only send the HTML to update
                $request->setRequestFormat(TurboBundle::STREAM_FORMAT);
                $cards = $this->cardFinder->findPromos($hideOwnedCards, $foiling, $cardName);
                
                return $this->renderBlock('collection/promo_overview.html.twig', 'printing_table', [
                    'editionHelper' => $this->editionHelper,
                    'foilingHelper' => $this->foilingHelper,
                    'rarityHelper' => $this->rarityHelper,
                    'artVariationsHelper' => $this->artVariationsHelper,
                    'userCollectionManager' => $this->userCollectionManager,
                    'cardPrintingsTree' => $cards,
                    'userCollectedCards' => $collectedCards,
                    'userCollectedPrintings' => $collectedPrintings,
                ]);
            }

            // If the client doesn't support JavaScript, or isn't using Turbo, the form still works as usual.
            // Symfony UX Turbo is all about progressively enhancing your applications!
            return $this->redirectToRoute('app_collection_managepromocollection', [], Response::HTTP_SEE_OTHER);
        }

        $cards = $this->cardFinder->findPromos();
                
        return $this->render('collection/promo_overview.html.twig', [
            'editionHelper' => $this->editionHelper,
            'foilingHelper' => $this->foilingHelper,
            'rarityHelper' => $this->rarityHelper,
            'artVariationsHelper' => $this->artVariationsHelper,
            'userCollectionManager' => $this->userCollectionManager,
            'cardPrintingsTree' => $cards,
            'userCollectedCards' => $collectedCards,
            'userCollectedPrintings' => $collectedPrintings,
            'form' => $form,
        ]);
    }

    #[Route('/update-user-collection', methods: ['POST'])]
    #[IsGranted('ROLE_USER', message: 'Updating collection is for logged in users')]
    public function updateUserCollection(
        Request $request,
        EntityManagerInterface $entityManager,
    ): Response {
        $data = json_decode($request->getContent(), true);
        
        $amount = $data['amount'] ?? null;
        
        if ($amount !== null) {
            $cardPrintingId = $data['id'];
            $userCardPrintings = $entityManager->getRepository(UserCardPrintings::class)->find([
                'user' => $this->getUser(),
                'cardPrinting' => $cardPrintingId,
            ]);
            if (null === $userCardPrintings) {
                $userCardPrintings = new UserCardPrintings();
                $userCardPrintings->setUser($this->getUser());
                $userCardPrintings->setCardPrinting($cardPrintingId);
                $entityManager->persist($userCardPrintings);
            }
            if ('0' === $amount) {
                $entityManager->remove($userCardPrintings);
            } else {
                $userCardPrintings->setCollectionAmount($amount);
            }
            $entityManager->flush();
        }

        $this->addFlash('success', 'Quantity updated successfully!');

        return new JsonResponse(['success' => true]);
    }
}