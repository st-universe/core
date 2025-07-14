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
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\OrderBy;
use Doctrine\ORM\Mapping\Table;
use LogicException;
use Stu\Component\Faction\FactionEnum;
use Stu\Component\Game\GameEnum;
use Stu\Module\PlayerSetting\Lib\UserConstants;
use Stu\Module\PlayerSetting\Lib\UserStateEnum;
use Stu\Orm\Repository\UserRepository;

#[Table(name: 'stu_user')]
#[Index(name: 'user_alliance_idx', columns: ['allys_id'])]
#[Entity(repositoryClass: UserRepository::class)]
class User
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'string')]
    private string $username = '';

    #[Column(type: 'integer', nullable: true)]
    private ?int $allys_id = null;

    #[Column(type: 'integer', enumType: FactionEnum::class)]
    private FactionEnum $faction_id = FactionEnum::FACTION_FEDERATION;

    #[Column(type: 'smallint', enumType: UserStateEnum::class)]
    private UserStateEnum $state = UserStateEnum::NEW;

    #[Column(type: 'integer')]
    private int $lastaction = 0;

    #[Column(type: 'integer')]
    private int $kn_lez = 0;

    #[Column(type: 'boolean')]
    private bool $vac_active = false;

    #[Column(type: 'integer')]
    private int $vac_request_date = 0;

    #[Column(type: 'text')]
    private string $description = '';

    #[Column(type: 'text')]
    private string $sessiondata = '';

    #[Column(type: 'integer')]
    private int $prestige = 0;

    #[Column(type: 'boolean')]
    private bool $deals = false;

    #[Column(type: 'integer', nullable: true)]
    private ?int $last_boarding = null;

    #[OneToOne(targetEntity: UserRegistration::class, mappedBy: 'user', cascade: ['all'])]
    private ?UserRegistration $registration;

    #[ManyToOne(targetEntity: Alliance::class, inversedBy: 'members')]
    #[JoinColumn(name: 'allys_id', referencedColumnName: 'id')]
    private ?Alliance $alliance = null;

    #[ManyToOne(targetEntity: Faction::class)]
    #[JoinColumn(name: 'faction_id', nullable: false, referencedColumnName: 'id')]
    private Faction $faction;

    /**
     * @var ArrayCollection<int, Buoy>
     */
    #[OneToMany(targetEntity: Buoy::class, mappedBy: 'user')]
    private Collection $buoys;

    /**
     * @var ArrayCollection<int, UserAward>
     */
    #[OneToMany(targetEntity: UserAward::class, mappedBy: 'user', indexBy: 'award_id')]
    #[OrderBy(['award_id' => 'ASC'])]
    private Collection $awards;

    /**
     * @var ArrayCollection<int, Colony>
     */
    #[OneToMany(targetEntity: Colony::class, mappedBy: 'user', indexBy: 'id')]
    #[OrderBy(['colonies_classes_id' => 'ASC', 'id' => 'ASC'])]
    private Collection $colonies;

    /**
     * @var ArrayCollection<int, UserLayer>
     */
    #[OneToMany(targetEntity: UserLayer::class, mappedBy: 'user', indexBy: 'layer_id')]
    private Collection $userLayers;

    #[OneToOne(targetEntity: UserLock::class, mappedBy: 'user')]
    private ?UserLock $userLock = null;

    /**
     * @var ArrayCollection<string, UserSetting>
     */
    #[OneToMany(targetEntity: UserSetting::class, mappedBy: 'user', indexBy: 'setting')]
    private Collection $settings;

    /**
     * @var ArrayCollection<int, UserCharacter>
     */
    #[OneToMany(targetEntity: UserCharacter::class, mappedBy: 'user', fetch: 'EXTRA_LAZY')]
    private Collection $characters;

    /**
     * @var ArrayCollection<int, ColonyScan>
     */
    #[OneToMany(targetEntity: ColonyScan::class, mappedBy: 'user', indexBy: 'id', fetch: 'EXTRA_LAZY')]
    #[OrderBy(['colony_id' => 'ASC', 'date' => 'ASC'])]
    private Collection $colonyScans;

    #[OneToOne(targetEntity: PirateWrath::class, mappedBy: 'user')]
    private ?PirateWrath $pirateWrath = null;

    /**
     * @var ArrayCollection<int, UserTutorial>
     */
    #[OneToMany(targetEntity: UserTutorial::class, mappedBy: 'user', indexBy: 'tutorial_step_id', fetch: 'EXTRA_LAZY')]
    private Collection $tutorials;

    /**
     * @var ArrayCollection<int, WormholeRestriction>
     */
    #[OneToMany(targetEntity: WormholeRestriction::class, mappedBy: 'user', fetch: 'EXTRA_LAZY')]
    private Collection $wormholeRestrictions;

    public function __construct()
    {
        $this->awards = new ArrayCollection();
        $this->colonies = new ArrayCollection();
        $this->userLayers = new ArrayCollection();
        $this->settings = new ArrayCollection();
        $this->characters = new ArrayCollection();
        $this->buoys = new ArrayCollection();
        $this->colonyScans = new ArrayCollection();
        $this->tutorials = new ArrayCollection();
        $this->wormholeRestrictions = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getRegistration(): UserRegistration
    {
        return $this->registration ?? throw new LogicException('User has no registration');
    }

    public function setRegistration(UserRegistration $registration): User
    {
        $this->registration = $registration;

        return $this;
    }

    public function getName(): string
    {
        //if UMODE active, add info to user name
        if ($this->isVacationRequestOldEnough()) {
            return $this->username . '[b][color=red] (UMODE)[/color][/b]';
        }
        return $this->username;
    }

    public function setUsername(string $username): User
    {
        $this->username = $username;
        return $this;
    }

    public function getFactionId(): int
    {
        return $this->faction->getId();
    }

    public function setFaction(Faction $faction): User
    {
        $this->faction = $faction;
        return $this;
    }

    public function getFaction(): Faction
    {
        return $this->faction;
    }

    /**
     * @return Collection<int, UserAward>
     */
    public function getAwards(): Collection
    {
        return $this->awards;
    }

    /**
     * @return Collection<int, Colony>
     */
    public function getColonies(): Collection
    {
        return $this->colonies;
    }

    public function hasColony(): bool
    {
        return $this->getState() === UserStateEnum::ACTIVE && !$this->getColonies()->isEmpty();
    }

    public function getState(): UserStateEnum
    {
        return $this->state;
    }

    public function isLocked(): bool
    {
        return $this->getUserLock() !== null && $this->getUserLock()->getRemainingTicks() > 0;
    }

    public function setState(UserStateEnum $state): User
    {
        $this->state = $state;
        return $this;
    }

    public function getLastaction(): int
    {
        return $this->lastaction;
    }

    public function setLastaction(int $lastaction): User
    {
        $this->lastaction = $lastaction;
        return $this;
    }

    public function getKnMark(): int
    {
        return $this->kn_lez;
    }

    public function setKnMark(int $knMark): User
    {
        $this->kn_lez = $knMark;
        return $this;
    }

    public function isVacationMode(): bool
    {
        return $this->vac_active;
    }

    public function setVacationMode(bool $vacationMode): User
    {
        $this->vac_active = $vacationMode;
        return $this;
    }

    public function getVacationRequestDate(): int
    {
        return $this->vac_request_date;
    }

    public function setVacationRequestDate(int $date): User
    {
        $this->vac_request_date = $date;

        return $this;
    }

    public function isVacationRequestOldEnough(): bool
    {
        return $this->isVacationMode() && (time() - $this->getVacationRequestDate() > UserConstants::VACATION_DELAY_IN_SECONDS);
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): User
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return Collection<int, UserLayer>
     */
    public function getUserLayers(): Collection
    {
        return $this->userLayers;
    }

    /**
     * @return Collection<string, UserSetting>
     */
    public function getSettings(): Collection
    {
        return $this->settings;
    }

    public function getSessiondata(): string
    {
        return $this->sessiondata;
    }

    public function setSessiondata(string $sessiondata): User
    {
        $this->sessiondata = $sessiondata;
        return $this;
    }

    public function getPrestige(): int
    {
        return $this->prestige;
    }

    public function setPrestige(int $prestige): User
    {
        $this->prestige = $prestige;
        return $this;
    }

    public function getDeals(): bool
    {
        return $this->deals;
    }

    public function setDeals(bool $deals): User
    {
        $this->deals = $deals;
        return $this;
    }

    public function getLastBoarding(): ?int
    {
        return $this->last_boarding;
    }

    public function setLastBoarding(int $lastBoarding): User
    {
        $this->last_boarding = $lastBoarding;
        return $this;
    }

    public function isOnline(): bool
    {
        return !($this->getLastAction() < time() - GameEnum::USER_ONLINE_PERIOD);
    }

    public function getAlliance(): ?Alliance
    {
        return $this->alliance;
    }

    public function setAlliance(?Alliance $alliance): User
    {
        $this->alliance = $alliance;
        return $this;
    }

    public function isContactable(): bool
    {
        return $this->getId() != UserConstants::USER_NOONE;
    }

    public function hasAward(int $awardId): bool
    {
        return $this->awards->containsKey($awardId) === true;
    }

    public static function isUserNpc(int $userId): bool
    {
        return $userId < UserConstants::USER_FIRST_ID;
    }

    public function isNpc(): bool
    {
        return self::isUserNpc($this->getId());
    }

    public function getUserLock(): ?UserLock
    {
        return $this->userLock;
    }

    public function __toString(): string
    {
        return sprintf('userName: %s', $this->getName());
    }

    /**
     * @return Collection<int, UserCharacter>
     */
    public function getCharacters(): Collection
    {
        return $this->characters;
    }

    /**
     * @return Collection<int, ColonyScan>
     */
    public function getColonyScans(): Collection
    {
        return $this->colonyScans;
    }

    /**
     * @return Collection<int, Buoy>
     */
    public function getBuoys(): Collection
    {
        return $this->buoys;
    }

    public function getPirateWrath(): ?PirateWrath
    {
        return $this->pirateWrath;
    }

    public function setPirateWrath(?PirateWrath $wrath): User
    {
        $this->pirateWrath = $wrath;

        return $this;
    }

    /**
     * @return Collection<int, UserTutorial>
     */
    public function getTutorials(): Collection
    {
        return $this->tutorials;
    }

    /**
     * @return Collection<int, WormholeRestriction>
     */
    public function getWormholeRestrictions(): Collection
    {
        return $this->wormholeRestrictions;
    }
}
