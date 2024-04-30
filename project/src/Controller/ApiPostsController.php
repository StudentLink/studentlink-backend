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
class ApiPostsController extends AbstractController
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

    #[Route('/posts', name: '_posts', methods: ['GET', 'POST'])]
    public function posts(Request $request, PostRepository $postRepository): Response
    {
        if ($request->getMethod() == 'GET') {
            return $this->json(
                $postRepository->findAll(),
                200,
                [],
                ['groups' => 'post']
            );
        }

        if ($request->getMethod() == 'POST') {
            $data = json_decode($request->getContent(), true);
            $decodedJwtToken = $this->jwtManager->decode($this->tokenStorageInterface->getToken());

            $userRepository = $this->entityManager->getRepository(User::class);
            $user = $userRepository->findOneBy(['id' => $decodedJwtToken['sub']]);
            if ($user == null) {
                return $this->json([
                    'message' => 'Utilisateur introuvable.',
                ], 404);
            }


            if (empty($data)) {
                return $this->json([
                    'message' => 'Aucune donnée envoyée.',
                ], 400);
            }

            if (!isset($data['content']) || (!isset($data['school']) && !isset($data['location']))) {
                return $this->json([
                    'message' => 'De la donnée est manquante. Consultez la documentation.',
                ], 400);
            }

            $post = new Post();
            $post->setContent($data['content']);
            if (isset($data['school']) && $data['school'] != null) {
                $schoolRepository = $this->entityManager->getRepository(School::class);
                $school = $schoolRepository->findOneBy(['id' => $data['school']]);
                if ($school == null) {
                    return $this->json([
                        'message' => 'École introuvable.',
                    ], 404);
                }
                $post->setSchool($school);

                if ($user->getSchool() !== $school) {
                    return $this->json([
                        'message' => "L'école concernée par le post ne correspond pas à celle de l'utilisateur.",
                    ], 400);
                }
            }
            if (isset($data['location']) && $data['location'] != null) {
                $post->setLocation($data['location']);
            }
            $post->setUser($user);
            $post->setCreatedAt(new \DateTimeImmutable('now'));

            $this->entityManager->persist($post);
            $this->entityManager->flush();

            return $this->json(
                $post,
                200,
                [],
                ['groups' => 'post']
            );
        }

        return $this->json([
            'message' => 'Methode non autorisée.',
        ], 405);
    }

    #[Route('/posts/{id}', name: '_posts_id', methods: ['GET', 'DELETE'])]
    public function posts_id(Request $request, PostRepository $postRepository, int $id): Response
    {
        if ($request->getMethod() == 'GET') {

            $post = $postRepository->findOneBy(['id' => $id]);
            if ($post == null) {
                return $this->json([
                    'message' => 'Post introuvable.',
                ], 404);
            }

            return $this->json(
                $post,
                200,
                [],
                ['groups' => 'post']
            );
        }

        if ($request->getMethod() == 'DELETE') {
            $post = $postRepository->findOneBy(['id' => $id]);
            if ($post == null) {
                return $this->json([
                    'message' => 'Post introuvable.',
                ], 404);
            }

            $this->entityManager->remove($post);
            $this->entityManager->flush();
            return $this->json([
                'message' => 'Post supprimé.',
            ]);
        }

        return $this->json([
            'message' => 'Methode non autorisée.',
        ], 405);
    }

    #[Route('/posts/{id}/comments', name: '_posts_id_posts', methods: ['GET'])]
    public function posts_id_posts(Request $request, PostRepository $postRepository, int $id): Response {
        $post = $postRepository->findOneBy(['id' => $id]);
        if ($post == null) {
            return $this->json([
                'message' => 'Post introuvable.',
            ], 404);
        }

        return $this->json(
            $post->getComments(),
            200,
            [],
            ['groups' => 'comments']
        );
    }
}
