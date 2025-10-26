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
use Stu\Component\Game\ModuleEnum;
use Stu\Lib\General\EntityWithHrefInterface;
use Stu\Module\Communication\View\ShowSingleKn\ShowSingleKn;
use Stu\Orm\Attribute\TruncateOnGameReset;
use Stu\Orm\Repository\KnPostRepository;

#[Table(name: 'stu_kn')]
#[Index(name: 'plot_idx', columns: ['plot_id'])]
#[Index(name: 'kn_post_date_idx', columns: ['date'])]
#[Index(name: 'kn_post_user_idx', columns: ['user_id'])]
#[Entity(repositoryClass: KnPostRepository::class)]
#[TruncateOnGameReset]
class KnPost implements EntityWithHrefInterface
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'string', nullable: true)]
    private ?string $titel = '';

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

    #[Column(type: 'integer')]
    private int $lastedit = 0;

    #[Column(type: 'integer', nullable: true)]
    private ?int $plot_id = null;

    #[Column(type: 'integer', nullable: true)]
    private ?int $deleted = null;

    /**
     * @var array<mixed>
     */
    #[Column(type: 'json')]
    private array $ratings = [];

    /**
     * @var ArrayCollection<int, KnComment>
     */
    #[OneToMany(targetEntity: KnComment::class, mappedBy: 'post')]
    #[OrderBy(['id' => 'ASC'])]
    private Collection $comments;

    /**
     * @var ArrayCollection<int, KnCharacter>
     */
    #[OneToMany(targetEntity: KnCharacter::class, mappedBy: 'knPost')]
    private Collection $knCharacters;

    #[ManyToOne(targetEntity: RpgPlot::class, inversedBy: 'posts')]
    #[JoinColumn(name: 'plot_id', referencedColumnName: 'id')]
    private ?RpgPlot $rpgPlot = null;

    #[ManyToOne(targetEntity: User::class)]
    #[JoinColumn(name: 'user_id', nullable: false, referencedColumnName: 'id')]
    private User $user;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
        $this->knCharacters = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->titel;
    }

    public function setTitle(string $title): KnPost
    {
        $this->titel = $title;

        return $this;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function setText(string $text): KnPost
    {
        $this->text = $text;

        return $this;
    }

    public function getDate(): int
    {
        return $this->date;
    }

    public function setDate(int $date): KnPost
    {
        $this->date = $date;

        return $this;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): KnPost
    {
        $this->username = $username;

        return $this;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function getdelUserId(): ?int
    {
        return $this->del_user_id;
    }

    public function setdelUserId(?int $userid): KnPost
    {
        $this->del_user_id = $userid;

        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): KnPost
    {
        $this->user = $user;
        return $this;
    }

    public function getEditDate(): int
    {
        return $this->lastedit;
    }

    public function setEditDate(int $editDate): KnPost
    {
        $this->lastedit = $editDate;

        return $this;
    }

    public function getPlotId(): ?int
    {
        return $this->plot_id;
    }

    public function getRpgPlot(): ?RpgPlot
    {
        return $this->rpgPlot;
    }

    public function setRpgPlot(?RpgPlot $rpgPlot): KnPost
    {
        $this->rpgPlot = $rpgPlot;

        return $this;
    }

    /**
     * @return Collection<int, KnComment>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    /**
     * @return array<mixed>
     */
    public function getRatings(): array
    {
        return $this->ratings;
    }

    /**
     * @param array<mixed> $ratings
     */
    public function setRatings(array $ratings): KnPost
    {
        $this->ratings = $ratings;
        return $this;
    }

    public function getDeleted(): ?int
    {
        return $this->deleted;
    }

    public function setDeleted(?int $timestamp): KnPost
    {
        $this->deleted = $timestamp;

        return $this;
    }

    public function getUrl(): string
    {
        return sprintf(
            '/comm.php?%s=1&knid=%d',
            ShowSingleKn::VIEW_IDENTIFIER,
            $this->getId()
        );
    }

    /**
     * @return Collection<int, KnCharacter>
     */
    public function getKnCharacters(): Collection
    {
        return $this->knCharacters;
    }

    #[\Override]
    public function getHref(): string
    {
        return sprintf(
            '%s?%s=1&knid=%d',
            ModuleEnum::COMMUNICATION->getPhpPage(),
            ShowSingleKn::VIEW_IDENTIFIER,
            $this->getId()
        );
    }
}
