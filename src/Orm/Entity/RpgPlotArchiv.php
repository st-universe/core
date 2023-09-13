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

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\RpgPlotArchivRepository")
 * @Table(
 *     name="stu_plots_archiv",
 *     indexes={
 *         @Index(name="rpg_plot_archiv_end_date_idx", columns={"end_date"}),
 *     }
 * )
 **/
class RpgArchivPlot implements RpgPlotArchivInterface
{
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="IDENTITY")
     *
     */
    private int $id;

    /**
     * @Column(type="string", nullable=true)
     *
     */
    private ?string $version = '';

    /**
     * @Column(type="integer", nullable=true)
     * 
     */
    private ?int $former_id = 0;

    /**
     * @Column(type="integer", nullable=true)
     *
     */
    private ?int $user_id = 0;

    /**
     * @Column(type="string", nullable=true)
     *
     */
    private ?string $title = '';

    /**
     * @Column(type="text", nullable=true)
     *
     */
    private ?string $description = '';

    /**
     * @Column(type="integer", nullable=true)
     *
     */
    private ?int $start_date = 0;

    /**
     * @Column(type="integer", nullable=true)
     *
     */
    private ?int $end_date = null;

    /**
     * @var ArrayCollection<int, KnPostArchivInterface>
     *
     * @OneToMany(targetEntity="KnPostArchiv", mappedBy="rpgPlot")
     */
    private Collection $posts;

    /**
     * @var ArrayCollection<int, RpgPlotMemberArchivInterface>
     *
     * @OneToMany(targetEntity="RpgPlotMemberArchiv", mappedBy="rpgPlot", indexBy="user_id")
     */
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

    public function setTitle(string $title): RpgPlotArchivInterface
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): RpgPlotArchivInterface
    {
        $this->description = $description;

        return $this;
    }

    public function getStartDate(): int
    {
        return $this->start_date;
    }

    public function setStartDate(int $startDate): RpgPlotArchivInterface
    {
        $this->start_date = $startDate;

        return $this;
    }

    public function getEndDate(): ?int
    {
        return $this->end_date;
    }

    public function setEndDate(?int $endDate): RpgPlotArchivInterface
    {
        $this->end_date = $endDate;

        return $this;
    }

    public function isActive(): bool
    {
        return $this->getEndDate() === null || $this->getEndDate() === 0;
    }

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

    public function getMembers(): Collection
    {
        return $this->members;
    }

    public function __toString(): string
    {
        return sprintf('title: %s', $this->getTitle());
    }
}
