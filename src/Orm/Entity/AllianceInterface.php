<?php

namespace Stu\Orm\Entity;

use Doctrine\Common\Collections\Collection;
use Stu\Component\Alliance\Exception\AllianceFounderNotSetException;

interface AllianceInterface
{
    public function getId(): int;

    public function getName(): string;

    public function setName(string $name): AllianceInterface;

    public function getDescription(): string;

    public function setDescription(string $description): AllianceInterface;

    public function getHomepage(): string;

    public function setHomepage(string $homepage): AllianceInterface;

    public function getDate(): int;

    public function setDate(int $date): AllianceInterface;

    public function getFactionId(): ?int;

    public function setFaction(?FactionInterface $faction): AllianceInterface;

    public function getAcceptApplications(): bool;

    /**
     * Returns `true` if a alliance avatar image has been set
     */
    public function hasAvatar(): bool;

    public function setAcceptApplications(bool $acceptApplications): AllianceInterface;

    public function getAvatar(): string;

    public function setAvatar(string $avatar): AllianceInterface;

    public function getRgbCode(): string;

    public function setRgbCode(string $rgbCode): AllianceInterface;

    /**
     * @throws AllianceFounderNotSetException
     */
    public function getFounder(): AllianceJobInterface;

    public function getSuccessor(): ?AllianceJobInterface;

    public function getDiplomatic(): ?AllianceJobInterface;

    /**
     * @return Collection<int, UserInterface>
     */
    public function getMembers(): Collection;

    /**
     * Returns `true` if the founder is a npc
     */
    public function isNpcAlliance(): bool;

    /**
     * Returns the alliance jobs, indexed by type
     *
     * @return Collection<int, AllianceJobInterface>
     */
    public function getJobs(): Collection;

    public function hasTranslation(): bool;
}
