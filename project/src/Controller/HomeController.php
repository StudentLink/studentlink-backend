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

    #[Route('/about', name: 'vitrine_users_about')]
    public function users_about(): Response
    {
        return $this->render('home/about.html.twig');
    }

    #[Route('/contact', name: 'vitrine_users_contact')]
    public function users_contact(): Response
    {
        return $this->render('home/contact.html.twig');
    }



    #[Route('/ecoles', name: 'vitrine_schools_home')]
    public function schools_home(): Response
    {
        return $this->render('schools/home/index.html.twig');
    }


    #[Route('/entreprises', name: 'vitrine_partners_home')]
    public function partners_home(): Response
    {
        return $this->render('partners/home/index.html.twig');
    }
}
