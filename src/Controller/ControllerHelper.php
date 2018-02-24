<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;

class ControllerHelper
{
    // Move this to an exception listener
    public function getBadRequestJsonResponse(string $message)
    {
        return new JsonResponse(["error" => [
            'code' => 400,
            'message' => 'Bad Request: ' . $message
        ]]);
    }
}