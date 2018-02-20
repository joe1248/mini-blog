<?php
namespace App\DataFixtures;

use App\Entity\Article;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class ArticleFixtures extends Fixture
{
    const ARTICLES_COUNT = 5;

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $dataFile = './src/DataFixtures/articles.json';
        $loremIpsumApi = 'https://baconipsum.com/api/?type=meat-and-filler&paras=5&format=text';

        if (!file_exists($dataFile )) {
            $articles = [];
            for ($i = 0; $i < self::ARTICLES_COUNT ; $i++) {
                $content = file_get_contents($loremIpsumApi);
                $title = $this->extractFirstWords($content, rand(3, 5));
                $article = [
                    'title' => $title,
                    'content' => $content,
                    'createdAt' => date('Y-m-' . rand(1, 30) . ' H:i:s')
                ];
                $articles[] = $article;
            }
            file_put_contents($dataFile, json_encode($articles));
        }

        $articles = json_decode(file_get_contents($dataFile), true);

        for ($i = 0; $i < count($articles) ; $i++) {
            $article = new Article($articles[$i]);
            $manager->persist($article);
            $manager->flush();
            //$this->addReference('article' . $articlesData[$i]['id'], $article);
        }
    }

    private function extractFirstWords(string $text, int $numberOfWords = 3) : string
    {
        $chunks = explode(" ", $text, $numberOfWords + 1);
        $firstWords = implode(" ", array_splice($chunks, 0, $numberOfWords));

        return rtrim($firstWords, ', .');
    }
}