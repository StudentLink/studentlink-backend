<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

use App\Repository\UserRepository;

class ApiController extends AbstractController
{
    private UserPasswordHasherInterface $userPasswordHasher;
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->userPasswordHasher = $userPasswordHasher;
        $this->entityManager = $entityManager;
    }

    #[Route('/api', name: 'api_home')]
    public function index(): Response
    {
        return $this->render('api/index.html.twig', [
            'controller_name' => 'ApiController',
        ]);
    }

    #[Route('/api/register', name: 'api_register')]
    public function userRegister(Request $request, UserRepository $userRepository, JWTTokenManagerInterface $JWTManager): Response
    {
//        if ($request->isMethod('GET')) {
//            return $this->json([
//                'message' => 'Bad method.',
//            ], 405);
//        }

        $data = array(
            "role" => $request->get('role'),
            "email" => $request->get('email'),
            "displayname" => $request->get('displayname'),
            "username" => $request->get('username'),
            "password" => $request->get('password')
        );

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
