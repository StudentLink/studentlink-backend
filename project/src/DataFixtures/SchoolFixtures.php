<?php

namespace App\DataFixtures;

use App\Entity\School;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class SchoolFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {

        $ynovLyon = new School();
        $ynovLyon->setName('Lyon YNOV Campus');
        $manager->persist($ynovLyon);

        $ynovParis = new School();
        $ynovParis->setName('Paris YNOV Campus');
        $manager->persist($ynovParis);

        $ynovToulouse = new School();
        $ynovToulouse->setName('Toulouse YNOV Campus');
        $manager->persist($ynovToulouse);

        $ynovNantes = new School();
        $ynovNantes->setName('Nantes YNOV Campus');
        $manager->persist($ynovNantes);

        $ynovAix = new School();
        $ynovAix->setName('Aix YNOV Campus');
        $manager->persist($ynovAix);

        $ynovLille = new School();
        $ynovLille->setName('Lille YNOV Campus');
        $manager->persist($ynovLille);

        $ynovRennes = new School();
        $ynovRennes->setName('Rennes YNOV Campus');
        $manager->persist($ynovRennes);

        $ynovSophia = new School();
        $ynovSophia->setName('Sophia YNOV Campus');
        $manager->persist($ynovSophia);

        $epitechBordeaux = new School();
        $epitechBordeaux->setName('Epitech Bordeaux');
        $manager->persist($epitechBordeaux);

        $epitechLyon = new School();
        $epitechLyon->setName('Epitech Lyon');
        $manager->persist($epitechLyon);

        $quaranteDeuxParis = new School();
        $quaranteDeuxParis->setName('42 Paris');
        $manager->persist($quaranteDeuxParis);

        $quatanteDeuxAngouleme = new School();
        $quatanteDeuxAngouleme->setName('42 Angoulème');
        $manager->persist($quatanteDeuxAngouleme);

        $manager->flush();
    }
}
