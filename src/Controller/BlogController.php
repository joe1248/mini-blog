<?php

namespace App\Controller;

use App\Business\ArticleService;
use App\Entity\Article;
use App\Repository\ArticleRepository;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
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

    /**
     * List all articles ordered by date DESC
     *
     * @param string $offset
     * @param string $limit
     * @param ArticleService $articleService
     *
     * @return JsonResponse
     *
     * @Route("/articles/{offset}/{limit}", requirements={"offset" = "\d+", "limit" = "\d+"})
     * @Method({"GET"})
     */
    public function getAll(
        string $offset = '0',
        string $limit = '3',
        ArticleService $articleService
    ): JsonResponse
    {
        /*if (!is_numeric($offset)) {
            return $controllerHelper->getBadRequestJsonResponse('invalid value specified for `offset`');
        }
        if (!is_numeric($limit)) {
            return $controllerHelper->getBadRequestJsonResponse('invalid value specified for `limit`');
        }
        if ($offset < 0) {
            return $controllerHelper->getBadRequestJsonResponse(
                'Invalid value specified for `offset`. Minimum required value is 0.'
            );
        }
        if ($limit > self::MAX_LIMIT_NUMBER_OF_FEATURED_ARTICLES) {
            return $controllerHelper->getBadRequestJsonResponse(
                'Invalid value specified for `limit`. ' .
                'Maximum allowed value is ' . self::MAX_LIMIT_NUMBER_OF_FEATURED_ARTICLES . '.'
            );
        }*/

        /** @var ArticleRepository $articleRepository */
        $articleRepository = $this->getDoctrine()->getRepository(Article::class);
        $articles = $articleService->getAll($articleRepository, $offset, $limit + 1);

        return new JsonResponse([
            'articles' => $articles,
            'next' => count($articles) == $limit + 1 ? '/articles/' . ++$offset . '/' . $limit : null,
        ]);
    }
}