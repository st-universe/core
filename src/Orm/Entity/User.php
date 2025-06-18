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
use Override;
use Stu\Component\Game\GameEnum;
use Stu\Component\Game\ModuleEnum;
use Stu\Component\Map\MapEnum;
use Stu\Component\Player\UserAwardEnum;
use Stu\Component\Player\UserCssClassEnum;
use Stu\Component\Player\UserRpgBehaviorEnum;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Module\PlayerSetting\Lib\UserSettingEnum;
use Stu\Orm\Repository\UserRepository;

#[Table(name: 'stu_user')]
#[Index(name: 'user_alliance_idx', columns: ['allys_id'])]
#[Entity(repositoryClass: UserRepository::class)]
class User implements UserInterface
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'string')]
    private string $username = '';

    #[Column(type: 'string', length: 20)]
    private string $login = '';

    #[Column(type: 'string', length: 255)]
    private string $pass = '';

    #[Column(type: 'string', length: 6, nullable: true)]
    private ?string $sms_code = null;

    #[Column(type: 'string', length: 200)]
    private string $email = '';

    #[Column(type: 'string', length: 255, nullable: true)]
    private ?string $mobile = null;

    #[Column(type: 'integer', nullable: true)]
    private ?int $allys_id = null;

    #[Column(type: 'integer')]
    private int $race = 9;

    #[Column(type: 'smallint')]
    private int $state = UserEnum::USER_STATE_NEW;

    #[Column(type: 'integer')]
    private int $lastaction = 0;

    #[Column(type: 'integer')]
    private int $creation = 0;

    #[Column(type: 'integer')]
    private int $kn_lez = 0;

    #[Column(type: 'smallint')]
    private int $delmark = 0;

    #[Column(type: 'boolean')]
    private bool $vac_active = false;

    #[Column(type: 'integer')]
    private int $vac_request_date = 0;

    #[Column(type: 'text')]
    private string $description = '';

    #[Column(type: 'smallint')]
    private int $tick = 1;

    #[Column(type: 'smallint', nullable: true)]
    private ?int $maptype = MapEnum::MAPTYPE_INSERT;

    #[Column(type: 'text')]
    private string $sessiondata = '';

    #[Column(type: 'string', length: 255)]
    private string $password_token = '';

    #[Column(type: 'integer')]
    private int $prestige = 0;

    #[Column(type: 'boolean')]
    private bool $deals = false;

    #[Column(type: 'integer', nullable: true)]
    private ?int $last_boarding = null;

    #[ManyToOne(targetEntity: 'Alliance', inversedBy: 'members')]
    #[JoinColumn(name: 'allys_id', referencedColumnName: 'id')]
    private ?AllianceInterface $alliance = null;

    #[ManyToOne(targetEntity: 'Faction')]
    #[JoinColumn(name: 'race', referencedColumnName: 'id')]
    private FactionInterface $faction;

    /**
     * @var ArrayCollection<int, BuoyInterface>
     */
    #[OneToMany(targetEntity: 'Buoy', mappedBy: 'user')]
    private Collection $buoys;

    /**
     * @var ArrayCollection<int, UserAwardInterface>
     */
    #[OneToMany(targetEntity: 'UserAward', mappedBy: 'user', indexBy: 'award_id')]
    #[OrderBy(['award_id' => 'ASC'])]
    private Collection $awards;

    /**
     * @var ArrayCollection<int, ColonyInterface>
     */
    #[OneToMany(targetEntity: 'Colony', mappedBy: 'user', indexBy: 'id')]
    #[OrderBy(['colonies_classes_id' => 'ASC', 'id' => 'ASC'])]
    private Collection $colonies;

    /**
     * @var ArrayCollection<int, UserLayerInterface>
     */
    #[OneToMany(targetEntity: 'UserLayer', mappedBy: 'user', indexBy: 'layer_id')]
    private Collection $userLayers;

    #[OneToOne(targetEntity: 'UserLock', mappedBy: 'user')]
    private ?UserLockInterface $userLock = null;

    /**
     * @var ArrayCollection<string, UserSettingInterface>
     */
    #[OneToMany(targetEntity: 'UserSetting', mappedBy: 'user', indexBy: 'setting')]
    private Collection $settings;

    /**
     * @var ArrayCollection<int, UserCharacterInterface>
     */
    #[OneToMany(targetEntity: 'UserCharacter', mappedBy: 'user')]
    private Collection $characters;

    /**
     * @var ArrayCollection<int, ColonyScanInterface>
     */
    #[OneToMany(targetEntity: 'ColonyScan', mappedBy: 'user', indexBy: 'id', fetch: 'EXTRA_LAZY')]
    #[OrderBy(['colony_id' => 'ASC', 'date' => 'ASC'])]
    private Collection $colonyScans;

    #[OneToOne(targetEntity: 'PirateWrath', mappedBy: 'user')]
    private ?PirateWrathInterface $pirateWrath = null;

    /**
     * @var ArrayCollection<int, UserTutorialInterface>
     */
    #[OneToMany(targetEntity: 'UserTutorial', mappedBy: 'user', indexBy: 'tutorial_step_id', fetch: 'EXTRA_LAZY')]
    private Collection $tutorials;

    /** @var null|array<mixed> */
    private $sessiondataUnserialized;

    /**
     * @var ArrayCollection<int, WormholeRestriction>
     */
    #[OneToMany(targetEntity: 'WormholeRestriction', mappedBy: 'user')]
    private Collection $wormholeRestrictions;

    #[OneToOne(targetEntity: 'UserReferer', mappedBy: 'user')]
    private ?UserRefererInterface $referer = null;


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

    #[Override]
    public function getId(): int
    {
        return $this->id;
    }

    #[Override]
    public function getName(): string
    {
        //if UMODE active, add info to user name
        if ($this->isVacationRequestOldEnough()) {
            return $this->username . '[b][color=red] (UMODE)[/color][/b]';
        }
        return $this->username;
    }

    #[Override]
    public function setUsername(string $username): UserInterface
    {
        $this->username = $username;
        return $this;
    }

    #[Override]
    public function getLogin(): string
    {
        return $this->login;
    }

    #[Override]
    public function setLogin(string $login): UserInterface
    {
        $this->login = $login;
        return $this;
    }

    #[Override]
    public function getPassword(): string
    {
        return $this->pass;
    }

    #[Override]
    public function setPassword(string $password): UserInterface
    {
        $this->pass = $password;
        return $this;
    }

    #[Override]
    public function getSmsCode(): ?string
    {
        return $this->sms_code;
    }

    #[Override]
    public function setSmsCode(?string $code): UserInterface
    {
        $this->sms_code = $code;
        return $this;
    }

    #[Override]
    public function getEmail(): string
    {
        return $this->email;
    }

    #[Override]
    public function setEmail(string $email): UserInterface
    {
        $this->email = $email;
        return $this;
    }

    #[Override]
    public function getMobile(): ?string
    {
        return $this->mobile;
    }

    #[Override]
    public function setMobile(?string $mobile): UserInterface
    {
        $this->mobile = $mobile;
        return $this;
    }

    #[Override]
    public function getRgbCode(): string
    {
        $setting = $this->getSettings()->get(UserSettingEnum::RGB_CODE->value);
        if ($setting !== null) {
            return $setting->getValue();
        }

        return '';
    }

    #[Override]
    public function getCss(): string
    {
        $setting = $this->getSettings()->get(UserSettingEnum::CSS_COLOR_SHEET->value);
        if ($setting !== null) {
            return $setting->getValue();
        }

        return UserCssClassEnum::BLACK->value;
    }

    #[Override]
    public function getFactionId(): int
    {
        return $this->race;
    }

    #[Override]
    public function setFaction(FactionInterface $faction): UserInterface
    {
        $this->faction = $faction;
        return $this;
    }

    #[Override]
    public function getFaction(): FactionInterface
    {
        return $this->faction;
    }

    #[Override]
    public function getAwards(): Collection
    {
        return $this->awards;
    }

    #[Override]
    public function getColonies(): Collection
    {
        return $this->colonies;
    }

    #[Override]
    public function hasColony(): bool
    {
        if (
            $this->getState() === UserEnum::USER_STATE_COLONIZATION_SHIP
            || $this->getState() === UserEnum::USER_STATE_UNCOLONIZED
        ) {
            return false;
        }

        return !$this->getColonies()->isEmpty();
    }

    #[Override]
    public function getState(): int
    {
        return $this->state;
    }

    #[Override]
    public function isLocked(): bool
    {
        return $this->getUserLock() !== null && $this->getUserLock()->getRemainingTicks() > 0;
    }

    #[Override]
    public function getUserStateDescription(): string
    {
        if ($this->isLocked()) {
            return _('GESPERRT');
        }
        return UserEnum::getUserStateDescription($this->getState());
    }

    #[Override]
    public function setState(int $state): UserInterface
    {
        $this->state = $state;
        return $this;
    }

    #[Override]
    public function getAvatar(): string
    {
        $setting = $this->getSettings()->get(UserSettingEnum::AVATAR->value);
        if ($setting !== null) {
            return $setting->getValue();
        }

        return '';
    }

    #[Override]
    public function isEmailNotification(): bool
    {
        $setting = $this->getSettings()->get(UserSettingEnum::EMAIL_NOTIFICATION->value);
        if ($setting !== null) {
            return (bool)$setting->getValue();
        }

        return false;
    }

    #[Override]
    public function getLastaction(): int
    {
        return $this->lastaction;
    }

    #[Override]
    public function setLastaction(int $lastaction): UserInterface
    {
        $this->lastaction = $lastaction;
        return $this;
    }

    #[Override]
    public function getCreationDate(): int
    {
        return $this->creation;
    }

    #[Override]
    public function setCreationDate(int $creationDate): UserInterface
    {
        $this->creation = $creationDate;
        return $this;
    }

    #[Override]
    public function getKnMark(): int
    {
        return $this->kn_lez;
    }

    #[Override]
    public function setKnMark(int $knMark): UserInterface
    {
        $this->kn_lez = $knMark;
        return $this;
    }

    #[Override]
    public function getDeletionMark(): int
    {
        return $this->delmark;
    }

    #[Override]
    public function setDeletionMark(int $deletionMark): UserInterface
    {
        $this->delmark = $deletionMark;
        return $this;
    }

    #[Override]
    public function isVacationMode(): bool
    {
        return $this->vac_active;
    }

    #[Override]
    public function setVacationMode(bool $vacationMode): UserInterface
    {
        $this->vac_active = $vacationMode;
        return $this;
    }

    #[Override]
    public function getVacationRequestDate(): int
    {
        return $this->vac_request_date;
    }

    #[Override]
    public function setVacationRequestDate(int $date): UserInterface
    {
        $this->vac_request_date = $date;

        return $this;
    }

    #[Override]
    public function isVacationRequestOldEnough(): bool
    {
        return $this->isVacationMode() && (time() - $this->getVacationRequestDate() > UserEnum::VACATION_DELAY_IN_SECONDS);
    }

    #[Override]
    public function isStorageNotification(): bool
    {
        $setting = $this->getSettings()->get(UserSettingEnum::STORAGE_NOTIFICATION->value);
        if ($setting !== null) {
            return (bool)$setting->getValue();
        }

        return false;
    }

    #[Override]
    public function getDescription(): string
    {
        return $this->description;
    }

    #[Override]
    public function setDescription(string $description): UserInterface
    {
        $this->description = $description;
        return $this;
    }

    #[Override]
    public function isShowOnlineState(): bool
    {
        $setting = $this->getSettings()->get(UserSettingEnum::SHOW_ONLINE_STATUS->value);
        if ($setting !== null) {
            return (bool)$setting->getValue();
        }

        return false;
    }

    #[Override]
    public function isShowPmReadReceipt(): bool
    {
        $setting = $this->getSettings()->get(UserSettingEnum::SHOW_PM_READ_RECEIPT->value);
        if ($setting !== null) {
            return (bool)$setting->getValue();
        }

        return false;
    }

    #[Override]
    public function isSaveLogin(): bool
    {
        $setting = $this->getSettings()->get(UserSettingEnum::SAVE_LOGIN->value);
        if ($setting !== null) {
            return (bool)$setting->getValue();
        }

        return false;
    }

    #[Override]
    public function getFleetFixedDefault(): bool
    {
        $setting = $this->getSettings()->get(UserSettingEnum::FLEET_FIXED_DEFAULT->value);
        if ($setting !== null) {
            return (bool)$setting->getValue();
        }

        return false;
    }

    #[Override]
    public function getWarpsplitAutoCarryoverDefault(): bool
    {
        $setting = $this->getSettings()->get(UserSettingEnum::WARPSPLIT_AUTO_CARRYOVER_DEFAULT->value);
        if ($setting !== null) {
            return (bool)$setting->getValue();
        }

        return false;
    }

    #[Override]
    public function getTick(): int
    {
        return $this->tick;
    }

    #[Override]
    public function setTick(int $tick): UserInterface
    {
        $this->tick = $tick;
        return $this;
    }

    #[Override]
    public function getUserLayers(): Collection
    {
        return $this->userLayers;
    }

    #[Override]
    public function hasSeen(int $layerId): bool
    {
        return $this->getUserLayers()->containsKey($layerId);
    }

    #[Override]
    public function hasExplored(int $layerId): bool
    {
        return $this->hasSeen($layerId) && $this->getUserLayers()->get($layerId)->isExplored();
    }

    #[Override]
    public function getSettings(): Collection
    {
        return $this->settings;
    }

    #[Override]
    public function getSessiondata(): string
    {
        return $this->sessiondata;
    }

    #[Override]
    public function setSessiondata(string $sessiondata): UserInterface
    {
        $this->sessiondata = $sessiondata;
        $this->sessiondataUnserialized = null;
        return $this;
    }

    #[Override]
    public function getPasswordToken(): string
    {
        return $this->password_token;
    }

    #[Override]
    public function setPasswordToken(string $password_token): UserInterface
    {
        $this->password_token = $password_token;
        return $this;
    }

    #[Override]
    public function getPrestige(): int
    {
        return $this->prestige;
    }

    #[Override]
    public function setPrestige(int $prestige): UserInterface
    {
        $this->prestige = $prestige;
        return $this;
    }

    #[Override]
    public function getDefaultView(): ModuleEnum
    {
        $setting = $this->getSettings()->get(UserSettingEnum::DEFAULT_VIEW->value);
        if ($setting !== null) {
            return ModuleEnum::from($setting->getValue());
        }

        return ModuleEnum::MAINDESK;
    }

    #[Override]
    public function getRpgBehavior(): UserRpgBehaviorEnum
    {
        $setting = $this->getSettings()->get(UserSettingEnum::RPG_BEHAVIOR->value);
        if ($setting !== null) {
            return UserRpgBehaviorEnum::from((int)$setting->getValue());
        }

        return UserRpgBehaviorEnum::NOT_SET;
    }

    #[Override]
    public function getDeals(): bool
    {
        return $this->deals;
    }

    #[Override]
    public function setDeals(bool $deals): UserInterface
    {
        $this->deals = $deals;
        return $this;
    }

    #[Override]
    public function getLastBoarding(): ?int
    {
        return $this->last_boarding;
    }

    #[Override]
    public function setLastBoarding(int $lastBoarding): UserInterface
    {
        $this->last_boarding = $lastBoarding;
        return $this;
    }

    #[Override]
    public function isOnline(): bool
    {
        return !($this->getLastAction() < time() - GameEnum::USER_ONLINE_PERIOD);
    }

    #[Override]
    public function getAlliance(): ?AllianceInterface
    {
        return $this->alliance;
    }

    #[Override]
    public function setAlliance(?AllianceInterface $alliance): UserInterface
    {
        $this->alliance = $alliance;
        return $this;
    }

    #[Override]
    public function setAllianceId(?int $allianceId): UserInterface
    {
        $this->allys_id = $allianceId;
        return $this;
    }

    #[Override]
    public function getSessionDataUnserialized(): array
    {
        if ($this->sessiondataUnserialized === null) {
            $this->sessiondataUnserialized = unserialize($this->getSessionData());
            if (!is_array($this->sessiondataUnserialized)) {
                $this->sessiondataUnserialized = [];
            }
        }
        return $this->sessiondataUnserialized;
    }

    #[Override]
    public function isContactable(): bool
    {
        return $this->getId() != UserEnum::USER_NOONE;
    }

    #[Override]
    public function hasAward(int $awardId): bool
    {
        return $this->awards->containsKey($awardId) === true;
    }

    #[Override]
    public function hasStationsNavigation(): bool
    {
        if ($this->isNpc()) {
            return true;
        }

        return $this->hasAward(UserAwardEnum::RESEARCHED_STATIONS);
    }

    public static function isUserNpc(int $userId): bool
    {
        return $userId < UserEnum::USER_FIRST_ID;
    }

    #[Override]
    public function isNpc(): bool
    {
        return self::isUserNpc($this->getId());
    }

    #[Override]
    public function getUserLock(): ?UserLockInterface
    {
        return $this->userLock;
    }

    #[Override]
    public function __toString(): string
    {
        return sprintf('userName: %s', $this->getName());
    }

    #[Override]
    public function hasTranslation(): bool
    {
        $text = $this->getDescription();
        return strpos($text, '[translate]') !== false && strpos($text, '[/translate]') !== false;
    }

    #[Override]
    public function isShowPirateHistoryEntrys(): bool
    {
        $setting = $this->getSettings()->get(UserSettingEnum::SHOW_PIRATE_HISTORY_ENTRYS->value);
        if ($setting !== null) {
            return (bool)$setting->getValue();
        }

        return false;
    }

    #[Override]
    public function isInboxMessengerStyle(): bool
    {
        $setting = $this->getSettings()->get(UserSettingEnum::INBOX_MESSENGER_STYLE->value);
        if ($setting !== null) {
            return (bool)$setting->getValue();
        }

        return false;
    }

    #[Override]
    public function getCharacters(): Collection
    {
        return $this->characters;
    }

    #[Override]
    public function getColonyScans(): Collection
    {
        return $this->colonyScans;
    }

    /**
     * @return Collection<int, BuoyInterface>
     */
    public function getBuoys(): Collection
    {
        return $this->buoys;
    }

    #[Override]
    public function getPirateWrath(): ?PirateWrathInterface
    {
        return $this->pirateWrath;
    }

    #[Override]
    public function setPirateWrath(?PirateWrathInterface $wrath): UserInterface
    {
        $this->pirateWrath = $wrath;

        return $this;
    }

    #[Override]
    public function isProtectedAgainstPirates(): bool
    {
        $pirateWrath = $this->pirateWrath;
        if ($pirateWrath === null) {
            return false;
        }

        $timeout = $pirateWrath->getProtectionTimeout();
        if ($timeout === null) {
            return false;
        }

        return $timeout > time();
    }

    /**
     * @return Collection<int, UserTutorialInterface>
     */
    #[Override]
    public function getTutorials(): Collection
    {
        return $this->tutorials;
    }

    #[Override]
    /**
     * @return Collection<int, WormholeRestriction>
     */
    public function getWormholeRestrictions(): iterable
    {
        return $this->wormholeRestrictions;
    }

    #[Override]
    public function getReferer(): ?UserRefererInterface
    {
        return $this->referer;
    }

    #[Override]
    public function setReferer(?UserRefererInterface $referer): UserInterface
    {
        $this->referer = $referer;
        return $this;
    }
}
