<?php

namespace Stu\Orm\Entity;

use Doctrine\Common\Collections\Collection;
use Stu\Component\Game\ModuleEnum;
use Stu\Component\Player\UserRpgBehaviorEnum;

interface UserInterface
{
    public function getId(): int;

    public function getName(): string;

    public function setUsername(string $user): UserInterface;

    public function getLogin(): string;

    public function setLogin(string $login): UserInterface;

    public function getPassword(): string;

    public function setPassword(string $password): UserInterface;

    public function getSmsCode(): ?string;

    public function setSmsCode(?string $code): UserInterface;

    public function getEmail(): string;

    public function setEmail(string $email): UserInterface;

    public function getMobile(): ?string;

    public function setMobile(?string $mobile): UserInterface;

    public function getRgbCode(): string;

    public function getFactionId(): int;

    public function setFaction(FactionInterface $faction): UserInterface;

    public function getFaction(): FactionInterface;

    public function getCss(): string;

    /**
     * @return Collection<int, UserAwardInterface>
     */
    public function getAwards(): Collection;

    /**
     * @return Collection<int, ColonyInterface>
     */
    public function getColonies(): Collection;

    public function hasColony(): bool;

    public function getState(): int;

    public function isLocked(): bool;

    public function getUserStateDescription(): string;

    public function setState(int $state): UserInterface;

    public function getAvatar(): string;

    public function isEmailNotification(): bool;

    public function getLastaction(): int;

    public function setLastaction(int $lastaction): UserInterface;

    public function getCreationDate(): int;

    public function setCreationDate(int $creationDate): UserInterface;

    public function getKnMark(): int;

    public function setKnMark(int $knMark): UserInterface;

    public function getDeletionMark(): int;

    public function setDeletionMark(int $deletionMark): UserInterface;

    public function isVacationMode(): bool;

    public function setVacationMode(bool $vacationMode): UserInterface;

    public function getVacationRequestDate(): int;

    public function setVacationRequestDate(int $date): UserInterface;

    public function isVacationRequestOldEnough(): bool;

    public function isStorageNotification(): bool;

    public function getDescription(): string;

    public function setDescription(string $description): UserInterface;

    public function isShowOnlineState(): bool;

    public function isShowPmReadReceipt(): bool;

    public function getDeals(): bool;

    public function setDeals(bool $deals): UserInterface;

    public function getLastBoarding(): ?int;

    public function setLastBoarding(int $time): UserInterface;

    public function isSaveLogin(): bool;

    public function getFleetFixedDefault(): bool;

    public function getWarpsplitAutoCarryoverDefault(): bool;

    public function getTick(): int;

    public function setTick(int $tick): UserInterface;

    /**
     * @return Collection<int, UserLayerInterface>
     */
    public function getUserLayers(): Collection;

    public function hasSeen(int $layerId): bool;

    public function hasExplored(int $layerId): bool;

    /**
     * @return Collection<string, UserSettingInterface>
     */
    public function getSettings(): Collection;

    /**
     * @return Collection<int, UserCharacterInterface>
     */
    public function getCharacters(): Collection;

    /**
     * @return Collection<int, ColonyScanInterface>
     */
    public function getColonyScans(): Collection;

    public function getSessiondata(): string;

    public function setSessiondata(string $sessiondata): UserInterface;

    public function getPasswordToken(): string;

    public function setPasswordToken(string $password_token): UserInterface;

    public function getPrestige(): int;

    public function setPrestige(int $prestige): UserInterface;

    public function getDefaultView(): ModuleEnum;

    public function getRpgBehavior(): UserRpgBehaviorEnum;

    public function isOnline(): bool;

    public function getAlliance(): ?AllianceInterface;

    public function setAlliance(?AllianceInterface $alliance): UserInterface;

    public function setAllianceId(?int $allianceId): UserInterface;

    public function getSessionDataUnserialized(): array;

    public function isContactable(): bool;

    public function hasAward(int $awardId): bool;

    public function hasStationsNavigation(): bool;

    public function isNpc(): bool;

    public function getUserLock(): ?UserLockInterface;

    public function hasTranslation(): bool;

    public function isShowPirateHistoryEntrys(): bool;

    public function isInboxMessengerStyle(): bool;

    public function getPirateWrath(): ?PirateWrathInterface;

    public function setPirateWrath(?PirateWrathInterface $wrath): UserInterface;

    public function isProtectedAgainstPirates(): bool;

    /**
     * @return Collection<int, UserTutorialInterface>
     */
    public function getTutorials(): Collection;

    /**
     * @return iterable<WormholeRestriction>
     */
    public function getWormholeRestrictions(): iterable;

    public function getReferer(): ?UserRefererInterface;

    public function setReferer(?UserRefererInterface $referer): UserInterface;
}
