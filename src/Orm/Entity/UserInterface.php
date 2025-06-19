<?php

namespace Stu\Orm\Entity;

use Doctrine\Common\Collections\Collection;

interface UserInterface
{
    public function getId(): int;

    public function getRegistration(): UserRegistrationInterface;

    public function setRegistration(UserRegistrationInterface $registration): UserInterface;

    public function getName(): string;

    public function setUsername(string $user): UserInterface;

    public function getFactionId(): int;

    public function setFaction(FactionInterface $faction): UserInterface;

    public function getFaction(): FactionInterface;

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

    public function setState(int $state): UserInterface;

    public function getLastaction(): int;

    public function setLastaction(int $lastaction): UserInterface;

    public function getKnMark(): int;

    public function setKnMark(int $knMark): UserInterface;

    public function isVacationMode(): bool;

    public function setVacationMode(bool $vacationMode): UserInterface;

    public function getVacationRequestDate(): int;

    public function setVacationRequestDate(int $date): UserInterface;

    public function isVacationRequestOldEnough(): bool;

    public function getDescription(): string;

    public function setDescription(string $description): UserInterface;

    public function getDeals(): bool;

    public function setDeals(bool $deals): UserInterface;

    public function getLastBoarding(): ?int;

    public function setLastBoarding(int $time): UserInterface;

    /**
     * @return Collection<int, UserLayerInterface>
     */
    public function getUserLayers(): Collection;

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

    /**
     * @return Collection<int, BuoyInterface>
     */
    public function getBuoys(): Collection;

    public function getSessiondata(): string;

    public function setSessiondata(string $sessiondata): UserInterface;

    public function getPrestige(): int;

    public function setPrestige(int $prestige): UserInterface;

    public function isOnline(): bool;

    public function getAlliance(): ?AllianceInterface;

    public function setAlliance(?AllianceInterface $alliance): UserInterface;

    public function isContactable(): bool;

    public function hasAward(int $awardId): bool;

    public function isNpc(): bool;

    public function getUserLock(): ?UserLockInterface;

    public function getPirateWrath(): ?PirateWrathInterface;

    public function setPirateWrath(?PirateWrathInterface $wrath): UserInterface;

    /**
     * @return Collection<int, UserTutorialInterface>
     */
    public function getTutorials(): Collection;

    /**
     * @return Collection<int, WormholeRestriction>
     */
    public function getWormholeRestrictions(): Collection;
}
