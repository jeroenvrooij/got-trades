<?php
// src/Controller/HomeController.php
namespace App\Controller;

use App\Entity\Card;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/')]
    public function helloWorld(EntityManagerInterface $entityManager): Response
    {
        $cards = $entityManager->getRepository(Card::class)->findBy([], ['name' => 'ASC'], 50);

        if (!$cards) {
            throw $this->createNotFoundException(
                'No card found'
            );
        }

        return $this->render('home/helloworld.html.twig', ['cards' => $cards]);
    }
}