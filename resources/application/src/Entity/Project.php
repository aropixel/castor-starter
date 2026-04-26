<?php

namespace App\Entity;

use Aropixel\AdminBundle\Entity\Publishable;
use Aropixel\AdminBundle\Entity\PublishableTrait;
use App\Repository\ProjectRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Entity(repositoryClass: ProjectRepository::class)]
#[ORM\Table(name: 'app_project')]
class Project implements Publishable
{
    use PublishableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'string', length: 20)]
    private string $status = Publishable::STATUS_OFFLINE;

    #[Gedmo\Slug(fields: ['title'])]
    #[ORM\Column(type: 'string', length: 255, unique: true)]
    private ?string $slug = null;

    #[Gedmo\Timestampable(on: 'create')]
    #[ORM\Column(name: 'created_at', type: 'datetime')]
    private ?\DateTime $createdAt = null;

    #[Gedmo\Timestampable(on: 'update')]
    #[ORM\Column(name: 'updated_at', type: 'datetime', nullable: true)]
    private ?\DateTime $updatedAt = null;

    #[ORM\OneToOne(targetEntity: ProjectImage::class, mappedBy: 'project', cascade: ['persist', 'remove'])]
    private ?ProjectImage $image = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    public function getImage(): ?ProjectImage
    {
        return $this->image;
    }

    public function setImage(?ProjectImage $image): self
    {
        if (null === $image && null !== $this->image) {
            $this->image->setProject(null);
        }

        if (null !== $image && $image->getProject() !== $this) {
            $image->setProject($this);
        }

        $this->image = $image;

        return $this;
    }
}
