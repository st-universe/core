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
use Stu\Orm\Repository\AllianceBoardRepository;

#[Table(name: 'stu_alliance_boards')]
#[Index(name: 'alliance_idx', columns: ['alliance_id'])]
#[Entity(repositoryClass: AllianceBoardRepository::class)]
class AllianceBoard
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer')]
    private int $alliance_id = 0;

    #[Column(type: 'string')]
    private string $name = '';

    /**
     * @var ArrayCollection<int, AllianceBoardTopic>
     */
    #[OneToMany(targetEntity: AllianceBoardTopic::class, mappedBy: 'board')]
    private Collection $topics;

    /**
     * @var ArrayCollection<int, AllianceBoardPost>
     */
    #[OneToMany(targetEntity: AllianceBoardPost::class, mappedBy: 'board')]
    #[OrderBy(['date' => 'DESC'])]
    private Collection $posts;

    #[ManyToOne(targetEntity: Alliance::class)]
    #[JoinColumn(name: 'alliance_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Alliance $alliance;

    public function __construct()
    {
        $this->topics = new ArrayCollection();
        $this->posts = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): AllianceBoard
    {
        $this->name = $name;

        return $this;
    }

    public function getTopicCount(): int
    {
        return count($this->topics);
    }

    public function getPostCount(): int
    {
        return array_reduce(
            $this->getTopics()->toArray(),
            fn(int $sum, AllianceBoardTopic $allianceBoardTopic): int => $sum + $allianceBoardTopic->getPostCount(),
            0
        );
    }

    public function getLatestPost(): ?AllianceBoardPost
    {
        $post = $this->getPosts()->first();

        return $post === false
            ? null
            : $post;
    }

    /**
     * @return Collection<int, AllianceBoardTopic>
     */
    public function getTopics(): Collection
    {
        return $this->topics;
    }

    public function getAlliance(): Alliance
    {
        return $this->alliance;
    }

    public function setAlliance(Alliance $alliance): AllianceBoard
    {
        $this->alliance = $alliance;

        return $this;
    }

    /**
     * @return Collection<int, AllianceBoardPost>
     */
    public function getPosts(): Collection
    {
        return $this->posts;
    }
}
