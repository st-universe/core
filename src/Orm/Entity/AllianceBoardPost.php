<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Stu\Orm\Repository\AllianceBoardPostRepository;

#[Table(name: 'stu_alliance_posts')]
#[Index(name: 'topic_date_idx', columns: ['topic_id', 'date'])]
#[Index(name: 'board_date_idx', columns: ['board_id', 'date'])]
#[Entity(repositoryClass: AllianceBoardPostRepository::class)]
class AllianceBoardPost
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer')]
    private int $topic_id = 0;

    #[Column(type: 'integer')]
    private int $board_id = 0;

    #[Column(type: 'string')]
    private string $name = '';

    #[Column(type: 'integer')]
    private int $date = 0;

    #[Column(type: 'text')]
    private string $text = '';

    #[Column(type: 'integer')]
    private int $user_id = 0;

    #[Column(type: 'integer', nullable: true)]
    private ?int $lastedit = null;

    #[ManyToOne(targetEntity: AllianceBoardTopic::class, inversedBy: 'posts')]
    #[JoinColumn(name: 'topic_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private AllianceBoardTopic $topic;

    #[ManyToOne(targetEntity: AllianceBoard::class, inversedBy: 'posts')]
    #[JoinColumn(name: 'board_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private AllianceBoard $board;

    #[ManyToOne(targetEntity: User::class)]
    #[JoinColumn(name: 'user_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private User $user;

    public function getId(): int
    {
        return $this->id;
    }

    public function getTopicId(): int
    {
        return $this->topic_id;
    }

    public function setTopicId(int $topicId): AllianceBoardPost
    {
        $this->topic_id = $topicId;

        return $this;
    }

    public function getBoardId(): int
    {
        return $this->board_id;
    }

    public function setBoardId(int $boardId): AllianceBoardPost
    {
        $this->board_id = $boardId;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): AllianceBoardPost
    {
        $this->name = $name;

        return $this;
    }

    public function getDate(): int
    {
        return $this->date;
    }

    public function setDate(int $date): AllianceBoardPost
    {
        $this->date = $date;

        return $this;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function setText(string $text): AllianceBoardPost
    {
        $this->text = $text;

        return $this;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): AllianceBoardPost
    {
        $this->user = $user;
        return $this;
    }

    public function getTopic(): AllianceBoardTopic
    {
        return $this->topic;
    }

    public function setTopic(AllianceBoardTopic $topic): AllianceBoardPost
    {
        $this->topic = $topic;

        return $this;
    }

    public function getBoard(): AllianceBoard
    {
        return $this->board;
    }

    public function setBoard(AllianceBoard $board): AllianceBoardPost
    {
        $this->board = $board;

        return $this;
    }

    public function getEditDate(): ?int
    {
        return $this->lastedit;
    }

    public function setEditDate(?int $editDate): AllianceBoardPost
    {
        $this->lastedit = $editDate;

        return $this;
    }
}
