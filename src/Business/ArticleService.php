<?php
namespace App\Business;
use App\Entity\Article;
use App\Repository\ArticleRepository;
use PHPUnit\Framework\MockObject\MockObject;

class ArticleService
{
    /**
     * @param ArticleRepository | MockObject $articleRepository
     * @param int $page
     * @param int $limit
     *
     * @return array
     */
    public function getAll(
        ArticleRepository $articleRepository,
        int $page,
        int $limit
    ): array
    {
        /** @var int $offset */
        $offset = ($page - 1) * $limit;

        /** @var Article[] $articles */
        $articles = $articleRepository->findBy([
            'deleted' => false
        ],
            null,// date created !! DESC
            // We always get one extra element in order to check if more pages are after that one.
            $limit + 1,
            $offset
        );
        return array_map(
            function (Article $article) {
                return $article->getAttributes();
            },
            $articles
        );
    }

    /**
     * @param ArticleRepository | MockObject $articleRepository
     * @param string $searchedWord
     *
     * @return array
     */
    public function searchByTitleAndContent(
        ArticleRepository $articleRepository,
        string $searchedWord
    ): array
    {
        return $articleRepository->searchByTitleAndContent($searchedWord);
    }
}