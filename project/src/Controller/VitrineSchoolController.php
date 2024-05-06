<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/ecoles', name: 'vitrine_schools')]
class VitrineSchoolController extends AbstractController
{
    #[Route('', name: '_home')]
    public function schools_home(): Response
    {
        return $this->render('schools/home/index.html.twig');
    }
}
