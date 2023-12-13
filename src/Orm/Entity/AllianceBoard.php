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

#[Table(name: 'stu_alliance_boards')]
#[Index(name: 'alliance_idx', columns: ['alliance_id'])]
#[Entity(repositoryClass: 'Stu\Orm\Repository\AllianceBoardRepository')]
class AllianceBoard implements AllianceBoardInterface
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
     * @var Collection<int, AllianceBoardTopicInterface>
     */
    #[OneToMany(targetEntity: 'AllianceBoardTopic', mappedBy: 'board')]
    private Collection $topics;

    /**
     * @var ArrayCollection<int, AllianceBoardPostInterface>
     */
    #[OneToMany(targetEntity: 'AllianceBoardPost', mappedBy: 'board')]
    #[OrderBy(['date' => 'DESC'])]
    private Collection $posts;

    #[ManyToOne(targetEntity: 'Alliance')]
    #[JoinColumn(name: 'alliance_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private AllianceInterface $alliance;

    public function __construct()
    {
        $this->topics = new ArrayCollection();
        $this->posts = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getAllianceId(): int
    {
        return $this->alliance_id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): AllianceBoardInterface
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
            fn (int $sum, AllianceBoardTopicInterface $allianceBoardTopic): int => $sum + $allianceBoardTopic->getPostCount(),
            0
        );
    }

    public function getLatestPost(): ?AllianceBoardPostInterface
    {
        $post = $this->getPosts()->first();

        return $post === false
            ? null
            : $post;
    }

    public function getTopics(): Collection
    {
        return $this->topics;
    }

    public function getAlliance(): AllianceInterface
    {
        return $this->alliance;
    }

    public function setAlliance(AllianceInterface $alliance): AllianceBoardInterface
    {
        $this->alliance = $alliance;

        return $this;
    }

    public function getPosts(): Collection
    {
        return $this->posts;
    }
}
