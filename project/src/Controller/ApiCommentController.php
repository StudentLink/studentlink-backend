<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Post;
use App\Entity\School;
use App\Entity\User;
use App\Repository\CommentRepository;
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
class ApiCommentController extends AbstractController
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

    #[Route('/comments', name: '_comments', methods: ['GET', 'POST'])]
    public function posts(Request $request, CommentRepository $commentRepository, PostRepository $postRepository): Response
    {
        if ($request->getMethod() == 'GET') {
            return $this->json(
                $commentRepository->findAll(),
                200,
                [],
                ['groups' => 'comment']
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

            if (!isset($data['content']) || !isset($data['post'])) {
                return $this->json([
                    'message' => 'De la donnée est manquante. Consultez la documentation.',
                ], 400);
            }
            if ($data['content'] == null || $data['post'] == null) {
                return $this->json([
                    'message' => 'De la donnée est manquante. Consultez la documentation.',
                ], 400);
            }



            $comment = new Comment();
            $comment->setContent($data['content']);

            $post = $postRepository->findOneBy(['id' => $data['post']]);
            if ($post == null) {
                return $this->json([
                    'message' => 'Post introuvable.',
                ], 404);
            }
            $comment->setPost($post);

            $comment->setUser($user);
            $comment->setCreatedAt(new \DateTimeImmutable('now'));

            $this->entityManager->persist($comment);
            $this->entityManager->flush();

            return $this->json(
                $comment,
                200,
                [],
                ['groups' => 'comment']
            );
        }

        return $this->json([
            'message' => 'Methode non autorisée.',
        ], 405);
    }

    #[Route('/comments/{id}', name: '_comments_id', methods: ['GET', 'DELETE'])]
    public function comments_id(Request $request, CommentRepository $commentRepository, int $id): Response
    {
        if ($request->getMethod() == 'GET') {
            $comment = $commentRepository->findOneBy(['id' => $id]);
            if ($comment == null) {
                return $this->json([
                    'message' => 'Commentaire introuvable.',
                ], 404);
            }

            return $this->json(
                $comment,
                200,
                [],
                ['groups' => 'comment']
            );
        }

        if ($request->getMethod() == 'DELETE') {
            $comment = $commentRepository->findOneBy(['id' => $id]);
            if ($comment == null) {
                return $this->json([
                    'message' => 'Commentaire introuvable.',
                ], 404);
            }

            $decodedToken = $this->jwtManager->decode($this->tokenStorageInterface->getToken());
            $userRepository = $this->entityManager->getRepository(User::class);
            $user = $userRepository->findOneBy(['id' => $decodedToken['sub']]);

            if ($user == null) {
                return $this->json([
                    'message' => 'Utilisateur introuvable.',
                ], 404);
            }
            if (!in_array('ROLE_ADMIN', $user->getRoles())  && $user !== $comment->getUser()) {
                return $this->json([
                    'message' => "Vous n'avez pas les droits pour supprimer ce commentaire.",
                ], 403);
            }

            $this->entityManager->remove($comment);
            $this->entityManager->flush();
            return $this->json([
                'message' => 'Commentaire supprimé.',
            ]);
        }

        return $this->json([
            'message' => 'Methode non autorisée.',
        ], 405);
    }
}
