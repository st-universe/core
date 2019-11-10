<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Stu\Orm\Repository\KnPostRepositoryInterface;
use Stu\Orm\Repository\RpgPlotMemberRepositoryInterface;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\RpgPlotRepository")
 * @Table(
 *     name="stu_plots",
 *     indexes={
 *         @Index(name="end_date_idx", columns={"end_date"}),
 *         @Index(name="user_idx", columns={"user_id"}),
 *     }
 * )
 **/
class RpgPlot implements RpgPlotInterface
{
    /** @Id @Column(type="integer") @GeneratedValue * */
    private $id;

    /** @Column(type="integer") * */
    private $user_id = 0;

    /** @Column(type="string") */
    private $title = '';

    /** @Column(type="text") */
    private $description = '';

    /** @Column(type="integer") * */
    private $start_date = 0;

    /** @Column(type="integer", nullable=true) * */
    private $end_date;

    /**
     * @OneToMany(targetEntity="KnPost", mappedBy="rpgPlot")
     */
    private $posts;

    /**
     * @OneToMany(targetEntity="RpgPlotMember", mappedBy="rpgPlot")
     */
    private $members;

    /**
     * @ManyToOne(targetEntity="User")
     * @JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;

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
}
