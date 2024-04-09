<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
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
        // Création d'un user "normal"
        $user = new User();
        $user->setEmail("user@studentlink.com");
        $user->setName("Luka GARCIA");
        $user->setUsername("lukagrc");
        $user->setRoles(["ROLE_USER"]);
        $user->setPassword($this->userPasswordHasher->hashPassword($user, "password"));
        $manager->persist($user);

        // Création d'un user "école"
        $userSchool = new User();
        $userSchool->setEmail("ynov@studentlink.com");
        $userSchool->setName("YNOV Campus");
        $userSchool->setUsername("ynov");
        $userSchool->setRoles(["ROLE_SCHOOL"]);
        $userSchool->setPassword($this->userPasswordHasher->hashPassword($userSchool, "password"));
        $manager->persist($userSchool);

        // Création d'un user "partenaire"
        $userPartner = new User();
        $userPartner->setEmail("bao@studentlink.com");
        $userPartner->setName("Bao Le Haillan");
        $userPartner->setUsername("baolehaillan");
        $userPartner->setRoles(["ROLE_PARTNER"]);
        $userPartner->setPassword($this->userPasswordHasher->hashPassword($userPartner, "password"));
        $manager->persist($userPartner);
        
        // Création d'un user admin
        $userAdmin = new User();
        $userAdmin->setEmail("admin@studentlink.com");
        $userAdmin->setName("Prénom NOM");
        $userAdmin->setUsername("useradmin");
        $userAdmin->setRoles(["ROLE_ADMIN"]);
        $userAdmin->setPassword($this->userPasswordHasher->hashPassword($userAdmin, "password"));
        $manager->persist($userAdmin);

        $manager->flush();
    }
}
