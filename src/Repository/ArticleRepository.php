<?php
namespace App\Repository;

use Doctrine\ORM\EntityRepository;

class ArticleRepository extends EntityRepository
{
    const MAX_LIMIT_SEARCH_RESULTS = 15;

    /**
     * @param string $searchedWord
     *
     * @return array
     */
    public function searchByTitleAndContent(string $searchedWord): array
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
            ->setFirstResult( 0 )
            ->setMaxResults( self::MAX_LIMIT_SEARCH_RESULTS )
            ->getArrayResult();

        return $results;
    }
}