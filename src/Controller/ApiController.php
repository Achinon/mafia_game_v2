<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\RoleLoader;

#[Route('/api')]
class ApiController extends AbstractController
{
    #[Route('/', name: 'app_api', methods: ['GET'])]
    public function index(RoleLoader $loader): Response
    {
        $loader->load();

        return $this->json([
            'message' => sprintf('Documentation available at %s.', 'https://documenter.getpostman.com/view/14431758/2sA3QniEfo'),
        ]);
    }
}
