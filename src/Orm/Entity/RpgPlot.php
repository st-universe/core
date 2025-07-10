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
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;
use Stu\Orm\Attribute\TruncateOnGameReset;
use Stu\Orm\Repository\RpgPlotRepository;

#[Table(name: 'stu_plots')]
#[Index(name: 'rpg_plot_end_date_idx', columns: ['end_date'])]
#[Index(name: 'rpg_plot_user_idx', columns: ['user_id'])]
#[Entity(repositoryClass: RpgPlotRepository::class)]
#[TruncateOnGameReset]
class RpgPlot
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

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
     * @var ArrayCollection<int, KnPost>
     */
    #[OneToMany(targetEntity: KnPost::class, mappedBy: 'rpgPlot')]
    private Collection $posts;

    /**
     * @var ArrayCollection<int, RpgPlotMember>
     */
    #[OneToMany(targetEntity: RpgPlotMember::class, mappedBy: 'rpgPlot', indexBy: 'user_id')]
    private Collection $members;

    #[ManyToOne(targetEntity: User::class)]
    #[JoinColumn(name: 'user_id', nullable: false, referencedColumnName: 'id')]
    private User $user;

    public function __construct()
    {
        $this->posts = new ArrayCollection();
        $this->members = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): RpgPlot
    {
        $this->user = $user;
        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): RpgPlot
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): RpgPlot
    {
        $this->description = $description;

        return $this;
    }

    public function getStartDate(): int
    {
        return $this->start_date;
    }

    public function setStartDate(int $startDate): RpgPlot
    {
        $this->start_date = $startDate;

        return $this;
    }

    public function getEndDate(): ?int
    {
        return $this->end_date;
    }

    public function setEndDate(?int $endDate): RpgPlot
    {
        $this->end_date = $endDate;

        return $this;
    }

    public function isActive(): bool
    {
        return $this->getEndDate() === null || $this->getEndDate() === 0;
    }

    /**
     * @return Collection<int, KnPost>
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
     * @return Collection<int, RpgPlotMember>
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
