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
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\OrderBy;
use Doctrine\ORM\Mapping\Table;
use Noodlehaus\ConfigInterface;
use Stu\Component\Alliance\AllianceEnum;
use Stu\Component\Game\GameEnum;
use Stu\Component\Map\MapEnum;
use Stu\Component\Player\UserAwardEnum;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Orm\Repository\AllianceRelationRepositoryInterface;
use Stu\Orm\Repository\ContactRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\UserRepository")
 * @Table(
 *     name="stu_user",
 *     indexes={
 *     }
 * )
 **/
class User implements UserInterface
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
     * @Column(type="string")
     *
     * @var string
     */
    private $username = '';

    /**
     * @Column(type="string", length=20)
     *
     * @var string
     */
    private $login = '';

    /**
     * @Column(type="string", length=255)
     *
     * @var string
     */
    private $pass = '';

    /**
     * @Column(type="string", length=6, nullable=true)
     *
     * @var string|null
     */
    private $sms_code;

    /**
     * @Column(type="string", length=200)
     *
     * @var string
     */
    private $email = '';

    /**
     * @Column(type="string", length=255, nullable=true)
     *
     * @var string|null
     */
    private $mobile;

    /**
     * @Column(type="integer", nullable=true)
     *
     * @var int|null
     */
    private $allys_id;

    /**
     * @Column(type="integer", nullable=true)
     *
     * @var int|null
     */
    private $race;

    /**
     * @Column(type="smallint")
     *
     * @var int
     */
    private $state = UserEnum::USER_STATE_NEW;

    /**
     * @Column(type="string", length=200)
     *
     * @var string
     */
    private $propic = '';

    /**
     * @Column(type="boolean")
     *
     * @var bool
     */
    private $email_notification = true;

    /**
     * @Column(type="integer")
     *
     * @var int
     */
    private $lastaction = 0;

    /**
     * @Column(type="integer")
     *
     * @var int
     */
    private $creation = 0;

    /**
     * @Column(type="integer")
     *
     * @var int
     */
    private $kn_lez = 0;

    /**
     * @Column(type="smallint")
     *
     * @var int
     */
    private $delmark = 0;

    /**
     * @Column(type="boolean")
     *
     * @var bool
     */
    private $vac_active = false;

    /**
     * @Column(type="integer")
     *
     * @var int
     */
    private $vac_request_date = 0;

    /**
     * @Column(type="boolean")
     *
     * @var bool
     */
    private $storage_notification = true;

    /**
     * @Column(type="text")
     *
     * @var string
     */
    private $description = '';

    /**
     * @Column(type="boolean")
     *
     * @var bool
     */
    private $show_online_status = true;

    /**
     * @Column(type="boolean")
     *
     * @var bool
     */
    private $show_pm_read_receipt = true;

    /**
     * @Column(type="boolean")
     *
     * @var bool
     */
    private $save_login = true;

    /**
     * @Column(type="boolean")
     *
     * @var bool
     */
    private $fleet_fixed_default = false;

    /**
     * @Column(type="smallint")
     *
     * @var int
     */
    private $tick = 1;

    /**
     * @Column(type="smallint", nullable=true)
     *
     * @var int|null
     */
    private $maptype = MapEnum::MAPTYPE_INSERT;

    /**
     * @Column(type="text")
     *
     * @var string
     */
    private $sessiondata = '';

    /**
     * @Column(type="string", length=255)
     *
     * @var string
     */
    private $password_token = '';

    /**
     * @Column(type="string", length=7)
     *
     * @var string
     */
    private $rgb_code = '';

    /**
     * @Column(type="integer") *
     *
     * @var int
     */
    private $prestige = 0;

    /**
     * @Column(type="string", length=100, nullable=true)
     *
     * @var string|null
     */
    private $start_page;

    /**
     * @var null|AllianceInterface
     *
     * @ManyToOne(targetEntity="Alliance", inversedBy="members")
     * @JoinColumn(name="allys_id", referencedColumnName="id")
     */
    private $alliance;

    /**
     * @var FactionInterface
     *
     * @ManyToOne(targetEntity="Faction")
     * @JoinColumn(name="race", referencedColumnName="id")
     */
    private $faction;

    /**
     * @var ArrayCollection<int, UserAwardInterface>
     *
     * @OneToMany(targetEntity="UserAward", mappedBy="user", indexBy="award_id", cascade={"remove"})
     * @OrderBy({"award_id" = "ASC"})
     */
    private $awards;

    /**
     * @var ArrayCollection<int, ColonyInterface>
     *
     * @OneToMany(targetEntity="Colony", mappedBy="user")
     * @OrderBy({"colonies_classes_id" = "ASC", "id" = "ASC"})
     */
    private $colonies;

    /**
     * @var ArrayCollection<int, UserLayerInterface>
     *
     * @OneToMany(targetEntity="UserLayer", mappedBy="user", indexBy="layer_id", cascade={"remove"})
     */
    private $userLayers;

    /**
     * @var null|UserLockInterface
     *
     * @OneToOne(targetEntity="UserLock", mappedBy="user")
     */
    private $userLock;

    /** @var null|int */
    private $global_crew_limit;

    /** @var null|array<mixed> */
    private $sessiondataUnserialized;

    /** @var null|array<UserInterface> */
    private $friends;

    public function __construct()
    {
        $this->awards = new ArrayCollection();
        $this->colonies = new ArrayCollection();
        $this->userLayers = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUserName(): string
    {
        //if UMODE active, add info to user name
        if ($this->isVacationRequestOldEnough()) {
            return $this->username . '[b][color=red] (UMODE)[/color][/b]';
        }
        return $this->username;
    }

    public function setUsername(string $username): UserInterface
    {
        $this->username = $username;
        return $this;
    }

    public function getLogin(): string
    {
        return $this->login;
    }

    public function setLogin(string $login): UserInterface
    {
        $this->login = $login;
        return $this;
    }

    public function getPassword(): string
    {
        return $this->pass;
    }

    public function setPassword(string $password): UserInterface
    {
        $this->pass = $password;
        return $this;
    }

    public function getSmsCode(): ?string
    {
        return $this->sms_code;
    }

    public function setSmsCode(?string $code): UserInterface
    {
        $this->sms_code = $code;
        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): UserInterface
    {
        $this->email = $email;
        return $this;
    }

    public function getMobile(): ?string
    {
        return $this->mobile;
    }

    public function setMobile(?string $mobile): UserInterface
    {
        $this->mobile = $mobile;
        return $this;
    }

    public function getRgbCode(): string
    {
        return $this->rgb_code;
    }

    public function setRgbCode(string $rgbCode): UserInterface
    {
        $this->rgb_code = $rgbCode;
        return $this;
    }

    public function getAllianceId(): ?int
    {
        return $this->allys_id;
    }

    public function getFactionId(): ?int
    {
        return $this->race;
    }

    public function setFaction(FactionInterface $faction): UserInterface
    {
        $this->faction = $faction;
        return $this;
    }

    public function getFaction(): ?FactionInterface
    {
        return $this->faction;
    }

    public function getAwards(): Collection
    {
        return $this->awards;
    }

    public function getColonies(): Collection
    {
        return $this->colonies;
    }

    public function getState(): int
    {
        return $this->state;
    }

    public function isLocked(): bool
    {
        return $this->getUserLock() !== null && $this->getUserLock()->getRemainingTicks() > 0;
    }

    public function getUserStateDescription(): string
    {
        if ($this->isLocked()) {
            return _('GESPERRT');
        }
        return UserEnum::getUserStateDescription($this->getState());
    }

    public function setState(int $state): UserInterface
    {
        $this->state = $state;
        return $this;
    }

    public function getAvatar(): string
    {
        return $this->propic;
    }

    public function setAvatar(string $avatar): UserInterface
    {
        $this->propic = $avatar;
        return $this;
    }

    public function isEmailNotification(): bool
    {
        return $this->email_notification;
    }

    public function setEmailNotification(bool $email_notification): UserInterface
    {
        $this->email_notification = $email_notification;
        return $this;
    }

    public function getLastaction(): int
    {
        return $this->lastaction;
    }

    public function setLastaction(int $lastaction): UserInterface
    {
        $this->lastaction = $lastaction;
        return $this;
    }

    public function getCreationDate(): int
    {
        return $this->creation;
    }

    public function setCreationDate(int $creationDate): UserInterface
    {
        $this->creation = $creationDate;
        return $this;
    }

    public function getKnMark(): int
    {
        return $this->kn_lez;
    }

    public function setKnMark(int $knMark): UserInterface
    {
        $this->kn_lez = $knMark;
        return $this;
    }

    public function getDeletionMark(): int
    {
        return $this->delmark;
    }

    public function setDeletionMark(int $deletionMark): UserInterface
    {
        $this->delmark = $deletionMark;
        return $this;
    }

    public function isVacationMode(): bool
    {
        return $this->vac_active;
    }

    public function setVacationMode(bool $vacationMode): UserInterface
    {
        $this->vac_active = $vacationMode;
        return $this;
    }

    public function getVacationRequestDate(): int
    {
        return $this->vac_request_date;
    }

    public function setVacationRequestDate(int $date): UserInterface
    {
        $this->vac_request_date = $date;

        return $this;
    }

    public function isVacationRequestOldEnough(): bool
    {
        return $this->isVacationMode() && (time() - $this->getVacationRequestDate() > UserEnum::VACATION_DELAY_IN_SECONDS);
    }

    public function isStorageNotification(): bool
    {
        return $this->storage_notification;
    }

    public function setStorageNotification(bool $storage_notification): UserInterface
    {
        $this->storage_notification = $storage_notification;
        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): UserInterface
    {
        $this->description = $description;
        return $this;
    }

    public function isShowOnlineState(): bool
    {
        return $this->show_online_status;
    }

    public function setShowOnlineState(bool $showOnlineState): UserInterface
    {
        $this->show_online_status = $showOnlineState;
        return $this;
    }

    public function isShowPmReadReceipt(): bool
    {
        return $this->show_pm_read_receipt;
    }

    public function setShowPmReadReceipt(bool $showPmReadReceipt): UserInterface
    {
        $this->show_pm_read_receipt = $showPmReadReceipt;
        return $this;
    }

    public function isSaveLogin(): bool
    {
        return $this->save_login;
    }

    public function setSaveLogin(bool $save_login): UserInterface
    {
        $this->save_login = $save_login;
        return $this;
    }

    public function getFleetFixedDefault(): bool
    {
        return $this->fleet_fixed_default;
    }

    public function setFleetFixedDefault(bool $fleetFixedDefault): UserInterface
    {
        $this->fleet_fixed_default = $fleetFixedDefault;
        return $this;
    }

    public function getTick(): int
    {
        return $this->tick;
    }

    public function setTick(int $tick): UserInterface
    {
        $this->tick = $tick;
        return $this;
    }

    public function getUserLayers(): Collection
    {
        return $this->userLayers;
    }

    public function hasSeen(int $layerId): bool
    {
        return $this->getUserLayers()->containsKey($layerId);
    }

    public function hasExplored(int $layerId): bool
    {
        return $this->hasSeen($layerId) && $this->getUserLayers()->get($layerId)->isExplored();
    }

    public function getSessiondata(): string
    {
        return $this->sessiondata;
    }

    public function setSessiondata(string $sessiondata): UserInterface
    {
        $this->sessiondata = $sessiondata;
        $this->sessiondataUnserialized = null;
        return $this;
    }

    public function getPasswordToken(): string
    {
        return $this->password_token;
    }

    public function setPasswordToken(string $password_token): UserInterface
    {
        $this->password_token = $password_token;
        return $this;
    }

    public function getPrestige(): int
    {
        return $this->prestige;
    }

    public function setPrestige(int $prestige): UserInterface
    {
        $this->prestige = $prestige;
        return $this;
    }

    public function getStartPage(): ?string
    {
        return $this->start_page;
    }

    public function setStartPage(string $startPage): UserInterface
    {
        $this->start_page = $startPage;
        return $this;
    }

    /**
     * @deprecated
     */
    public function getName(): string
    {
        return $this->getUserName();
    }


    public function getFullAvatarPath(): string
    {
        if (!$this->getAvatar()) {
            return "/assets/rassen/" . $this->getFactionId() . "kn.png";
        }

        // @todo refactor
        global $container;

        $config = $container->get(ConfigInterface::class);

        return sprintf(
            '/%s/%s.png',
            $config->get('game.user_avatar_path'),
            $this->getAvatar()
        );
    }

    public function isOnline(): bool
    {
        if ($this->getLastAction() < time() - GameEnum::USER_ONLINE_PERIOD) {
            return false;
        }
        return true;
    }

    public function getFriends(): array
    {
        if ($this->friends === null) {
            // @todo refactor
            global $container;

            $this->friends = $container->get(UserRepositoryInterface::class)->getFriendsByUserAndAlliance(
                $this->getId(),
                (int) $this->getAllianceId()
            );
        }
        return $this->friends;
    }

    public function getAlliance(): ?AllianceInterface
    {
        return $this->alliance;
    }

    public function setAlliance(?AllianceInterface $alliance): UserInterface
    {
        $this->alliance = $alliance;
        return $this;
    }

    public function isFriend(int $userId): bool
    {
        // @todo refactor
        global $container;

        $user = $container->get(UserRepositoryInterface::class)->find($userId);
        if ($this->getAllianceId() > 0) {
            if ($this->getAllianceId() == $user->getAllianceId()) {
                return true;
            }

            $result = $container->get(AllianceRelationRepositoryInterface::class)->getActiveByTypeAndAlliancePair(
                [AllianceEnum::ALLIANCE_RELATION_FRIENDS, AllianceEnum::ALLIANCE_RELATION_ALLIED, AllianceEnum::ALLIANCE_RELATION_VASSAL],
                (int) $user->getAllianceId(),
                (int) $this->getAllianceId()
            );
            if ($result !== null) {
                return true;
            }
        }
        $contact = $container->get(ContactRepositoryInterface::class)->getByUserAndOpponent(
            $this->getId(),
            (int) $userId
        );

        return $contact !== null && $contact->isFriendly();
    }

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

    public function isContactable(): bool
    {
        return !in_array($this->getId(), [GameEnum::USER_NOONE]);
    }

    public function getGlobalCrewLimit(): int
    {
        if ($this->global_crew_limit === null) {
            $this->global_crew_limit = array_reduce(
                $this->getColonies()->toArray(),
                fn (int $sum, ColonyInterface $colony): int => $colony->getCrewLimit() + $sum,
                0
            );
        }
        return $this->global_crew_limit;
    }

    public function hasAward(int $awardId): bool
    {
        return $this->awards->containsKey($awardId) === true;
    }

    public function hasStationsNavigation(): bool
    {
        if ($this->isNpc()) {
            return true;
        }

        return $this->hasAward(UserAwardEnum::RESEARCHED_STATIONS);
    }

    public function isNpc(): bool
    {
        return $this->getId() < 100;
    }

    public function isAdmin(): bool
    {
        // @todo refactor
        global $container;
        return in_array($this->getId(),  $container->get(ConfigInterface::class)->get('game.admins'));
    }

    public function getUserLock(): ?UserLockInterface
    {
        return $this->userLock;
    }

    public function __toString()
    {
        return sprintf('userName: %s', $this->getName());
    }
}
