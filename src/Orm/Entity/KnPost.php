<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\KnPostRepository")
 * @Table(
 *     name="stu_kn",
 *     indexes={
 *         @Index(name="plot_idx", columns={"plot_id"}),
 *         @Index(name="date_idx", columns={"date"})
 *     }
 * )
 **/
class KnPost implements KnPostInterface
{
    /** @Id @Column(type="integer") @GeneratedValue * */
    private $id;

    /** @Column(type="string") */
    private $titel = '';

    /** @Column(type="text") */
    private $text = '';

    /** @Column(type="integer") */
    private $date = 0;

    /** @Column(type="string") */
    private $username = '';

    /** @Column(type="integer", nullable=true) */
    private $user_id = 0;

    /** @Column(type="integer") */
    private $lastedit = 0;

    /** @Column(type="integer", nullable=true) */
    private $plot_id;

    /**
     * @OneToMany(targetEntity="KnComment", mappedBy="post")
     */
    private $comments;

    /**
     * @ManyToOne(targetEntity="RpgPlot", inversedBy="posts")
     * @JoinColumn(name="plot_id", referencedColumnName="id")
     */
    private $rpgPlot;

    public function __construct() {
        $this->comments = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->titel;
    }

    public function setTitle(string $title): KnPostInterface
    {
        $this->titel = $title;

        return $this;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function setText(string $text): KnPostInterface
    {
        $this->text = $text;

        return $this;
    }

    public function getDate(): int
    {
        return $this->date;
    }

    public function setDate(int $date): KnPostInterface
    {
        $this->date = $date;

        return $this;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): KnPostInterface
    {
        $this->username = $username;

        return $this;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function setUserId(int $userId): KnPostInterface
    {
        $this->user_id = $userId;

        return $this;
    }

    public function getEditDate(): int
    {
        return $this->lastedit;
    }

    public function setEditDate(int $editDate): KnPostInterface
    {
        $this->lastedit = $editDate;

        return $this;
    }

    public function getPlotId(): ?int
    {
        return $this->plot_id;
    }

    public function setPlotId(int $plotId): KnPostInterface
    {
        $this->plot_id = $plotId;

        return $this;
    }

    public function getRpgPlot(): ?RpgPlotInterface
    {
        return $this->rpgPlot;
    }

    public function setRpgPlot(?RpgPlotInterface $rpgPlot): KnPostInterface
    {
        $this->rpgPlot = $rpgPlot;

        if ($rpgPlot !== null) {
            $this->setPlotId($rpgPlot->getId());
        }

        return $this;
    }

    public function getComments(): Collection
    {
        return $this->comments;
    }
}
