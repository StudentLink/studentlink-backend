<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'vitrine_users_home')]
    public function users_home(UserRepository $userRepository): Response
    {

        $userCount = $userRepository->count([]);

        return $this->render('users/home/index.html.twig', [
            'userCount' => $userCount,
        ]);
    }

    #[Route('/decouvrir', name: 'vitrine_users_discover')]
    public function users_about(): Response
    {
        return $this->render('users/discover/index.html.twig', [
            'pageH1' => "Découvrir"
        ]);
    }

    #[Route('/contact', name: 'vitrine_users_contact')]
    public function users_contact(): Response
    {
        return $this->render('home/contact.html.twig');
    }
}
