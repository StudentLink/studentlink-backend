<?php

namespace App\DataFixtures;

use App\Entity\School;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    private $userPasswordHasher;
    
    public function __construct(UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->userPasswordHasher = $userPasswordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $ynovBordeaux = new School();
        $ynovBordeaux->setName('Bordeaux YNOV Campus');
        $manager->persist($ynovBordeaux);

        // Création d'un user "normal"
        $user = new User();
        $user->setEmail("user@studentlink.com");
        $user->setName("Luka GARCIA");
        $user->setUsername("lukagrc");
        $user->setRoles(["ROLE_USER"]);
        $user->setPassword($this->userPasswordHasher->hashPassword($user, "password"));
        $user->setLocations(["Bordeaux", "Paris"]);
        $user->setSchool($ynovBordeaux);
        $user->setCreatedAt(new \DateTimeImmutable('now'));
        $manager->persist($user);

        // Création d'un user "école"
        $userSchool = new User();
        $userSchool->setEmail("ynov@studentlink.com");
        $userSchool->setName("YNOV Campus");
        $userSchool->setUsername("ynov");
        $userSchool->setRoles(["ROLE_SCHOOL"]);
        $userSchool->setPassword($this->userPasswordHasher->hashPassword($userSchool, "password"));
        $userSchool->setLocations(["Bordeaux"]);
        $userSchool->setSchool($ynovBordeaux);
        $userSchool->setCreatedAt(new \DateTimeImmutable('now'));
        $manager->persist($userSchool);

        // Création d'un user "partenaire"
        $userPartner = new User();
        $userPartner->setEmail("bao@studentlink.com");
        $userPartner->setName("Bao Le Haillan");
        $userPartner->setUsername("baolehaillan");
        $userPartner->setRoles(["ROLE_PARTNER"]);
        $userPartner->setPassword($this->userPasswordHasher->hashPassword($userPartner, "password"));
        $userPartner->setLocations(["Le Haillan"]);
        $userPartner->setCreatedAt(new \DateTimeImmutable('now'));
        $manager->persist($userPartner);
        
        // Création d'un user admin
        $userAdmin = new User();
        $userAdmin->setEmail("admin@studentlink.com");
        $userAdmin->setName("Prénom NOM");
        $userAdmin->setUsername("useradmin");
        $userAdmin->setRoles(["ROLE_ADMIN"]);
        $userAdmin->setPassword($this->userPasswordHasher->hashPassword($userAdmin, "password"));
        $userAdmin->setLocations(["Bordeaux"]);
        $userAdmin->setCreatedAt(new \DateTimeImmutable('now'));
        $manager->persist($userAdmin);

        $manager->flush();
    }
}
