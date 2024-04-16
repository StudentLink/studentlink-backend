<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'vitrine_home')]
    public function index(UserRepository $userRepository): Response
    {

        $userCount = $userRepository->count([]);

        return $this->render('home/index.html.twig', [
            'userCount' => $userCount,
        ]);
    }
}
