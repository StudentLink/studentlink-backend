<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use App\Repository\UserRepository;

class ApiController extends AbstractController
{
    #[Route('/api', name: 'api_home')]
    public function index(): Response
    {
        return $this->render('api/index.html.twig', [
            'controller_name' => 'ApiController',
        ]);
    }

    #[Route('/api/users', name: 'api_users')]
    public function users(UserRepository $userRepository): Response
    {

        return $this->json(
            $userRepository->findAll(),
        );
    }

    #[Route('/api/user/{id}', name: 'api_user')]
    public function user(UserRepository $userRepository, int $id): Response
    {

        return $this->json(
            $userRepository->findOneBy(['id' => $id]),
        );
    }
}
