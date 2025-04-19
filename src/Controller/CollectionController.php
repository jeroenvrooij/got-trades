<?php
// src/Controller/PrintingController.php
namespace App\Controller;

use App\Entity\Set;
use App\Entity\UserCardPrintings;
use App\Form\CardFilterFormType;
use App\Repository\CardPrintingRepository;
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
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
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
        Set $set,
        ParameterBagInterface $params,
    ): Response {
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

                $cardsPaginator = $this->cardFinder->findPaginatedCardsBySet($set, 0, $hideOwnedCards, $collectorView, $foiling, $cardName);
                $cards = $this->cardFinder->buildPrintingTree($cardsPaginator);

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
                    'pageType' => $params->get('collectionPageType_SET'),
                    'cardsPaginator' => $cardsPaginator,
                    'nextOffset' => CardPrintingRepository::CARDS_PER_PAGE,
                ]);
            }

            // If the client doesn't support JavaScript, or isn't using Turbo, the form still works as usual.
            // Symfony UX Turbo is all about progressively enhancing your applications!
            return $this->redirectToRoute('app_collection_managecollectionbyset', ['setId', $set->getId()], Response::HTTP_SEE_OTHER);
        }

        $cardsPaginator = $this->cardFinder->findPaginatedCardsBySet($set);
        $cards = $this->cardFinder->buildPrintingTree($cardsPaginator);

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
            'pageTitle' => $set->getName(),
            'pageType' => $params->get('collectionPageType_SET'),
            'cardsPaginator' => $cardsPaginator,
            'nextOffset' => CardPrintingRepository::CARDS_PER_PAGE,
        ]);
    }

    #[Route('/manage-collection-by-class/{className}')]
    public function manageCollectionByClass(
        Request $request,
        ParameterBagInterface $params,
        ?string $className,
    ): Response {
        if (null === $className || false === $this->isClassNameValid($className)) {
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

                $cardsPaginator = $this->cardFinder->findPaginatedCardsByClass($className, 0, $hideOwnedCards, $collectorView, $foiling, $cardName);
                $cards = $this->cardFinder->buildPrintingTree($cardsPaginator);

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
                    'pageType' => $params->get('collectionPageType_CLASS'),
                    'cardsPaginator' => $cardsPaginator,
                    'nextOffset' => CardPrintingRepository::CARDS_PER_PAGE,
                ]);
            }

            // If the client doesn't support JavaScript, or isn't using Turbo, the form still works as usual.
            // Symfony UX Turbo is all about progressively enhancing your applications!
            return $this->redirectToRoute('app_collection_managecollectionbyset', ['className', $className], Response::HTTP_SEE_OTHER);
        }

        $cardsPaginator = $this->cardFinder->findPaginatedCardsByClass($className);
        $cards = $this->cardFinder->buildPrintingTree($cardsPaginator);

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
            'pageType' => $params->get('collectionPageType_CLASS'),
            'cardsPaginator' => $cardsPaginator,
            'nextOffset' => CardPrintingRepository::CARDS_PER_PAGE,
        ]);
    }

    #[Route('/fetch-collection-view-rows-by-offset')]
    public function fetchCollectionViewRowsByOffset(
        Request $request,
        ParameterBagInterface $params,
        EntityManagerInterface $entityManager,
    ) {
        $className = $request->query->get('className');
        $setId = $request->query->get('setId');
        if (null === $className && null === $setId) {
            throw $this->createNotFoundException("Provide either a class name or a set id.");
        }

        // Create the form and handle the GET request parameters
        $form = $this->createForm(CardFilterFormType::class);

        $formData = $request->query->all();
        // Since this is a GET request, we manually set the form data from the request
        $form->submit($formData['card_filter_form'], false);  // false = don't clear missing fields

        // // Retrieve the filter form data
        $cardName = $form->get('cardName')->getData();
        $foiling = $form->get('foiling')->getData();
        $hideOwnedCards = $form->get('hide')->getData();
        $collectorView = $form->get('collectorView')->getData();
        $offset = $request->query->getInt('offset', 0);
        $renderedSets = $request->query->get('renderedSet');

        if (null !== $className) {
            if (!$this->isClassNameValid($className)) {
                throw $this->createNotFoundException("Invalid class: $className");
            }
            $cardsPaginator = $this->cardFinder->findPaginatedCardsByClass($className, $offset, $hideOwnedCards, $collectorView, $foiling, $cardName);
            $pageType = $params->get('collectionPageType_CLASS');
        }

        if (null !== $setId) {
            $set = $entityManager->getRepository(Set::class)->find($setId);

            if (!$set) {
                throw $this->createNotFoundException("Invalid set: $setId");
            }
            $cardsPaginator = $this->cardFinder->findPaginatedCardsBySet($set, $offset, $hideOwnedCards, $collectorView, $foiling, $cardName);
            $pageType = $params->get('collectionPageType_SET');
        }
        $cards = $this->cardFinder->buildPrintingTree($cardsPaginator);

        $collectedCards = new ArrayCollection();
        $collectedPrintings = new ArrayCollection();
        if (null !== $this->getUser()) {
            $collectedCards = $this->userCollectionManager->getCollectedCardsBy($this->getUser(), null, null, true);
            $collectedPrintings = $this->userCollectionManager->getCollectedPrintingsBy($this->getUser(), null, null, true);
        }

        $request->setRequestFormat(TurboBundle::STREAM_FORMAT);

        return $this->renderBlock('collection/overview.html.twig', 'printing_card_rows', [
            'editionHelper' => $this->editionHelper,
            'foilingHelper' => $this->foilingHelper,
            'rarityHelper' => $this->rarityHelper,
            'artVariationsHelper' => $this->artVariationsHelper,
            'userCollectionManager' => $this->userCollectionManager,
            'cardPrintingsTree' => $cards,
            'userCollectedCards' => $collectedCards,
            'userCollectedPrintings' => $collectedPrintings,
            'collectorView' => $collectorView,
            'cardsPaginator' => $cardsPaginator,
            'nextOffset' => min(count($cardsPaginator), $offset + CardPrintingRepository::CARDS_PER_PAGE),
            'renderedSets' => $renderedSets,
            'pageType' => $pageType,
        ]);
    }

    /**
     * Only FaB classes are allowed
     */
    private function isClassNameValid(string $className)
    {
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

        return in_array($className, $allowedClasses, true);
    }

    #[Route('/fetch-promo-rows-by-offset')]
    public function fetchPromoRowsByOffset(
        Request $request,
        ParameterBagInterface $params,
    ) {
        // Create the form and handle the GET request parameters
        $form = $this->createForm(CardFilterFormType::class);

        $formData = $request->query->all();
        // Since this is a GET request, we manually set the form data from the request
        $form->submit($formData['card_filter_form'], false);  // false = don't clear missing fields

        // // Retrieve the filter form data
        $cardName = $form->get('cardName')->getData();
        $foiling = $form->get('foiling')->getData();
        $hideOwnedCards = $form->get('hide')->getData();
        $offset = $request->query->getInt('offset', 0);
        $renderedSets = $request->query->get('renderedSet');

        $cardsPaginator = $this->cardFinder->findPaginatedPromos($offset, $hideOwnedCards, $foiling, $cardName);
        $cards = $this->cardFinder->buildPrintingTree($cardsPaginator);

        $collectedCards = new ArrayCollection();
        $collectedPrintings = new ArrayCollection();
        if (null !== $this->getUser()) {
            $collectedCards = $this->userCollectionManager->getCollectedCardsBy($this->getUser(), null, null, true);
            $collectedPrintings = $this->userCollectionManager->getCollectedPrintingsBy($this->getUser(), null, null, true);
        }

        $request->setRequestFormat(TurboBundle::STREAM_FORMAT);

        return $this->renderBlock('collection/promo_overview.html.twig', 'printing_card_rows', [
            'editionHelper' => $this->editionHelper,
            'foilingHelper' => $this->foilingHelper,
            'rarityHelper' => $this->rarityHelper,
            'artVariationsHelper' => $this->artVariationsHelper,
            'userCollectionManager' => $this->userCollectionManager,
            'cardPrintingsTree' => $cards,
            'userCollectedCards' => $collectedCards,
            'userCollectedPrintings' => $collectedPrintings,
            'cardsPaginator' => $cardsPaginator,
            'nextOffset' => min(count($cardsPaginator), $offset + CardPrintingRepository::CARDS_PER_PAGE),
            'renderedSets' => $renderedSets,
            'pageType' => $params->get('collectionPageType_PROMO'),
        ]);
    }

    #[Route('/manage-promo-collection')]
    public function managePromoCollection(
        Request $request,
        ParameterBagInterface $params,
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

                $cardsPaginator = $this->cardFinder->findPaginatedPromos(0, $hideOwnedCards, $foiling, $cardName);
                $cards = $this->cardFinder->buildPrintingTree($cardsPaginator);

                return $this->renderBlock('collection/promo_overview.html.twig', 'printing_table', [
                    'editionHelper' => $this->editionHelper,
                    'foilingHelper' => $this->foilingHelper,
                    'rarityHelper' => $this->rarityHelper,
                    'artVariationsHelper' => $this->artVariationsHelper,
                    'userCollectionManager' => $this->userCollectionManager,
                    'cardPrintingsTree' => $cards,
                    'userCollectedCards' => $collectedCards,
                    'userCollectedPrintings' => $collectedPrintings,
                    'pageType' => $params->get('collectionPageType_PROMO'),
                    'cardsPaginator' => $cardsPaginator,
                    'nextOffset' => CardPrintingRepository::CARDS_PER_PAGE,
                ]);
            }

            // If the client doesn't support JavaScript, or isn't using Turbo, the form still works as usual.
            // Symfony UX Turbo is all about progressively enhancing your applications!
            return $this->redirectToRoute('app_collection_managepromocollection', [], Response::HTTP_SEE_OTHER);
        }

        $cardsPaginator = $this->cardFinder->findPaginatedPromos();
        $cards = $this->cardFinder->buildPrintingTree($cardsPaginator);

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
            'pageType' => $params->get('collectionPageType_PROMO'),
            'cardsPaginator' => $cardsPaginator,
            'nextOffset' => CardPrintingRepository::CARDS_PER_PAGE,
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