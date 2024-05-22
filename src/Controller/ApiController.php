<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


#[Route('/api')]
class ApiController extends AbstractController
{
    #[Route('/api', name: 'app_api')]
    public function index(): Response
    {
        return $this->json([
            'message' => sprintf('Documentation available at %s.', 'https://documenter.getpostman.com/view/14431758/2sA3QniEfo'),
        ]);
    }
}
