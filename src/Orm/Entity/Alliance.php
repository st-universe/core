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
use Stu\Component\Alliance\AllianceEnum;
use Stu\Component\Alliance\Exception\AllianceFounderNotSetException;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\AllianceRepository")
 * @Table(
 *     name="stu_alliances",
 *     indexes={
 *     }
 * )
 **/
class Alliance implements AllianceInterface
{
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="IDENTITY")
     *
     */
    private int $id;

    /**
     * @Column(type="string")
     *
     */
    private string $name = '';

    /**
     * @Column(type="text")
     *
     */
    private string $description = '';

    /**
     * @Column(type="string")
     *
     */
    private string $homepage = '';

    /**
     * @Column(type="integer")
     *
     */
    private int $date = 0;

    /**
     * @Column(type="integer", nullable=true)
     *
     */
    private ?int $faction_id = null;

    /**
     * @Column(type="boolean")
     *
     */
    private bool $accept_applications = false;

    /**
     * @Column(type="string", length=32)
     *
     */
    private string $avatar = '';

    /**
     * @Column(type="string", length=7)
     *
     */
    private string $rgb_code = '';

    /**
     *
     * @ManyToOne(targetEntity="Faction")
     * @JoinColumn(name="faction_id", referencedColumnName="id")
     */
    private ?FactionInterface $faction = null;

    /**
     * @var ArrayCollection<int, UserInterface>
     *
     * @OneToMany(targetEntity="User", mappedBy="alliance")
     */
    private Collection $members;

    /**
     * @var ArrayCollection<int, AllianceJobInterface>
     *
     * @OneToMany(targetEntity="AllianceJob", mappedBy="alliance", indexBy="type")
     */
    private Collection $jobs;

    public function __construct()
    {
        $this->members = new ArrayCollection();
        $this->jobs = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): AllianceInterface
    {
        $this->name = $name;
        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): AllianceInterface
    {
        $this->description = $description;
        return $this;
    }

    public function getHomepage(): string
    {
        return $this->homepage;
    }

    public function setHomepage(string $homepage): AllianceInterface
    {
        $this->homepage = $homepage;
        return $this;
    }

    public function getDate(): int
    {
        return $this->date;
    }

    public function setDate(int $date): AllianceInterface
    {
        $this->date = $date;
        return $this;
    }

    public function getFactionId(): ?int
    {
        return $this->faction_id;
    }

    public function setFaction(?FactionInterface $faction): AllianceInterface
    {
        $this->faction = $faction;
        return $this;
    }

    public function getAcceptApplications(): bool
    {
        return $this->accept_applications;
    }

    public function setAcceptApplications(bool $acceptApplications): AllianceInterface
    {
        $this->accept_applications = $acceptApplications;
        return $this;
    }

    public function hasAvatar(): bool
    {
        return strlen($this->getAvatar()) > 0;
    }

    public function getAvatar(): string
    {
        return $this->avatar;
    }

    public function setAvatar(string $avatar): AllianceInterface
    {
        $this->avatar = $avatar;
        return $this;
    }

    public function getRgbCode(): string
    {
        return $this->rgb_code;
    }

    public function setRgbCode(string $rgbCode): AllianceInterface
    {
        $this->rgb_code = $rgbCode;
        return $this;
    }

    /**
     * @throws AllianceFounderNotSetException
     */
    public function getFounder(): AllianceJobInterface
    {
        $job = $this->jobs->get(AllianceEnum::ALLIANCE_JOBS_FOUNDER);
        if ($job === null) {
            // alliance without founder? this should not happen
            throw new AllianceFounderNotSetException();
        }
        return $job;
    }

    public function getSuccessor(): ?AllianceJobInterface
    {
        return $this->jobs->get(AllianceEnum::ALLIANCE_JOBS_SUCCESSOR);
    }

    public function getDiplomatic(): ?AllianceJobInterface
    {
        return $this->jobs->get(AllianceEnum::ALLIANCE_JOBS_DIPLOMATIC);
    }

    public function getMembers(): Collection
    {
        return $this->members;
    }

    public function isNpcAlliance(): bool
    {
        $founder = $this->jobs->get(AllianceEnum::ALLIANCE_JOBS_FOUNDER);

        if ($founder === null) {
            return false;
        }

        return $founder->getUser()->isNpc();
    }

    public function getJobs(): Collection
    {
        return $this->jobs;
    }

    public function __toString(): string
    {
        return $this->getName();
    }

    public function hasTranslation(): bool
    {
        $text = $this->getDescription();
        return strpos($text, '[translate]') !== false && strpos($text, '[/translate]') !== false;
    }
}
