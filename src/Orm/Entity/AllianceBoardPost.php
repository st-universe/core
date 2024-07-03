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
use Override;
use Stu\Orm\Repository\AllianceBoardPostRepository;

#[Table(name: 'stu_alliance_posts')]
#[Index(name: 'topic_date_idx', columns: ['topic_id', 'date'])]
#[Index(name: 'board_date_idx', columns: ['board_id', 'date'])]
#[Entity(repositoryClass: AllianceBoardPostRepository::class)]
class AllianceBoardPost implements AllianceBoardPostInterface
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

    #[ManyToOne(targetEntity: 'AllianceBoardTopic', inversedBy: 'posts')]
    #[JoinColumn(name: 'topic_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private AllianceBoardTopicInterface $topic;

    #[ManyToOne(targetEntity: 'AllianceBoard', inversedBy: 'posts')]
    #[JoinColumn(name: 'board_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private AllianceBoardInterface $board;

    #[ManyToOne(targetEntity: 'User')]
    #[JoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private UserInterface $user;

    #[Override]
    public function getId(): int
    {
        return $this->id;
    }

    #[Override]
    public function getTopicId(): int
    {
        return $this->topic_id;
    }

    #[Override]
    public function setTopicId(int $topicId): AllianceBoardPostInterface
    {
        $this->topic_id = $topicId;

        return $this;
    }

    #[Override]
    public function getBoardId(): int
    {
        return $this->board_id;
    }

    #[Override]
    public function setBoardId(int $boardId): AllianceBoardPostInterface
    {
        $this->board_id = $boardId;

        return $this;
    }

    #[Override]
    public function getName(): string
    {
        return $this->name;
    }

    #[Override]
    public function setName(string $name): AllianceBoardPostInterface
    {
        $this->name = $name;

        return $this;
    }

    #[Override]
    public function getDate(): int
    {
        return $this->date;
    }

    #[Override]
    public function setDate(int $date): AllianceBoardPostInterface
    {
        $this->date = $date;

        return $this;
    }

    #[Override]
    public function getText(): string
    {
        return $this->text;
    }

    #[Override]
    public function setText(string $text): AllianceBoardPostInterface
    {
        $this->text = $text;

        return $this;
    }

    #[Override]
    public function getUserId(): int
    {
        return $this->user_id;
    }

    #[Override]
    public function getUser(): UserInterface
    {
        return $this->user;
    }

    #[Override]
    public function setUser(UserInterface $user): AllianceBoardPostInterface
    {
        $this->user = $user;
        return $this;
    }

    #[Override]
    public function getTopic(): AllianceBoardTopicInterface
    {
        return $this->topic;
    }

    #[Override]
    public function setTopic(AllianceBoardTopicInterface $topic): AllianceBoardPostInterface
    {
        $this->topic = $topic;

        return $this;
    }

    #[Override]
    public function getBoard(): AllianceBoardInterface
    {
        return $this->board;
    }

    #[Override]
    public function setBoard(AllianceBoardInterface $board): AllianceBoardPostInterface
    {
        $this->board = $board;

        return $this;
    }

    #[Override]
    public function getEditDate(): ?int
    {
        return $this->lastedit;
    }

    #[Override]
    public function setEditDate(?int $editDate): AllianceBoardPostInterface
    {
        $this->lastedit = $editDate;

        return $this;
    }
}
