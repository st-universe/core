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

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\RpgPlotRepository")
 * @Table(
 *     name="stu_plots",
 *     indexes={
 *         @Index(name="rpg_plot_end_date_idx", columns={"end_date"}),
 *         @Index(name="rpg_plot_user_idx", columns={"user_id"}),
 *     }
 * )
 **/
class RpgPlot implements RpgPlotInterface
{
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="IDENTITY")
     *
     */
    private int $id;

    /**
     * @Column(type="integer")
     *
     */
    private int $user_id = 0;

    /**
     * @Column(type="string")
     *
     */
    private string $title = '';

    /**
     * @Column(type="text")
     *
     */
    private string $description = '';

    /**
     * @Column(type="integer")
     *
     */
    private int $start_date = 0;

    /**
     * @Column(type="integer", nullable=true)
     *
     */
    private ?int $end_date = null;

    /**
     * @var ArrayCollection<int, KnPostInterface>
     *
     * @OneToMany(targetEntity="KnPost", mappedBy="rpgPlot")
     */
    private Collection $posts;

    /**
     * @var ArrayCollection<int, RpgPlotMemberInterface>
     *
     * @OneToMany(targetEntity="RpgPlotMember", mappedBy="rpgPlot", indexBy="user_id")
     */
    private ArrayCollection $members;

    /**
     *
     * @ManyToOne(targetEntity="User")
     * @JoinColumn(name="user_id", referencedColumnName="id")
     */
    private UserInterface $user;

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

    public function getUser(): UserInterface
    {
        return $this->user;
    }

    public function setUser(UserInterface $user): RpgPlotInterface
    {
        $this->user = $user;
        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): RpgPlotInterface
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): RpgPlotInterface
    {
        $this->description = $description;

        return $this;
    }

    public function getStartDate(): int
    {
        return $this->start_date;
    }

    public function setStartDate(int $startDate): RpgPlotInterface
    {
        $this->start_date = $startDate;

        return $this;
    }

    public function getEndDate(): ?int
    {
        return $this->end_date;
    }

    public function setEndDate(?int $endDate): RpgPlotInterface
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
