<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\School;
use App\Entity\User;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api', name: 'api')]
class ApiFeedsController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    private JWTTokenManagerInterface $jwtManager;
    private TokenStorageInterface $tokenStorageInterface;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordHasherInterface $userPasswordHasher, JWTTokenManagerInterface $jwtManager, TokenStorageInterface $tokenStorageInterface)
    {
        $this->entityManager = $entityManager;

        // For using JWT Tokens in controllers
        $this->jwtManager = $jwtManager;
        $this->tokenStorageInterface = $tokenStorageInterface;
    }

    #[Route('/feed', name: '_feed', methods: ['GET'])]
    public function feed(Request $request, PostRepository $postRepository): Response
    {
        $decodedToken = $this->jwtManager->decode($this->tokenStorageInterface->getToken());
        $userRepository = $this->entityManager->getRepository(User::class);
        $user = $userRepository->findOneBy(['id' => $decodedToken['sub']]);
        if ($user == null) {
            return $this->json([
                'message' => 'Utilisateur introuvable.',
            ], 404);
        }

        $school = $user->getSchool();
        if ($school == null) {
            return $this->json([
                'message' => 'École introuvable.',
            ], 404);
        }

        $posts = $postRepository->findBy(['school' => $school], ['createdAt' => 'DESC']);

        foreach ($user->getLocations() as $location) {
            $postsCurrentLocation = $postRepository->findBy(['location' => $location], ['createdAt' => 'DESC']);
            $posts = array_merge($posts, $postsCurrentLocation);
        }

        usort($posts, fn($a, $b) => $b->getCreatedAt()->getTimestamp() - $a->getCreatedAt()->getTimestamp());

        return $this->json(
            $posts,
            200,
            [],
            ['groups' => 'post']
        );
    }

    #[Route('/feed/school', name: '_feed_school', methods: ['GET'])]
    public function feed_school(Request $request, PostRepository $postRepository): Response
    {
        $decodedToken = $this->jwtManager->decode($this->tokenStorageInterface->getToken());
        $userRepository = $this->entityManager->getRepository(User::class);
        $user = $userRepository->findOneBy(['id' => $decodedToken['sub']]);
        if ($user == null) {
            return $this->json([
                'message' => 'Utilisateur introuvable.',
            ], 404);
        }

        $school = $user->getSchool();
        if ($school == null) {
            return $this->json([
                'message' => 'École introuvable.',
            ], 404);
        }

        $posts = $postRepository->findBy(['school' => $school], ['createdAt' => 'DESC']);

        return $this->json(
            $posts,
            200,
            [],
            ['groups' => 'post']
        );
    }

    #[Route('/feed/locations', name: '_feed_locations', methods: ['GET'])]
    public function feed_locations(Request $request, PostRepository $postRepository): Response
    {
        $decodedToken = $this->jwtManager->decode($this->tokenStorageInterface->getToken());
        $userRepository = $this->entityManager->getRepository(User::class);
        $user = $userRepository->findOneBy(['id' => $decodedToken['sub']]);
        if ($user == null) {
            return $this->json([
                'message' => 'Utilisateur introuvable.',
            ], 404);
        }

        $posts = [];

        foreach ($user->getLocations() as $location) {
            $postsCurrentLocation = $postRepository->findBy(['location' => $location], ['createdAt' => 'DESC']);
            $posts = array_merge($posts, $postsCurrentLocation);
        }

        usort($posts, fn($a, $b) => $b->getCreatedAt()->getTimestamp() - $a->getCreatedAt()->getTimestamp());

        return $this->json(
            $posts,
            200,
            [],
            ['groups' => 'post']
        );
    }
}
