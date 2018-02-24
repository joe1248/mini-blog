<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ArticleRepository")
 * @ORM\Table(name="articles", indexes={
 *     @ORM\Index(columns={"title"},flags={"fulltext"}),
 *     @ORM\Index(columns={"title","content"},flags={"fulltext"})
 * })
 */
class Article
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(type="integer")
     */
    private $id;
    /**
     * @ORM\Column(type="string", length=50)
     */
    private $title;
    /**
     * @ORM\Column(type="text")
     */
    private $content;
    /**
     * @ORM\Column(type="datetime")
     * @Assert\DateTime()
     */
    private $createdAt;
    /**
     * @ORM\Column(name="deleted", type="boolean")
     */
    private $deleted;
    
    /**
     * Publisher constructor.
     *
     * @param array $input
     */
    public function __construct(array $input)
    {
        $this->id = $input['id'] ?? null;
        $this->title = $input['title'] ?? null;
        $this->content = $input['content'] ?? null;
        $this->deleted = $input['deleted'] ?? false;
        $this->createdAt = new \DateTime($input['createdAt'] ?? null);
    }

    /**
     * @param array $attributesList
     *
     * @return array
     */
    public function getAttributes(array $attributesList = []): array
    {
        $attributes = [
            'id' => $this->id,
            'title' => $this->title,
            'content' => $this->content,
            'createdAt' => $this->createdAt->format('Y-m-d H:i:s'),
        ];
        if (count($attributesList) == 0) {
            return $attributes;
        }
        return array_filter(
            $attributes,
            function ($key) use ($attributesList) {
                return in_array($key, $attributesList);
            },
            ARRAY_FILTER_USE_KEY
        );
    }

    /**
     * @return int
     */
    public function id(): int
    {
        return $this->id;
    }
}