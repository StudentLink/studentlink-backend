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
class ApiController extends AbstractController
{
    private UserPasswordHasherInterface $userPasswordHasher;
    private $entityManager;

    private JWTTokenManagerInterface $jwtManager;
    private TokenStorageInterface $tokenStorageInterface;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordHasherInterface $userPasswordHasher, JWTTokenManagerInterface $jwtManager, TokenStorageInterface $tokenStorageInterface)
    {
        $this->userPasswordHasher = $userPasswordHasher;
        $this->entityManager = $entityManager;

        // For using JWT Tokens in controllers
        $this->jwtManager = $jwtManager;
        $this->tokenStorageInterface = $tokenStorageInterface;
    }

    #[Route('/users', name: '_users', methods: ['GET', 'POST'])]
    public function users(Request $request, UserRepository $userRepository, JWTTokenManagerInterface $JWTManager): Response
    {
        if ($request->getMethod() == 'GET') {
            return $this->json(
                $userRepository->findAll(),
            );
        }

        if ($request->getMethod() == 'POST') {
            $data = json_decode($request->getContent(), true);

            if (empty($data)) {
                return $this->json([
                    'message' => 'No data provided.',
                ], 400);
            }

            if ($data['email'] == null || $data['displayname'] == null || $data['role'] == null || $data['username'] == null || $data['password'] == null) {
                return $this->json([
                    'message' => 'Some data is missing. Please refer to the documentation.',
                ], 400);
            }

            // Création d'un user
            $user = new User();
            if(!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                return $this->json([
                    'message' => 'Email is invalid.',
                ], 400);
            }
            if ($userRepository->findOneBy(['email' => $data['email']])) {
                return $this->json([
                    'message' => 'Email already used.',
                ], 400);
            }
            $user->setEmail($data['email']);
            $user->setName($data['displayname']);
            if ($userRepository->findOneBy(['username' => $data['username']])) {
                return $this->json([
                    'message' => 'Username already used.',
                ], 400);
            }
            $user->setUsername($data['username']);
            if (!in_array($data['role'], ['ROLE_USER', 'ROLE_SCHOOL', 'ROLE_PARTNER', 'ROLE_ADMIN'])) {
                return $this->json([
                    'message' => 'Role is invalid.',
                ], 400);
            }
            $user->setRoles([$data['role']]);
            if (strlen($data['password']) < 8 || !preg_match('^(?=.*[a-z])(?=.*[0-9])(?=.*[A-Z])(?=.*[\W_]).*$^',$data['password'])) {
                return $this->json([
                    'message' => 'Password does not respect the Security Policy.',
                ], 400);
            }
            $user->setPassword($this->userPasswordHasher->hashPassword($user, $data['password']));

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            return $this->json(['token' => $JWTManager->create($user)]);
        }

        return $this->json([
            'message' => 'Method not allowed.',
        ], 405);
    }

    #[Route('/users/me', name: '_user_me', methods: ['GET', 'PUT', 'DELETE'])]
    public function userMe(Request $request, UserRepository $userRepository): Response
    {
        $decodedJwtToken = $this->jwtManager->decode($this->tokenStorageInterface->getToken());
        $id = $decodedJwtToken['sub'];

        if ($request->getMethod() == 'GET') {
            $user = $userRepository->findOneBy(['id' => $id]);
            if ($user == null) {
                return $this->json([
                    'message' => 'User not found.',
                ], 404);
            }

            return $this->json(
                $user,
            );
        }

        if ($request->getMethod() == 'PUT') {
            return $this->json([
                "messsage" => "Not implemented yet."
            ]);
        }

        if ($request->getMethod() == 'DELETE') {
            $user = $userRepository->findOneBy(['id' => $id]);
            if ($user == null) {
                return $this->json([
                    'message' => 'User not found.',
                ], 404);
            }

            $this->entityManager->remove($user);
            $this->entityManager->flush();
            return $this->json([
                'message' => 'User deleted.',
            ]);
        }

        return $this->json([
            'message' => 'Method not allowed.',
        ], 405);
    }

    #[Route('/users/{id}', name: '_users_id', methods: ['GET', 'PUT', 'DELETE'])]
    public function user_id(Request $request, UserRepository $userRepository, int $id): Response
    {
        if ($request->getMethod() == 'GET') {
            $user = $userRepository->findOneBy(['id' => $id]);
            if ($user == null) {
                return $this->json([
                    'message' => 'User not found.',
                ], 404);
            }

            return $this->json(
                $user,
            );
        }

        if ($request->getMethod() == 'PUT') {
            return $this->json([
                "messsage" => "Not implemented yet."
            ]);
        }

        if ($request->getMethod() == 'DELETE') {
            $user = $userRepository->findOneBy(['id' => $id]);
            if ($user == null) {
                return $this->json([
                    'message' => 'User not found.',
                ], 404);
            }

            $this->entityManager->remove($user);
            $this->entityManager->flush();
            return $this->json([
                'message' => 'User deleted.',
            ]);
        }

        return $this->json([
            'message' => 'Method not allowed.',
        ], 405);
    }


    #[Route('/schools', name: '_schools', methods: ['GET', 'POST'])]
    public function schools(Request $request, SchoolRepository $schoolRepository): Response
    {
        if ($request->getMethod() == 'GET') {
            return $this->json(
                $schoolRepository->findAll(),
            );
        }

        if ($request->getMethod() == 'POST') {
            $data = json_decode($request->getContent(), true);

            if (empty($data)) {
                return $this->json([
                    'message' => 'No data provided.',
                ], 400);
            }

            if ($data['name'] == null) {
                return $this->json([
                    'message' => 'Some data is missing. Please refer to the documentation.',
                ], 400);
            }

            if ($schoolRepository->findOneBy(['name' => $data['name']])) {
                return $this->json([
                    'message' => 'School already exists.',
                ], 400);
            }

            $school = new School();
            $school->setName($data['name']);

            $this->entityManager->persist($school);
            $this->entityManager->flush();

            return $this->json(
                $school
            );
        }

        return $this->json([
            'message' => 'Method not allowed.',
        ], 405);
    }

    #[Route('/schools/{id}', name: '_schools_id', methods: ['GET', 'PUT', 'DELETE'])]
    public function schools_id(Request $request, SchoolRepository $schoolRepository, int $id): Response
    {
        if ($request->getMethod() == 'GET') {

            $school = $schoolRepository->findOneBy(['id' => $id]);
            if ($school == null) {
                return $this->json([
                    'message' => 'School not found.',
                ], 404);
            }

            return $this->json(
                $school
            );
        }

        if ($request->getMethod() == 'PUT') {

            $school = $schoolRepository->findOneBy(['id' => $id]);
            if ($school == null) {
                return $this->json([
                    'message' => 'School not found.',
                ], 404);
            }

            $data = json_decode($request->getContent(), true);

            if (empty($data)) {
                return $this->json([
                    'message' => 'No data provided.',
                ], 400);
            }

            if ($data['name'] == null) {
                return $this->json([
                    'message' => 'Some data is missing. Please refer to the documentation.',
                ], 400);
            }

            if ($schoolRepository->findOneBy(['name' => $data['name']])) {
                return $this->json([
                    'message' => 'School already exists.',
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
            $school = $schoolRepository->findOneBy(['id' => $id]);
            if ($school == null) {
                return $this->json([
                    'message' => 'School not found.',
                ], 404);
            }

            $this->entityManager->remove($school);
            $this->entityManager->flush();
            return $this->json([
                'message' => 'School deleted.',
            ]);
        }

        return $this->json([
            'message' => 'Method not allowed.',
        ], 405);
    }
}
