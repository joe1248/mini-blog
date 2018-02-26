<?php

namespace App\Controller;

use App\Business\ArticleService;
use App\Entity\Article;
use App\Repository\ArticleRepository;
use App\Repository\RepositoryHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

class BlogController extends Controller
{
    const MAX_LIMIT_NUMBER_OF_FEATURED_ARTICLES = 50;


    /**
     * Get one article
     *
     * @param String $id
     * @param RepositoryHelper $repositoryHelper
	 *
     * @return JsonResponse
	 *
	 * @Route("/article/{id}", name="article_view")
	 * @Method({"GET"})
	 */
	public function getOne(String $id, RepositoryHelper $repositoryHelper): JsonResponse
	{
        /** @var ArticleRepository $articleRepository */
        $articleRepository = $this->getDoctrine()->getRepository(Article::class);

        $cacheKey = 'article-' . $id;

        /**
         * @return array
         */
        $realSearchQuery = function() use ($id, $cacheKey, $articleRepository) : array
        {
            /** @var Article $article */
            $article = $articleRepository->find($id);

            return $article->getAttributes();
        };

        /** @var array $articleProps */
        $articleProps = $repositoryHelper->fetchOrCreate($cacheKey, $realSearchQuery);

        return new JsonResponse($articleProps);
	}

    /**
     * List all articles ordered by date DESC
     *
     * @param string $page
     * @param string $limit
     * @param ArticleService $articleService
     * @param ControllerHelper $controllerHelper
     * @param RepositoryHelper $repositoryHelper
     *
     * @return JsonResponse
     *
     * @Route("/")
     * @Route("/articles/{page}/{limit}", requirements={"page" = "\d+", "limit" = "\d+"}, name="articles_list")
     * @Method({"GET"})
     */
    public function getAll(
        string $page = '1',
        string $limit = '3',
        ArticleService $articleService,
        ControllerHelper $controllerHelper,
        RepositoryHelper $repositoryHelper
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
        $articles = $articleService->getAll($repositoryHelper, $articleRepository, $page, $limit);

        /** @var bool $isLastPage */
        $isLastPage = count($articles) !== $limit + 1;

		return new JsonResponse([
            'articles' => $isLastPage ? $articles : array_slice($articles, 0,count($articles) - 1),
            'prev' => $page <= 1 ? '' : '/articles/' . ($page - 1) . '/' . $limit,
            'next' => $isLastPage ? '' : '/articles/' . ($page + 1) . '/' . $limit,
            'numberOfPages' => ceil($articleService->getCount($repositoryHelper, $articleRepository) / $limit),
        ]);
    }

    /**
     * Search all articles (title and content) for the searchedWord
     *
     * @param string $words
     * @param ArticleService $articleService
     * @param RepositoryHelper $repositoryHelper
     *
     * @return JsonResponse
     *
     * @Route("/search/{words}")
     * @Method({"GET"})
     */
    public function searchByTitleAndContent(
        string $words,
        ArticleService $articleService,
        RepositoryHelper $repositoryHelper
    ): JsonResponse
    {
        /** @var ArticleRepository $articleRepository */
        $articleRepository = $this->getDoctrine()->getRepository(Article::class);

        /** @var Article[] $articles */
        $articles = $articleService->searchByTitleAndContent($repositoryHelper, $articleRepository, $words);

        return new JsonResponse($articles);
    }
}