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
use Stu\Component\Alliance\Enum\AllianceJobTypeEnum;
use Stu\Component\Alliance\Exception\AllianceFounderNotSetException;
use Stu\Orm\Attribute\TruncateOnGameReset;
use Stu\Orm\Repository\AllianceRepository;

#[Table(name: 'stu_alliances')]
#[Entity(repositoryClass: AllianceRepository::class)]
#[TruncateOnGameReset]
class Alliance
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'string')]
    private string $name = '';

    #[Column(type: 'text')]
    private string $description = '';

    #[Column(type: 'string')]
    private string $homepage = '';

    #[Column(type: 'integer')]
    private int $date = 0;

    #[Column(type: 'integer', nullable: true)]
    private ?int $faction_id = null;

    #[Column(type: 'boolean')]
    private bool $accept_applications = false;

    #[Column(type: 'string', length: 32)]
    private string $avatar = '';

    #[Column(type: 'string', length: 7)]
    private string $rgb_code = '';

    #[ManyToOne(targetEntity: Faction::class)]
    #[JoinColumn(name: 'faction_id', referencedColumnName: 'id')]
    private ?Faction $faction = null;

    /**
     * @var ArrayCollection<int, AllianceSettings>
     */
    #[OneToMany(targetEntity: AllianceSettings::class, mappedBy: 'alliance')]
    private Collection $settings;


    /**
     * @var ArrayCollection<int, User>
     */
    #[OneToMany(targetEntity: User::class, mappedBy: 'alliance')]
    private Collection $members;

    /**
     * @var ArrayCollection<int, AllianceJob>
     */
    #[OneToMany(targetEntity: AllianceJob::class, mappedBy: 'alliance', indexBy: 'type')]
    private Collection $jobs;


    public function __construct()
    {
        $this->members = new ArrayCollection();
        $this->jobs = new ArrayCollection();
        $this->settings = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): Alliance
    {
        $this->name = $name;
        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): Alliance
    {
        $this->description = $description;
        return $this;
    }

    public function getHomepage(): string
    {
        return $this->homepage;
    }

    public function setHomepage(string $homepage): Alliance
    {
        $this->homepage = $homepage;
        return $this;
    }

    public function getDate(): int
    {
        return $this->date;
    }

    public function setDate(int $date): Alliance
    {
        $this->date = $date;
        return $this;
    }

    public function getFaction(): ?Faction
    {
        return $this->faction;
    }

    public function setFaction(?Faction $faction): Alliance
    {
        $this->faction = $faction;
        return $this;
    }

    public function getAcceptApplications(): bool
    {
        return $this->accept_applications;
    }

    public function setAcceptApplications(bool $acceptApplications): Alliance
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

    public function setAvatar(string $avatar): Alliance
    {
        $this->avatar = $avatar;
        return $this;
    }

    public function getRgbCode(): string
    {
        return $this->rgb_code;
    }

    public function setRgbCode(string $rgbCode): Alliance
    {
        $this->rgb_code = $rgbCode;
        return $this;
    }

    /**
     * @throws AllianceFounderNotSetException
     */
    public function getFounder(): AllianceJob
    {
        $job = $this->jobs->get(AllianceJobTypeEnum::FOUNDER->value);
        if ($job === null) {
            // alliance without founder? this should not happen
            throw new AllianceFounderNotSetException();
        }
        return $job;
    }

    public function getSuccessor(): ?AllianceJob
    {
        return $this->jobs->get(AllianceJobTypeEnum::SUCCESSOR->value);
    }

    public function getDiplomatic(): ?AllianceJob
    {
        return $this->jobs->get(AllianceJobTypeEnum::DIPLOMATIC->value);
    }

    /**
     * @return Collection<int, User>
     */
    public function getMembers(): Collection
    {
        return $this->members;
    }

    /**
     * Returns `true` if the founder is a npc
     */
    public function isNpcAlliance(): bool
    {
        $founder = $this->jobs->get(AllianceJobTypeEnum::FOUNDER->value);

        if ($founder === null) {
            return false;
        }

        return $founder->getUser()->isNpc();
    }

    /**
     * Returns the alliance jobs, indexed by type
     *
     * @return Collection<int, AllianceJob>
     */
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

    /**
     * @return Collection<int, AllianceSettings>
     */
    public function getSettings(): Collection
    {
        return $this->settings;
    }
}
