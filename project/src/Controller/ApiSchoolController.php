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
class ApiSchoolController extends AbstractController
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


    #[Route('/schools', name: '_schools', methods: ['GET', 'POST'])]
    public function schools(Request $request, SchoolRepository $schoolRepository): Response
    {
        if ($request->getMethod() == 'GET') {
            return $this->json(
                $schoolRepository->findAll(),
                200,
                [],
                ['groups' => 'school']
            );
        }

        if ($request->getMethod() == 'POST') {

            $decodedToken = $this->jwtManager->decode($this->tokenStorageInterface->getToken());
            $userRepository = $this->entityManager->getRepository(User::class);
            $user = $userRepository->findOneBy(['id' => $decodedToken['sub']]);

            if ($user == null) {
                return $this->json([
                    'message' => 'Utilisateur introuvable.',
                ], 404);
            }
            if (!in_array('ROLE_ADMIN', $user->getRoles())) {
                return $this->json([
                    'message' => "Vous n'avez pas les droits pour créer une école.",
                ], 403);
            }

            $data = json_decode($request->getContent(), true);

            if (empty($data)) {
                return $this->json([
                    'message' => 'Aucune donnée envoyée.',
                ], 400);
            }

            if ($data['name'] == null) {
                return $this->json([
                    'message' => 'De la donnée est manquante. Consultez la documentation.',
                ], 400);
            }

            if ($schoolRepository->findOneBy(['name' => $data['name']])) {
                return $this->json([
                    'message' => 'École déjà existante.',
                ], 400);
            }

            $school = new School();
            $school->setName($data['name']);

            $this->entityManager->persist($school);
            $this->entityManager->flush();

            return $this->json(
                $school,
                200,
                [],
                ['groups' => 'school']
            );
        }

        return $this->json([
            'message' => 'Methode non autorisée.',
        ], 405);
    }

    #[Route('/schools/{id}', name: '_schools_id', methods: ['GET', 'PUT', 'DELETE'])]
    public function schools_id(Request $request, SchoolRepository $schoolRepository, int $id): Response
    {
        if ($request->getMethod() == 'GET') {

            $school = $schoolRepository->findOneBy(['id' => $id]);
            if ($school == null) {
                return $this->json([
                    'message' => 'École introuvable.',
                ], 404);
            }

            return $this->json(
                $school,
                200,
                [],
                ['groups' => 'school']
            );
        }

        if ($request->getMethod() == 'PUT') {

            $school = $schoolRepository->findOneBy(['id' => $id]);
            if ($school == null) {
                return $this->json([
                    'message' => 'École introuvable.',
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
            if (!in_array('ROLE_ADMIN', $user->getRoles())  && !(in_array('ROLE_SCHOOL', $user->getRoles()) && $user->getSchool() === $school)) {
                return $this->json([
                    'message' => "Vous n'avez pas les droits pour modifier cette école.",
                ], 403);
            }

            $data = json_decode($request->getContent(), true);

            if (empty($data)) {
                return $this->json([
                    'message' => 'Aucune donnée envoyée.',
                ], 400);
            }

            if ($data['name'] == null) {
                return $this->json([
                    'message' => 'De la donnée est manquante. Consultez la documentation.',
                ], 400);
            }

            if ($schoolRepository->findOneBy(['name' => $data['name']])) {
                return $this->json([
                    'message' => 'École déjà existante.',
                ], 400);
            }

            $school->setName($data['name']);

            $this->entityManager->persist($school);
            $this->entityManager->flush();

            return $this->json(
                $school
            );
        }

        if ($request->getMethod() == 'DELETE') {
            $decodedToken = $this->jwtManager->decode($this->tokenStorageInterface->getToken());
            $userRepository = $this->entityManager->getRepository(User::class);
            $user = $userRepository->findOneBy(['id' => $decodedToken['sub']]);

            if ($user == null) {
                return $this->json([
                    'message' => 'Utilisateur introuvable.',
                ], 404);
            }
            if (!in_array('ROLE_ADMIN', $user->getRoles())) {
                return $this->json([
                    'message' => "Vous n'avez pas les droits pour supprimer une école.",
                ], 403);
            }

            $school = $schoolRepository->findOneBy(['id' => $id]);
            if ($school == null) {
                return $this->json([
                    'message' => 'École introuvable.',
                ], 404);
            }

            $this->entityManager->remove($school);
            $this->entityManager->flush();
            return $this->json([
                'message' => 'École supprimée.',
            ]);
        }

        return $this->json([
            'message' => 'Methode non autorisée.',
        ], 405);
    }

    #[Route('/schools/{id}/posts', name: '_schools_id_posts', methods: ['GET'])]
    public function schools_id_posts(Request $request, SchoolRepository $schoolRepository, int $id): Response {
        $school = $schoolRepository->findOneBy(['id' => $id]);
        if ($school == null) {
            return $this->json([
                'message' => 'École introuvable.',
            ], 404);
        }

        return $this->json(
            $school->getPosts(),
            200,
            [],
            ['groups' => 'post']
        );
    }
}
