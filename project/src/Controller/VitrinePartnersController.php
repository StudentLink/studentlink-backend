<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/entreprises', name: 'vitrine_partners')]

class VitrinePartnersController extends AbstractController
{
    #[Route('', name: '_home')]
    public function partners_home(): Response
    {
        return $this->render('partners/home/index.html.twig');
    }
}
