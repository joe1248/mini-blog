<?php
namespace App\Business;
use App\Entity\Article;
use App\Repository\ArticleRepository;
use PHPUnit\Framework\MockObject\MockObject;

class ArticleService
{
    /**
     * @param ArticleRepository|MockObject $articleRepository
     * @param int $offset
     * @param int $limit
     *
     * @return array
     */
    public function getAll(
        ArticleRepository $articleRepository,
        int $offset,
        int $limit
    ): array
    {
        /** @var Article[] $articles */
        $articles = $articleRepository->findBy([
            'deleted' => false
        ],
            null,// date created !! DESC
            $limit,
            $offset
        );
        return array_map(
            function (Article $article) {
                return $article->getAttributes();
            },
            $articles
        );
    }
}