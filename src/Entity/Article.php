<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="articles")
 * @ORM\Entity(repositoryClass="App\Repository\ArticleRepository")
 */
class Article
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
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
     * @return array
     */
    public function getAttributes(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'content' => $this->content,
            'createdAt' => $this->createdAt->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }
}