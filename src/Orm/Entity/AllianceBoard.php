<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Index;
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
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="IDENTITY")
     *
     * @var int
     */
    private $id;

    /**
     * @Column(type="integer") *
     *
     * @var int
     */
    private $alliance_id = 0;

    /**
     * @Column(type="string")
     *
     * @var string
     */
    private $name = '';

    /**
     * @var ArrayCollection<int, AllianceBoardTopicInterface>
     *
     * @OneToMany(targetEntity="AllianceBoardTopic", mappedBy="board")
     */
    private $topics;

    /**
     * @var ArrayCollection<int, AllianceBoardPostInterface>
     *
     * @OneToMany(targetEntity="AllianceBoardPost", mappedBy="board")
     */
    private $posts;

    /**
     * @var AllianceInterface
     *
     * @ManyToOne(targetEntity="Alliance")
     * @JoinColumn(name="alliance_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $alliance;

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
            $this->topics->toArray(),
            function (int $sum, AllianceBoardTopicInterface $allianceBoardTopic): int {
                return $sum + $allianceBoardTopic->getPostCount();
            },
            0
        );
    }

    public function getLatestPost(): ?AllianceBoardPostInterface
    {
        global $container;

        return $container->get(AllianceBoardPostRepositoryInterface::class)->getRecentByBoard($this->getId());
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
}
