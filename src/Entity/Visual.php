<?php

namespace App\Entity;

use App\Repository\VisualRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Form\FormTypeInterface;

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
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $url;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $caption;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $visualKind;

    /**
     * @ORM\ManyToOne(targetEntity=Figure::class, inversedBy="visuals")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $figure = null;

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

    public function getVisualKind(): ?string
    {
        return $this->visualKind;
    }

    public function setVisualKind(?string $visualKind): self
    {
        $this->visualKind = $visualKind;

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
