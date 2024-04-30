<?php

namespace App\Controller;

use App\Entity\School;
use App\Entity\User;
use App\Repository\SchoolRepository;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

use App\Repository\UserRepository;

#[Route('/api', name: 'api')]
class ApiUserController extends AbstractController
{
    private UserPasswordHasherInterface $userPasswordHasher;
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordHasherInterface $userPasswordHasher, JWTTokenManagerInterface $jwtManager, TokenStorageInterface $tokenStorageInterface)
    {
        $this->userPasswordHasher = $userPasswordHasher;
        $this->entityManager = $entityManager;
    }

    #[Route('/users', name: '_users', methods: ['GET', 'POST'])]
    public function users(Request $request, UserRepository $userRepository, JWTTokenManagerInterface $JWTManager): Response
    {
        if ($request->getMethod() == 'GET') {
            return $this->json(
                $userRepository->findAll(),
                200,
                [],
                ['groups' => 'user']
            );
        }

        if ($request->getMethod() == 'POST') {
            $data = json_decode($request->getContent(), true);

            if (empty($data)) {
                return $this->json([
                    'message' => 'Aucune donnée envoyée.',
                ], 400);
            }

            if (!isset($data['email']) || !isset($data['name']) || !isset($data['role']) || !isset($data['username']) || !isset($data['password'])) {
                return $this->json([
                    'message' => 'De la donnée est manquante. Consultez la documentation.',
                ], 400);
            }

            if ($data['email'] == null || $data['name'] == null || $data['role'] == null || $data['username'] == null || $data['password'] == null) {
                return $this->json([
                    'message' => 'De la donnée est manquante. Consultez la documentation.',
                ], 400);
            }

            // Création d'un user
            $user = new User();
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                return $this->json([
                    'message' => "Adresse e-mail invalide.",
                ], 400);
            }
            if ($userRepository->findOneBy(['email' => $data['email']])) {
                return $this->json([
                    'message' => 'Adresse e-mail déjà utilisée.',
                ], 400);
            }
            $user->setEmail($data['email']);
            $user->setName($data['name']);
            if ($userRepository->findOneBy(['username' => $data['username']])) {
                return $this->json([
                    'message' => "Nom d'utilisateur déjà utilisé.",
                ], 400);
            }
            $user->setUsername($data['username']);
            if (!in_array($data['role'], ['ROLE_USER', 'ROLE_SCHOOL', 'ROLE_PARTNER', 'ROLE_ADMIN'])) {
                return $this->json([
                    'message' => 'Rôle invalide.',
                ], 400);
            }
            $user->setRoles([$data['role']]);
            if (strlen($data['password']) < 8 || !preg_match('^(?=.*[a-z])(?=.*[0-9])(?=.*[A-Z])(?=.*[\W_]).*$^', $data['password'])) {
                return $this->json([
                    'message' => "Le mot de passe n'est pas assez fort (doit contenir au moins une lettre majuscule, minuscule, un chiffre et un caractère spécial).",
                ], 400);
            }
            $user->setPassword($this->userPasswordHasher->hashPassword($user, $data['password']));
            $user->setLocations([]);
            $user->setCreatedAt(new \DateTimeImmutable('now'));

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            return $this->json(['token' => $JWTManager->create($user)]);
        }

        return $this->json([
            'message' => 'Methode non autorisée.',
        ], 405);
    }

    #[Route('/users/{idOrUsername}', name: '_users_id', methods: ['GET', 'PUT', 'DELETE'])]
    public function user_id(Request $request, UserRepository $userRepository, string $idOrUsername): Response
    {
        if (is_numeric($idOrUsername)) {
            $id = intval($idOrUsername);
            $user = $userRepository->findOneBy(['id' => $id]);
            if ($user == null) {
                return $this->json([
                    'message' => 'Utilisateur introuvable.',
                ], 404);
            }
        } else {
            $user = $userRepository->findOneBy(['username' => $idOrUsername]);
            if ($user == null) {
                return $this->json([
                    'message' => 'Utilisateur introuvable.',
                ], 404);
            }
        }

        if ($request->getMethod() == 'GET') {
            return $this->json(
                $user,
                200,
                [],
                ['groups' => 'user']
            );
        }

        if ($request->getMethod() == 'PUT') {

            $data = json_decode($request->getContent(), true);

            if (empty($data)) {
                return $this->json([
                    'message' => 'Aucune donnée envoyée.',
                ], 400);
            }

            if (isset($data['email']) && $data['email'] != null) {
                if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                    return $this->json([
                        'message' => "Adresse e-mail invalide.",
                    ], 400);
                }
                if ($userRepository->findOneBy(['email' => $data['email']])) {
                    return $this->json([
                        'message' => "Adresse e-mail déjà utilisée.",
                    ], 400);
                }
                $user->setEmail($data['email']);
            }

            if (isset($data['name']) && $data['name'] != null) {
                $user->setName($data['name']);
            }

            if (isset($data['username']) && $data['username'] != null) {
                if ($userRepository->findOneBy(['username' => $data['username']])) {
                    return $this->json([
                        'message' => "Nom d'utilisateur déjà utilisé.",
                    ], 400);
                }
                $user->setUsername($data['username']);
            }

            if (isset($data['role']) && $data['role'] != null) {
                if (!in_array($data['role'], ['ROLE_USER', 'ROLE_SCHOOL', 'ROLE_PARTNER', 'ROLE_ADMIN'])) {
                    return $this->json([
                        'message' => 'Rôle invalide.',
                    ], 400);
                }
                $user->setRoles([$data['role']]);
            }

            if (isset($data['password']) && $data['password'] != null) {
                if (strlen($data['password']) < 8 || !preg_match('^(?=.*[a-z])(?=.*[0-9])(?=.*[A-Z])(?=.*[\W_]).*$^', $data['password'])) {
                    return $this->json([
                        'message' => "Le mot de passe n'est pas assez fort (doit contenir au moins une lettre majuscule, minuscule, un chiffre et un caractère spécial).",
                    ], 400);
                }
                $user->setPassword($this->userPasswordHasher->hashPassword($user, $data['password']));
            }

            if (isset($data['picture']) && $data['picture'] != null) {
                $user->setPicture($data['picture']);
            }

            if (isset($data['locations']) && $data['locations'] != null) {
                $user->setLocations($data['locations']);
            }

            if (isset($data['school']) && $data['school'] != null) {
                $school = $this->entityManager->getRepository(School::class)->findOneBy(['id' => $data['school']]);
                if ($school == null) {
                    return $this->json([
                        'message' => 'École introuvable.',
                    ], 404);
                }
                $user->setSchool($school);
            }

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            return $this->json(
                $user,
                200,
                [],
                ['groups' => 'user']
            );
        }

        if ($request->getMethod() == 'DELETE') {
            $this->entityManager->remove($user);
            $this->entityManager->flush();
            return $this->json([
                'message' => 'Utilisateur supprimé.',
            ]);
        }

        return $this->json([
            'message' => 'Methode non autorisée.',
        ], 405);
    }

    #[Route('/users/{idOrUsername}/posts', name: '_users_id_posts', methods: ['GET'])]
    public function schools_id_posts(Request $request, UserRepository $userRepository, string $idOrUsername): Response {

        if (is_numeric($idOrUsername)) {
            $id = intval($idOrUsername);
            $user = $userRepository->findOneBy(['id' => $id]);
            if ($user == null) {
                return $this->json([
                    'message' => 'Utilisateur introuvable.',
                ], 404);
            }
        } else {
            $user = $userRepository->findOneBy(['username' => $idOrUsername]);
            if ($user == null) {
                return $this->json([
                    'message' => 'Utilisateur introuvable.',
                ], 404);
            }
        }

        return $this->json(
            $user->getPosts(),
            200,
            [],
            ['groups' => 'post']
        );
    }
}