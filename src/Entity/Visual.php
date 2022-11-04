<?php

namespace App\Entity;

use App\Repository\VisualRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=VisualRepository::class)
 */
class Visual
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $url;

    /**
     * @ORM\Column(type="text")
     */
    private $caption;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $visualType;

    /**
     * @ORM\ManyToOne(targetEntity=Figure::class, inversedBy="visuals")
     */
    private $figure;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getCaption(): ?string
    {
        return $this->caption;
    }

    public function setCaption(string $caption): self
    {
        $this->caption = $caption;

        return $this;
    }

    public function getVisualType(): ?string
    {
        return $this->visualType;
    }

    public function setVisualType(?string $visualType): self
    {
        $this->visualType = $visualType;

        return $this;
    }

    public function getFigure(): ?Figure
    {
        return $this->figure;
    }

    public function setFigure(?Figure $figure): self
    {
        $this->figure = $figure;

        return $this;
    }
}
