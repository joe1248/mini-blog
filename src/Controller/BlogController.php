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
    const MAX_LIMIT_NUMBER_OF_FEATURED_ARTICLES = 50;

    /**
     * List all articles ordered by date DESC
     *
     * @param string $page
     * @param string $limit
     * @param ArticleService $articleService
     * @param ControllerHelper $controllerHelper
     *
     * @return JsonResponse
     *
     * @Route("/")
     * @Route("/articles/{page}/{limit}", requirements={"page" = "\d+", "limit" = "\d+"})
     * @Method({"GET"})
     */
    public function getAll(
        string $page = '1',
        string $limit = '3',
        ArticleService $articleService,
        ControllerHelper $controllerHelper
    ): JsonResponse
    {
        $limit = (int)$limit;
        $page = (int)$page;

        if ($limit > self::MAX_LIMIT_NUMBER_OF_FEATURED_ARTICLES) {
            return $controllerHelper->getBadRequestJsonResponse(
                'Invalid value specified for `limit`. ' .
                'Maximum allowed value is ' . self::MAX_LIMIT_NUMBER_OF_FEATURED_ARTICLES . '.'
            );
        }
        /** @var int $offset */
        $offset = ($page - 1) * $limit;

        /** @var ArticleRepository $articleRepository */
        $articleRepository = $this->getDoctrine()->getRepository(Article::class);

        // We get one extra element in order to check if more pages are after that one.
        /** @var int $expectedCount */

        $expectedCount = $limit + 1;
        /** @var Article[] $articles */
        $articles = $articleService->getAll($articleRepository, $offset, $expectedCount);

        /** @var bool $isLastPage */
        $isLastPage = count($articles) !== $expectedCount;
        // remove extra element UNLESS isLastPage
        if (!$isLastPage) {
            array_pop($articles);
        }

        return new JsonResponse([
            'articles' => $articles,
            'prev' => $page <= 1 ? null : '/articles/' . ($page - 1) . '/' . $limit,
            'next' => $isLastPage ? null : '/articles/' . ($page + 1) . '/' . $limit,
        ]);
    }
}