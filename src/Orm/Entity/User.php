<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Noodlehaus\ConfigInterface;
use Stu\Component\Alliance\AllianceEnum;
use Stu\Component\Game\GameEnum;
use Stu\Component\Player\UserAwardEnum;
use Stu\Component\Ship\ShipRumpEnum;
use Stu\Orm\Repository\AllianceJobRepositoryInterface;
use Stu\Orm\Repository\AllianceRelationRepositoryInterface;
use Stu\Orm\Repository\AllianceRepositoryInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\ContactRepositoryInterface;
use Stu\Orm\Repository\CrewRepositoryInterface;
use Stu\Orm\Repository\CrewTrainingRepositoryInterface;
use Stu\Orm\Repository\ShipCrewRepositoryInterface;
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
     */
    private $id;

    /** @Column(type="string") */
    private $username = '';

    /** @Column(type="string", length=20) */
    private $login = '';

    /** @Column(type="string", length=255) */
    private $pass = '';

    /** @Column(type="string", length=200) */
    private $email = '';

    /** @Column(type="integer", nullable=true) */
    private $allys_id;

    /** @Column(type="integer", nullable=true) */
    private $race;

    /** @Column(type="smallint") */
    private $aktiv = 0;

    /** @Column(type="string", length=200) */
    private $propic = '';

    /** @Column(type="boolean") */
    private $email_notification = true;

    /** @Column(type="integer") */
    private $lastaction = 0;

    /** @Column(type="integer") */
    private $creation = 0;

    /** @Column(type="integer") */
    private $kn_lez = 0;

    /** @Column(type="smallint") */
    private $delmark = 0;

    /** @Column(type="boolean") */
    private $vac_active = false;

    /** @Column(type="integer") * */
    private $vac_request_date = 0;

    /** @Column(type="boolean") */
    private $storage_notification = true;

    /** @Column(type="text") */
    private $description = '';

    /** @Column(type="boolean") */
    private $show_online_status = true;

    /** @Column(type="boolean") */
    private $save_login = true;

    /** @Column(type="boolean") */
    private $fleet_fixed_default = false;

    /** @Column(type="smallint") */
    private $tick = 1;

    /** @Column(type="smallint") */
    private $maptype = 1;

    /** @Column(type="text") */
    private $sessiondata = '';

    /** @Column(type="string", length=100) */
    private $password_token = '';

    /** @Column(type="string", length=7, nullable=true) */
    private $rgb_code;

    /**
     * @ManyToOne(targetEntity="Alliance", inversedBy="members")
     * @JoinColumn(name="allys_id", referencedColumnName="id")
     */
    private $alliance;

    /**
     * @ManyToOne(targetEntity="Faction")
     * @JoinColumn(name="race", referencedColumnName="id")
     */
    private $faction;

    /**
     * @OneToMany(targetEntity="UserAward", mappedBy="user", indexBy="type", cascade={"remove"}, fetch="EAGER")
     */
    private $awards;

    /**
     * @OneToMany(targetEntity="Colony", mappedBy="user", fetch="EAGER")
     */
    private $colonies;

    private $used_crew_count;

    private $crew_in_training;

    private $global_crew_limit;

    private $crew_count_debris;

    private $free_crew_count;

    private $sessiondataUnserialized;

    private $friends;

    public function __construct()
    {
        $this->awards = new ArrayCollection();
        $this->colonies = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUserName(): string
    {
        //wenn UMODE aktiv, eine Info an den Namen anhängen
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

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): UserInterface
    {
        $this->email = $email;
        return $this;
    }

    public function getRgbCode(): ?string
    {
        return $this->rgb_code;
    }

    public function setRgbCode(?string $rgbCode): UserInterface
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

    public function getActive(): int
    {
        return $this->aktiv;
    }

    public function setActive(int $active): UserInterface
    {
        $this->aktiv = $active;
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
        //172800 = 48 hours in seconds
        return $this->isVacationMode() && (time() - $this->getVacationRequestDate() > 172800);
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

    public function getMaptype(): int
    {
        return $this->maptype;
    }

    public function setMaptype(int $maptype): UserInterface
    {
        $this->maptype = $maptype;
        return $this;
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

    public function isFriend($userId): bool
    {
        // @todo refactor
        global $container;

        $user = $container->get(UserRepositoryInterface::class)->find($userId);
        if ($this->getAllianceId() > 0) {
            if ($this->getAllianceId() == $user->getAllianceId()) {
                return true;
            }

            $result = $container->get(AllianceRelationRepositoryInterface::class)->getActiveByTypeAndAlliancePair(
                [AllianceEnum::ALLIANCE_RELATION_FRIENDS, AllianceEnum::ALLIANCE_RELATION_ALLIED],
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

    public function getFreeCrewCount(): int
    {
        if ($this->free_crew_count === null) {
            // @todo refactor
            global $container;

            $this->free_crew_count = $container->get(CrewRepositoryInterface::class)->getFreeAmountByUser((int) $this->getId());
        }
        return $this->free_crew_count;
    }

    public function lowerFreeCrewCount(int $amount): void
    {
        $this->free_crew_count -= $amount;
    }

    public function getCrewCountDebris(): int
    {
        if ($this->crew_count_debris === null) {
            // @todo refactor
            global $container;

            $this->crew_count_debris = $container->get(CrewRepositoryInterface::class)
                ->getAmountByUserAndShipRumpCategory(
                    (int) $this->getId(),
                    ShipRumpEnum::SHIP_CATEGORY_DEBRISFIELD
                );

            $this->crew_count_debris += $container->get(CrewRepositoryInterface::class)
                ->getAmountByUserAndShipRumpCategory(
                    (int) $this->getId(),
                    ShipRumpEnum::SHIP_CATEGORY_ESCAPE_PODS
                );
        }
        return $this->crew_count_debris;
    }

    public function getTrainableCrewCountMax(): int
    {
        return (int) ceil($this->getGlobalCrewLimit() / 10);
    }

    public function getGlobalCrewLimit(): int
    {
        if ($this->global_crew_limit === null) {
            // @todo refactor
            global $container;

            $colonyRepository = $container->get(ColonyRepositoryInterface::class);

            $this->global_crew_limit = (int) array_reduce(
                $colonyRepository->getOrderedListByUser($this),
                function (int $sum, ColonyInterface $colony): int {
                    return $colony->getCrewLimit() + $sum;
                },
                0
            );
        }
        return $this->global_crew_limit;
    }

    public function getUsedCrewCount(): int
    {
        if ($this->used_crew_count === null) {
            // @todo refactor
            global $container;

            $this->used_crew_count = $container->get(ShipCrewRepositoryInterface::class)->getAmountByUser((int) $this->getId());
        }
        return $this->used_crew_count;
    }

    public function getCrewLeftCount(): int
    {
        return max(
            0,
            $this->getGlobalCrewLimit() - $this->getUsedCrewCount() - $this->getFreeCrewCount() - $this->getInTrainingCrewCount()
        );
    }

    public function getInTrainingCrewCount(): int
    {
        if ($this->crew_in_training === null) {
            // @todo refactor
            global $container;

            $this->crew_in_training = $container->get(CrewTrainingRepositoryInterface::class)->getCountByUser((int) $this->getId());
        }
        return $this->crew_in_training;
    }

    public function hasStationsNavigation(): bool
    {
        if ($this->isNpc()) {
            return true;
        }

        return $this->getAwards()->containsKey(UserAwardEnum::RESEARCHED_STATIONS);
    }

    public function maySignup(int $allianceId): bool
    {
        // @todo refactor
        global $container;

        $pendingApplication = $container->get(AllianceJobRepositoryInterface::class)->getByUserAndAllianceAndType(
            $this->getId(),
            $allianceId,
            AllianceEnum::ALLIANCE_JOBS_PENDING
        );
        if ($pendingApplication !== null) {
            return false;
        }

        $alliance = $container->get(AllianceRepositoryInterface::class)->find($allianceId);

        return $alliance->getAcceptApplications() && $this->getAlliance() === null && ($alliance->getFactionId() == 0 || $this->getFactionId() == $alliance->getFactionId());
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
}
