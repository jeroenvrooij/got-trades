<?php
// src/Controller/PrintingController.php
namespace App\Controller;

use App\Entity\CardPrinting;
use App\Entity\Set;
use App\Entity\UserCardPrintings;
use App\Form\CardFilterFormType;
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
    #[IsGranted('ROLE_USER', message: 'Viewing sets is only for logged in users')]
    public function manageCollectionBySet(
        Request $request,
        EntityManagerInterface $entityManager,
        #[MapEntity(mapping: ['setId' => 'id'], message: 'Set could not be found')]
        Set $set
    ): Response {
        $hideOwnedCards = false;
        $collectorView = false;
        $cardName = '';
        $foiling = '';
        $form = $this->createForm(CardFilterFormType::class);

        $form->handleRequest($request);

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
                dump($hideOwnedCards, $collectorView);
                $cards = $this->cardFinder->findCardsBySet($set, $hideOwnedCards, $collectorView, $foiling, $cardName);

                return $this->renderBlock('collection/overview.html.twig', 'printing_table', [
                    'editionHelper' => $this->editionHelper,
                    'foilingHelper' => $this->foilingHelper,
                    'rarityHelper' => $this->rarityHelper,
                    'artVariationsHelper' => $this->artVariationsHelper,
                    'userCollectionManager' => $this->userCollectionManager,
                    'cardPrintingsTree' => $cards,
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
            'form' => $form,
            'collectorView' => $collectorView,
            'pageTitle' => $set->getName(),
        ]);
    }


    #[Route('/manage-collection-by-class/{className}')]
    #[IsGranted('ROLE_USER', message: 'Viewing classes is only for logged in users')]
    public function manageCollectionByClass(
        Request $request,
        EntityManagerInterface $entityManager,
        ?string $className
    ): Response {
        $form = $this->createForm(CardFilterFormType::class);

        $form->handleRequest($request);

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

                // return $this->renderBlock('printing/printings_by_class.html.twig', 'printing_table', [
                //     'editionHelper' => $this->editionHelper,
                //     'foilingHelper' => $this->foilingHelper,
                //     'rarityHelper' => $this->rarityHelper,
                //     'artVariationsHelper' => $this->artVariationsHelper,
                //     'userCollectionManager' => $this->userCollectionManager,
                //     'class' => $className, 
                //     'cards' => $cards,
                // ]);
                return $this->renderBlock('collection/overview.html.twig', 'printing_table', [
                    'editionHelper' => $this->editionHelper,
                    'foilingHelper' => $this->foilingHelper,
                    'rarityHelper' => $this->rarityHelper,
                    'artVariationsHelper' => $this->artVariationsHelper,
                    'userCollectionManager' => $this->userCollectionManager,
                    'cardPrintingsTree' => $cards,
                    'collectorView' => $collectorView,
                    'pageTitle' => $className,
                ]);
            }

            // If the client doesn't support JavaScript, or isn't using Turbo, the form still works as usual.
            // Symfony UX Turbo is all about progressively enhancing your applications!
            return $this->redirectToRoute('app_collection_managecollectionbyset', ['className', $className], Response::HTTP_SEE_OTHER);
        }

        $cards = $this->cardFinder->findCardsByClass($className);

        // return $this->render('printing/printings_by_class.html.twig', [
        //     'editionHelper' => $this->editionHelper,
        //     'foilingHelper' => $this->foilingHelper,
        //     'rarityHelper' => $this->rarityHelper,
        //     'artVariationsHelper' => $this->artVariationsHelper,
        //     'userCollectionManager' => $this->userCollectionManager,
        //     'class' => $className, 
        //     'cards' => $cards,
        //     'form' => $form,
        // ]);
       return $this->render('collection/overview.html.twig', [
            'editionHelper' => $this->editionHelper,
            'foilingHelper' => $this->foilingHelper,
            'rarityHelper' => $this->rarityHelper,
            'artVariationsHelper' => $this->artVariationsHelper,
            'userCollectionManager' => $this->userCollectionManager,
            'cardPrintingsTree' => $cards,
            'form' => $form,
            'collectorView' => false,
            'pageTitle' => $className,
        ]);
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
                    'cardPrinting' => $data['id'],
                ]);
                if (null === $userCardPrintings) {
                    $userCardPrintings = new UserCardPrintings();
                    $userCardPrintings->setUser($this->getUser());
                    $cardPrinting = $entityManager->getRepository(CardPrinting::class)->findOneBy(['uniqueId' => $data['id']]);
                    $userCardPrintings->setCardPrinting($cardPrinting);
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