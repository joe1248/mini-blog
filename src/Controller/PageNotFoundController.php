<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class PageNotFoundController
{
    /**
    * @Route("/{anything}", requirements={"anything" = ".*"})
    */
    public function getPageNotFound()
    {
        return new JsonResponse(['error' => [
            'code' => 400,
            'message' => 'Page not found'
        ]]);
    }
}