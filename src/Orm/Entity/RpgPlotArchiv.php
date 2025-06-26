<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Stu\Orm\Repository\RpgPlotArchivRepository;

#[Table(name: 'stu_plots_archiv')]
#[Index(name: 'rpg_plot_archiv_end_date_idx', columns: ['end_date'])]
#[UniqueConstraint(name: 'unique_plot_id', columns: ['id'])]
#[UniqueConstraint(name: 'unique_former_id', columns: ['former_id'])]
#[Entity(repositoryClass: RpgPlotArchivRepository::class)]
class RpgPlotArchiv
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'string')]
    private string $version = '';

    #[Column(type: 'integer')]
    private int $former_id = 0;

    #[Column(type: 'integer')]
    private int $user_id = 0;

    #[Column(type: 'string')]
    private string $title = '';

    #[Column(type: 'text')]
    private string $description = '';

    #[Column(type: 'integer')]
    private int $start_date = 0;

    #[Column(type: 'integer', nullable: true)]
    private ?int $end_date = null;

    /**
     * @var ArrayCollection<int, KnPostArchiv>
     */
    #[OneToMany(targetEntity: KnPostArchiv::class, mappedBy: 'rpgPlot')]
    private Collection $posts;

    /**
     * @var ArrayCollection<int, RpgPlotMemberArchiv>
     */
    #[OneToMany(targetEntity: RpgPlotMemberArchiv::class, mappedBy: 'rpgPlot', indexBy: 'user_id')]
    private Collection $members;

    public function __construct()
    {
        $this->posts = new ArrayCollection();
        $this->members = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getVersion(): ?string
    {
        return $this->version;
    }

    public function getFormerId(): int
    {
        return $this->former_id;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): RpgPlotArchiv
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): RpgPlotArchiv
    {
        $this->description = $description;

        return $this;
    }

    public function getStartDate(): int
    {
        return $this->start_date;
    }

    public function setStartDate(int $startDate): RpgPlotArchiv
    {
        $this->start_date = $startDate;

        return $this;
    }

    public function getEndDate(): ?int
    {
        return $this->end_date;
    }

    public function setEndDate(?int $endDate): RpgPlotArchiv
    {
        $this->end_date = $endDate;

        return $this;
    }

    public function isActive(): bool
    {
        return $this->getEndDate() === null || $this->getEndDate() === 0;
    }

    /**
     * @return Collection<int, KnPostArchiv>
     */
    public function getPosts(): Collection
    {
        return $this->posts;
    }

    public function getMemberCount(): int
    {
        return $this->members->count();
    }

    public function getPostingCount(): int
    {
        return $this->posts->count();
    }

    /**
     * @return Collection<int, RpgPlotMemberArchiv>
     */
    public function getMembers(): Collection
    {
        return $this->members;
    }

    public function __toString(): string
    {
        return sprintf('title: %s', $this->getTitle());
    }
}
