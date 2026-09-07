<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ErrorController extends AbstractController
{
    #[Route('/access-denied', name: 'app_access_denied', methods: ['GET'])]
    public function accessDenied(): Response
    {
        return new Response(
            $this->renderView('error/access_denied.html.twig'),
            Response::HTTP_FORBIDDEN
        );
    }
}