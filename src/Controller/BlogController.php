<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

class BlogController extends Controller
{
    /**
    * @Route("/")
    */
    public function hello()
    {
        return new JsonResponse(['example' => 'Hello World']);
    }
}