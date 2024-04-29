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

    public function __construct(EntityManagerInterface $entityManager, UserPasswordHasherInterface $userPasswordHasher, JWTTokenManagerInterface $jwtManager, TokenStorageInterface $tokenStorageInterface)
    {
        $this->entityManager = $entityManager;
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
                $school,
                200,
                [],
                ['groups' => 'school']
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

    #[Route('/schools/{id}/posts', name: '_schools_id_posts', methods: ['GET'])]
    public function schools_id_posts(Request $request, SchoolRepository $schoolRepository, int $id): Response {
        $school = $schoolRepository->findOneBy(['id' => $id]);
        if ($school == null) {
            return $this->json([
                'message' => 'School not found.',
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
