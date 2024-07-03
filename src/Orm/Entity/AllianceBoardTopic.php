<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Override;
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
use Stu\Module\Alliance\View\Topic\Topic;

#[Table(name: 'stu_alliance_topics')]
#[Index(name: 'recent_topics_idx', columns: ['alliance_id', 'last_post_date'])]
#[Index(name: 'ordered_topics_idx', columns: ['board_id', 'last_post_date'])]
#[Entity(repositoryClass: 'Stu\Orm\Repository\AllianceBoardTopicRepository')]
class AllianceBoardTopic implements AllianceBoardTopicInterface
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer')]
    private int $board_id = 0;

    #[Column(type: 'integer')]
    private int $alliance_id = 0;

    #[Column(type: 'string')]
    private string $name = '';

    #[Column(type: 'integer')]
    private int $last_post_date = 0;

    #[Column(type: 'integer')]
    private int $user_id = 0;

    #[Column(type: 'boolean')]
    private bool $sticky = false;

    #[ManyToOne(targetEntity: 'AllianceBoard', inversedBy: 'topics')]
    #[JoinColumn(name: 'board_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private AllianceBoardInterface $board;

    #[ManyToOne(targetEntity: 'Alliance')]
    #[JoinColumn(name: 'alliance_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private AllianceInterface $alliance;

    #[ManyToOne(targetEntity: 'User')]
    #[JoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private UserInterface $user;

    /**
     * @var ArrayCollection<int, AllianceBoardPostInterface>
     */
    #[OneToMany(targetEntity: 'AllianceBoardPost', mappedBy: 'topic')]
    #[OrderBy(['date' => 'DESC'])]
    private Collection $posts;

    public function __construct()
    {
        $this->posts = new ArrayCollection();
    }

    #[Override]
    public function getId(): int
    {
        return $this->id;
    }

    #[Override]
    public function getBoardId(): int
    {
        return $this->board_id;
    }

    #[Override]
    public function setBoardId(int $boardId): AllianceBoardTopicInterface
    {
        $this->board_id = $boardId;

        return $this;
    }

    #[Override]
    public function getAllianceId(): int
    {
        return $this->alliance_id;
    }

    #[Override]
    public function getName(): string
    {
        return $this->name;
    }

    #[Override]
    public function setName(string $name): AllianceBoardTopicInterface
    {
        $this->name = $name;

        return $this;
    }

    #[Override]
    public function getLastPostDate(): int
    {
        return $this->last_post_date;
    }

    #[Override]
    public function setLastPostDate(int $lastPostDate): AllianceBoardTopicInterface
    {
        $this->last_post_date = $lastPostDate;

        return $this;
    }

    #[Override]
    public function getUserId(): int
    {
        return $this->user_id;
    }

    #[Override]
    public function getSticky(): bool
    {
        return $this->sticky;
    }

    #[Override]
    public function setSticky(bool $sticky): AllianceBoardTopicInterface
    {
        $this->sticky = $sticky;

        return $this;
    }

    #[Override]
    public function getUser(): UserInterface
    {
        return $this->user;
    }

    #[Override]
    public function setUser(UserInterface $user): AllianceBoardTopicInterface
    {
        $this->user = $user;
        return $this;
    }

    #[Override]
    public function getPages(): ?array
    {
        $postCount = count($this->getPosts());

        if ($postCount <= Topic::ALLIANCEBOARDLIMITER) {
            return null;
        }

        $pages = [];
        for ($i = 1; $i <= ceil($postCount / Topic::ALLIANCEBOARDLIMITER); $i++) {
            $pages[$i] = ($i - 1) * Topic::ALLIANCEBOARDLIMITER;
        }
        return $pages;
    }

    #[Override]
    public function getPostCount(): int
    {
        return count($this->posts);
    }

    #[Override]
    public function getLatestPost(): ?AllianceBoardPostInterface
    {
        $post = $this->getPosts()->first();

        return $post === false
            ? null
            : $post;
    }

    #[Override]
    public function getBoard(): AllianceBoardInterface
    {
        return $this->board;
    }

    #[Override]
    public function setBoard(AllianceBoardInterface $board): AllianceBoardTopicInterface
    {
        $this->board = $board;

        return $this;
    }

    #[Override]
    public function getPosts(): Collection
    {
        return $this->posts;
    }

    #[Override]
    public function getAlliance(): AllianceInterface
    {
        return $this->alliance;
    }

    #[Override]
    public function setAlliance(AllianceInterface $alliance): AllianceBoardTopicInterface
    {
        $this->alliance = $alliance;

        return $this;
    }
}
