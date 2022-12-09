<?php

namespace Stu\Orm\Entity;

use Doctrine\Common\Collections\Collection;

interface RpgPlotInterface
{
    public function getId(): int;

    public function getUserId(): int;

    public function getUser(): UserInterface;

    public function setUser(UserInterface $user): RpgPlotInterface;

    public function getTitle(): string;

    public function setTitle(string $title): RpgPlotInterface;

    public function getDescription(): string;

    public function setDescription(string $description): RpgPlotInterface;

    public function getStartDate(): int;

    public function setStartDate(int $startDate): RpgPlotInterface;

    public function getEndDate(): ?int;

    public function setEndDate(?int $endDate): RpgPlotInterface;

    public function isActive(): bool;

    public function getPosts(): Collection;

    public function getMemberCount(): int;

    public function getPostingCount(): int;

    /**
     * @return RpgPlotMemberInterface[]|Collection
     */
    public function getMembers(): Collection;
}
