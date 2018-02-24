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
        $limit = (int) $limit;
        $page = (int) $page;

        if ($limit > self::MAX_LIMIT_NUMBER_OF_FEATURED_ARTICLES) {
            return $controllerHelper->getBadRequestJsonResponse(
                'Invalid value specified for `limit`. ' .
                'Maximum allowed value is ' . self::MAX_LIMIT_NUMBER_OF_FEATURED_ARTICLES . '.'
            );
        }

        /** @var ArticleRepository $articleRepository */
        $articleRepository = $this->getDoctrine()->getRepository(Article::class);

        /** @var Article[] $articles */
        $articles = $articleService->getAll($articleRepository, $page, $limit);

        /** @var bool $isLastPage */
        $isLastPage = count($articles) !== $limit + 1;

        return new JsonResponse([
            'articles' => $isLastPage ? $articles : array_slice($articles, 0,count($articles) - 1),
            'prev' => $page <= 1 ? null : '/articles/' . ($page - 1) . '/' . $limit,
            'next' => $isLastPage ? null : '/articles/' . ($page + 1) . '/' . $limit,
        ]);
    }

    /**
     * Search all articles (title and content) for the searchedWord
     *
     * @param string $words
     * @param ArticleService $articleService
     *
     * @return JsonResponse
     *
     * @Route("/search/{words}")
     * @Method({"GET"})
     */
    public function searchByTitleAndContent(
        string $words,
        ArticleService $articleService
    ): JsonResponse
    {
        /** @var ArticleRepository $articleRepository */
        $articleRepository = $this->getDoctrine()->getRepository(Article::class);

        /** @var Article[] $articles */
        $articles = $articleService->searchByTitleAndContent($articleRepository, $words);

        return new JsonResponse([
            'articles' => $articles
        ]);
    }
}