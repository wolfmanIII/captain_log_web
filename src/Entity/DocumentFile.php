<?php

namespace App\Entity;

use App\Repository\DocumentFileRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DocumentFileRepository::class)]
class DocumentFile
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // Percorso relativo (es: "manuali/nemici_trast.md")
    #[ORM\Column(length: 500)]
    private ?string $path = null;

    #[ORM\Column(length: 20)]
    private ?string $extension = null;

    // hash SHA256 del file
    #[ORM\Column(length: 64)]
    private ?string $hash = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $indexedAt = null;

    /**
     * @var Collection<int, DocumentChunk>
     */
    #[ORM\OneToMany(
        mappedBy: 'file',
        targetEntity: DocumentChunk::class,
        cascade: ['remove'],
        orphanRemoval: true
    )]
    private Collection $chunks;

    public function __construct()
    {
        $this->chunks = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(string $path): self
    {
        $this->path = $path;
        return $this;
    }

    public function getExtension(): ?string
    {
        return $this->extension;
    }

    public function setExtension(string $extension): self
    {
        $this->extension = $extension;
        return $this;
    }

    public function getHash(): ?string
    {
        return $this->hash;
    }

    public function setHash(string $hash): self
    {
        $this->hash = $hash;
        return $this;
    }

    public function getIndexedAt(): ?\DateTimeImmutable
    {
        return $this->indexedAt;
    }

    public function setIndexedAt(\DateTimeImmutable $indexedAt): self
    {
        $this->indexedAt = $indexedAt;
        return $this;
    }

    /**
     * @return Collection<int, DocumentChunk>
     */
    public function getChunks(): Collection
    {
        return $this->chunks;
    }

    public function addChunk(DocumentChunk $chunk): self
    {
        if (!$this->chunks->contains($chunk)) {
            $this->chunks->add($chunk);
            $chunk->setFile($this);
        }

        return $this;
    }

    public function removeChunk(DocumentChunk $chunk): self
    {
        if ($this->chunks->removeElement($chunk)) {
            if ($chunk->getFile() === $this) {
                $chunk->setFile(null);
            }
        }

        return $this;
    }
}
