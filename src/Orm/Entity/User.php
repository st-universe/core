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
use Stu\Component\Game\GameEnum;
use Stu\Component\Map\MapEnum;
use Stu\Component\Player\UserAwardEnum;
use Stu\Component\Player\UserRpgEnum;
use Stu\Module\PlayerSetting\Lib\UserEnum;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\UserRepository")
 * @Table(
 *     name="stu_user",
 *     indexes={
 *         @Index(name="user_alliance_idx", columns={"allys_id"})
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
     */
    private int $id;

    /**
     * @Column(type="string")
     *
     */
    private string $username = '';

    /**
     * @Column(type="string", length=20)
     *
     */
    private string $login = '';

    /**
     * @Column(type="string", length=255)
     *
     */
    private string $pass = '';

    /**
     * @Column(type="string", length=6, nullable=true)
     *
     */
    private ?string $sms_code = null;

    /**
     * @Column(type="string", length=200)
     *
     */
    private string $email = '';

    /**
     * @Column(type="string", length=255, nullable=true)
     *
     */
    private ?string $mobile = null;

    /**
     * @Column(type="integer", nullable=true)
     *
     */
    private ?int $allys_id = null;

    /**
     * @Column(type="integer", nullable=true)
     *
     */
    private ?int $race = null;

    /**
     * @Column(type="smallint")
     *
     */
    private int $state = UserEnum::USER_STATE_NEW;

    /**
     * @Column(type="string", length=200)
     *
     */
    private string $propic = '';

    /**
     * @Column(type="boolean")
     *
     */
    private bool $email_notification = true;

    /**
     * @Column(type="integer")
     *
     */
    private int $lastaction = 0;

    /**
     * @Column(type="integer")
     *
     */
    private int $creation = 0;

    /**
     * @Column(type="integer")
     *
     */
    private int $kn_lez = 0;

    /**
     * @Column(type="smallint")
     *
     */
    private int $delmark = 0;

    /**
     * @Column(type="boolean")
     *
     */
    private bool $vac_active = false;

    /**
     * @Column(type="integer")
     *
     */
    private int $vac_request_date = 0;

    /**
     * @Column(type="boolean")
     *
     */
    private bool $storage_notification = true;

    /**
     * @Column(type="text")
     *
     */
    private string $description = '';

    /**
     * @Column(type="boolean")
     *
     */
    private bool $show_online_status = true;

    /**
     * @Column(type="boolean")
     *
     */
    private bool $show_pm_read_receipt = true;

    /**
     * @Column(type="boolean")
     *
     */
    private bool $save_login = true;

    /**
     * @Column(type="boolean")
     *
     */
    private bool $fleet_fixed_default = false;

    /**
     * @Column(type="smallint")
     *
     */
    private int $tick = 1;

    /**
     * @Column(type="smallint", nullable=true)
     *
     */
    private ?int $maptype = MapEnum::MAPTYPE_INSERT;

    /**
     * @Column(type="text")
     *
     */
    private string $sessiondata = '';

    /**
     * @Column(type="string", length=255)
     *
     */
    private string $password_token = '';

    /**
     * @Column(type="string", length=7)
     *
     */
    private string $rgb_code = '';

    /**
     * @Column(type="integer")
     *
     */
    private int $prestige = 0;

    /**
     * @Column(type="string", length=100, nullable=true)
     *
     */
    private ?string $start_page = null;

    /**
     * @Column(type="integer", options={"default": 0})
     *
     */
    private int $rpg_behavior = UserRpgEnum::RPG_BEHAVIOR_NOT_SET;

    /**
     * @Column(type="string", length=100)
     *
     */
    private string $css = 'schwarz';

    /**
     * @Column(type="boolean", options={"default": false})
     *
     */
    private bool $deals = false;

    /**
     *
     * @ManyToOne(targetEntity="Alliance", inversedBy="members")
     * @JoinColumn(name="allys_id", referencedColumnName="id")
     */
    private ?AllianceInterface $alliance = null;

    /**
     *
     * @ManyToOne(targetEntity="Faction")
     * @JoinColumn(name="race", referencedColumnName="id")
     */
    private ?FactionInterface $faction = null;

    /**
     * @var ArrayCollection<int, UserAwardInterface>
     *
     * @OneToMany(targetEntity="UserAward", mappedBy="user", indexBy="award_id")
     * @OrderBy({"award_id": "ASC"})
     */
    private Collection $awards;

    /**
     * @var ArrayCollection<int, ColonyInterface>
     *
     * @OneToMany(targetEntity="Colony", mappedBy="user")
     * @OrderBy({"colonies_classes_id": "ASC", "id": "ASC"})
     */
    private Collection $colonies;

    /**
     * @var ArrayCollection<int, UserLayerInterface>
     *
     * @OneToMany(targetEntity="UserLayer", mappedBy="user", indexBy="layer_id")
     */
    private Collection $userLayers;

    /**
     * @OneToOne(targetEntity="UserLock", mappedBy="user")
     */
    private ?UserLockInterface $userLock = null;

    /** @var null|array<mixed> */
    private $sessiondataUnserialized;

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

    public function getName(): string
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

    public function getCss(): string
    {
        return $this->css;
    }

    public function setCss(string $Css): UserInterface
    {
        $this->css = $Css;
        return $this;
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

    public function setStartPage(?string $startPage): UserInterface
    {
        $this->start_page = $startPage;
        return $this;
    }

    public function getRpgBehavior(): int
    {
        return $this->rpg_behavior;
    }

    public function setRpgBehavior(int $RPGbehavior): UserInterface
    {
        $this->rpg_behavior = $RPGbehavior;
        return $this;
    }

    public function getRpgBehaviorText(): string
    {
        return UserRpgEnum::getRpgBehaviorText($this->getRpgBehavior());
    }

    public function getDeals(): bool
    {
        return $this->deals;
    }

    public function setDeals(bool $deals): UserInterface
    {
        $this->deals = $deals;
        return $this;
    }

    public function isOnline(): bool
    {
        return !($this->getLastAction() < time() - GameEnum::USER_ONLINE_PERIOD);
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

    public function setAllianceId(?int $allianceId): UserInterface
    {
        $this->allys_id = $allianceId;
        return $this;
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
        return $this->getId() != UserEnum::USER_NOONE;
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
        return $this->getId() < UserEnum::USER_FIRST_ID;
    }

    public function getUserLock(): ?UserLockInterface
    {
        return $this->userLock;
    }

    public function __toString(): string
    {
        return sprintf('userName: %s', $this->getName());
    }

    public function hasTranslation(): bool
    {
        $text = $this->getDescription();
        return strpos($text, '[translate]') !== false && strpos($text, '[/translate]') !== false;
    }
}
