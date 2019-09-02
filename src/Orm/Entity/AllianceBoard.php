<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Stu\Orm\Repository\AllianceBoardPostRepositoryInterface;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\AllianceBoardRepository")
 * @Table(
 *     name="stu_alliance_boards",
 *     indexes={
 *         @Index(name="alliance_idx", columns={"alliance_id"})
 *     }
 * )
 **/
class AllianceBoard implements AllianceBoardInterface
{
    /** @Id @Column(type="integer") @GeneratedValue * */
    private $id;

    /** @Column(type="integer") * */
    private $alliance_id = 0;

    /** @Column(type="string") */
    private $name = '';

    /**
     * @OneToMany(targetEntity="AllianceBoardTopic", mappedBy="board")
     */
    private $topics;

    /**
     * @OneToMany(targetEntity="AllianceBoardPost", mappedBy="board")
     */
    private $posts;

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

    public function setAllianceId(int $allianceId): AllianceBoardInterface
    {
        $this->alliance_id = $allianceId;

        return $this;
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

    public function getTopicCount(): int {
        return count($this->topics);
    }

    public function getPostCount(): int {
        return array_reduce(
            $this->topics->toArray(),
            function (int $sum, AllianceBoardTopicInterface $allianceBoardTopic): int {
                return $sum + $allianceBoardTopic->getPostCount();
            },
            0
        );
    }

    public function getLatestPost(): ?AllianceBoardPostInterface {
        global $container;

        return $container->get(AllianceBoardPostRepositoryInterface::class)->getRecentByBoard($this->getId());
    }

    public function getTopics(): Collection
    {
        return $this->topics;
    }
}
