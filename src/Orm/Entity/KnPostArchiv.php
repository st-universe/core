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
use Doctrine\ORM\Mapping\OrderBy;
use Doctrine\ORM\Mapping\Table;
use Stu\Module\Communication\View\ShowSingleKn\ShowSingleKn;
use Doctrine\ORM\Mapping\UniqueConstraint;

#[Table(name: 'stu_kn_archiv')]
#[Index(name: 'plot_archiv_idx', columns: ['plot_id'])]
#[Index(name: 'kn_post_archiv_date_idx', columns: ['date'])]
#[UniqueConstraint(name: 'unique_kn_archiv_former_id', columns: ['former_id'])]
#[Entity(repositoryClass: 'Stu\Orm\Repository\KnPostArchivRepository')]
class KnPostArchiv implements KnPostArchivInterface
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'string')]
    private string $version = '';

    #[Column(type: 'integer')]
    private int $former_id;

    #[Column(type: 'string', nullable: true)]
    private ?string $titel;

    #[Column(type: 'text')]
    private string $text = '';

    #[Column(type: 'integer')]
    private int $date = 0;

    #[Column(type: 'string')]
    private string $username = '';

    #[Column(type: 'integer')]
    private int $user_id = 0;

    #[Column(type: 'integer', nullable: true)]
    private ?int $del_user_id = 0;

    #[Column(type: 'integer', nullable: true)]
    private ?int $lastedit = 0;

    #[Column(type: 'integer', nullable: true)]
    private ?int $plot_id = null;

    /**
     * @var array<mixed>
     */
    #[Column(type: 'json')]
    private array $ratings = [];

    /**
     * @var ArrayCollection<int, KnCommentArchivInterface>
     */
    #[OneToMany(targetEntity: 'KnCommentArchiv', mappedBy: 'post')]
    #[OrderBy(['id' => 'ASC'])]
    private Collection $comments;

    #[ManyToOne(targetEntity: 'RpgPlotArchiv', inversedBy: 'posts')]
    #[JoinColumn(name: 'plot_id', referencedColumnName: 'former_id')]
    private ?RpgPlotArchivInterface $rpgPlot = null;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
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

    public function getTitle(): ?string
    {
        return $this->titel;
    }

    public function setTitle(string $title): KnPostArchivInterface
    {
        $this->titel = $title;

        return $this;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function setText(string $text): KnPostArchivInterface
    {
        $this->text = $text;

        return $this;
    }

    public function getDate(): int
    {
        return $this->date;
    }

    public function setDate(int $date): KnPostArchivInterface
    {
        $this->date = $date;

        return $this;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): KnPostArchivInterface
    {
        $this->username = $username;

        return $this;
    }

    public function getUserId(): ?int
    {
        return $this->user_id;
    }

    public function getdelUserId(): ?int
    {
        return $this->del_user_id;
    }

    public function setdelUserId(?int $userid): KnPostArchivInterface
    {
        $this->del_user_id = $userid;

        return $this;
    }

    public function getEditDate(): ?int
    {
        return $this->lastedit;
    }

    public function setEditDate(int $editDate): KnPostArchivInterface
    {
        $this->lastedit = $editDate;

        return $this;
    }

    public function getPlotId(): ?int
    {
        return $this->plot_id;
    }

    public function getRpgPlot(): ?RpgPlotArchivInterface
    {
        return $this->rpgPlot;
    }

    public function setRpgPlot(?RpgPlotArchivInterface $rpgPlot): KnPostArchivInterface
    {
        $this->rpgPlot = $rpgPlot;

        return $this;
    }

    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function getRatings(): array
    {
        return $this->ratings;
    }

    public function setRatings(array $ratings): KnPostArchivInterface
    {
        $this->ratings = $ratings;
        return $this;
    }

    public function getUrl(): string
    {
        return sprintf(
            '/comm.php?%s=1&id=%d',
            ShowSingleKn::VIEW_IDENTIFIER,
            $this->getId()
        );
    }
}
