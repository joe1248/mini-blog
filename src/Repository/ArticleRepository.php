<?php
namespace App\Repository;

use Doctrine\ORM\EntityRepository;

class ArticleRepository extends EntityRepository
{
    const MAX_LIMIT_SEARCH_RESULTS = 15;

    /**
     * @param RepositoryHelper $repositoryHelper
     * @param string $searchedWord
     *
     * @return array
     */
    public function searchByTitleAndContent(RepositoryHelper $repositoryHelper, string $searchedWord): array
    {
        $cacheKey = 'search-' . $searchedWord;

        /**
         * @return array
         */
        $realSearchQuery = function() use ($searchedWord, $cacheKey): array
        {
            /** @var array $results */
            $results = $this->createQueryBuilder('p')
                ->select(
                    'p.id, p.title, ' .
                    'MATCH_AGAINST (p.title, :searchedWord) as score1, ' .
                    'MATCH_AGAINST (p.title, p.content, :searchedWord) as score2')
                ->where("MATCH_AGAINST (p.title, p.content, :searchedWord 'IN NATURAL LANGUAGE MODE') > 0")
                ->andWhere("p.deleted = false")
                ->setParameter('searchedWord', $searchedWord)
                ->orderBy('score1', 'DESC')
                ->addOrderBy('score2', 'DESC')
                ->getQuery()
                ->setFirstResult(0)
                ->setMaxResults(self::MAX_LIMIT_SEARCH_RESULTS)
                ->getArrayResult();

            return $results;
        };

        return $repositoryHelper->fetchOrCreate($cacheKey, $realSearchQuery);
    }

    /**
     * @param RepositoryHelper $repositoryHelper
     *
     * @return int
     */
    public function getCount(RepositoryHelper $repositoryHelper): int
    {
        $cacheKey = 'articles_count';

        $realSearchQuery = function() use ($cacheKey): array
        {
            /** @var int $count */
            $count = $this->createQueryBuilder('p')
                ->select('count(1)')
                ->where("p.deleted = false")
                ->getQuery()
                ->getSingleScalarResult();

            return ['articles_count' => $count];
        };
        $articlesCount = $repositoryHelper->fetchOrCreate($cacheKey, $realSearchQuery);

        return $articlesCount['articles_count'];
    }
}