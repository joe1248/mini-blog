<?php
namespace App\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\Common\Cache\RedisCache;

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

        $realSearchQuery = function(RedisCache $cacheDriver) use ($searchedWord, $cacheKey) {
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
            $cacheDriver->save($cacheKey, $results, 60);

            return $results;
        };

        return $repositoryHelper->fetchOrCreate($cacheKey, $realSearchQuery);
    }
}