<?php
namespace App\Business;

use App\Entity\Article;
use App\Repository\ArticleRepository;
use App\Repository\RepositoryHelper;
use Doctrine\Common\Cache\RedisCache;
use PHPUnit\Framework\MockObject\MockObject;

class ArticleService
{
    /**
     * @param RepositoryHelper $repositoryHelper
     * @param ArticleRepository | MockObject $articleRepository
     * @param int $page
     * @param int $limit
     *
     * @return array
     */
    public function getAll(
        RepositoryHelper $repositoryHelper,
        ArticleRepository $articleRepository,
        int $page,
        int $limit
    ): array
    {
        /** @var int $offset */
        $offset = ($page - 1) * $limit;

        $cacheKey = 'articles-' . $offset . '-' . $limit;

        /**
         * @param $cacheDriver
         * @return array
         */
        $realSearchQuery = function(RedisCache $cacheDriver) use ($offset, $limit, $cacheKey, $articleRepository) {
            $results = $articleRepository->findBy([
                    'deleted' => false
                ],
                ['createdAt' => 'DESC'],
                $limit + 1, // +1 so as we get one extra element to check if more pages are after that one.
                $offset
            );
            $cacheDriver->save($cacheKey, $results, 60);

            return $results;
        };

        /** @var Article[] $articles */
        $articles = $repositoryHelper->fetchOrCreate($cacheKey, $realSearchQuery);

        return array_map(
            function (Article $article) {
                return $article->getAttributes();
            },
            $articles
        );
    }

    /**
     * @param RepositoryHelper | MockObject $repositoryHelper
     * @param ArticleRepository | MockObject $articleRepository
     * @param string $searchedWord
     *
     * @return array
     */
    public function searchByTitleAndContent(
        RepositoryHelper $repositoryHelper,
        ArticleRepository $articleRepository,
        string $searchedWord
    ): array
    {
        return $articleRepository->searchByTitleAndContent($repositoryHelper, $searchedWord);
    }
}