<?php

namespace Stu\Orm\Entity;

use Doctrine\Common\Collections\Collection;

interface UserInterface
{
    public function getId(): int;

    public function getUserName(): string;

    public function setUsername(string $user): UserInterface;

    public function getLogin(): string;

    public function setLogin(string $login): UserInterface;

    public function getPassword(): string;

    public function setPassword(string $password): UserInterface;

    public function getEmail(): string;

    public function setEmail(string $email): UserInterface;

    public function getAllianceId(): ?int;

    public function getFactionId(): ?int;

    public function setFaction(FactionInterface $faction): UserInterface;

    public function getFaction(): ?FactionInterface;

    public function getAwards(): Collection;

    public function getColonies(): Collection;

    public function getActive(): int;

    public function setActive(int $active): UserInterface;

    public function getAvatar(): string;

    public function setAvatar(string $avatar): UserInterface;

    public function isEmailNotification(): bool;

    public function setEmailNotification(bool $email_notification): UserInterface;

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

    public function setStorageNotification(bool $storage_notification): UserInterface;

    public function getDescription(): string;

    public function setDescription(string $description): UserInterface;

    public function isShowOnlineState(): bool;

    public function setShowOnlineState(bool $showOnlineState): UserInterface;

    public function isSaveLogin(): bool;

    public function setSaveLogin(bool $save_login): UserInterface;

    public function getFleetFixedDefault(): bool;

    public function setFleetFixedDefault(bool $fleetFixedDefault): UserInterface;

    public function getTick(): int;

    public function setTick(int $tick): UserInterface;

    public function getMaptype(): int;

    public function setMaptype(int $maptype): UserInterface;

    public function getSessiondata(): string;

    public function setSessiondata(string $sessiondata): UserInterface;

    public function getPasswordToken(): string;

    public function setPasswordToken(string $password_token): UserInterface;

    /**
     * @deprecated
     */
    public function getName(): string;

    public function getFullAvatarPath(): string;

    public function isOnline(): bool;

    public function getFriends(): array;

    public function getAlliance(): ?AllianceInterface;

    public function setAlliance(?AllianceInterface $alliance): UserInterface;

    public function isFriend($userId): bool;

    public function getSessionDataUnserialized(): array;

    public function isContactable(): bool;

    public function getFreeCrewCount(): int;

    public function lowerFreeCrewCount(int $amount): void;

    public function getCrewCountDebris(): int;

    public function getTrainableCrewCountMax(): int;

    public function getGlobalCrewLimit(): int;

    public function getUsedCrewCount(): int;

    public function getCrewLeftCount(): int;

    public function getInTrainingCrewCount(): int;

    public function hasStationsNavigation(): bool;

    public function maySignup(int $allianceId): bool;

    public function isNpc(): bool;

    public function isAdmin(): bool;
}
