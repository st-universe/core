<?php

namespace Stu\Orm\Entity;

use Doctrine\Common\Collections\Collection;

interface RpgPlotArchivInterface
{
    public function getId(): int;

    public function getVersion(): ?string;

    public function getFormerId(): int;

    public function getUserId(): int;

    public function getTitle(): string;

    public function setTitle(string $title): RpgPlotArchivInterface;

    public function getDescription(): string;

    public function setDescription(string $description): RpgPlotArchivInterface;

    public function getStartDate(): int;

    public function setStartDate(int $startDate): RpgPlotArchivInterface;

    public function getEndDate(): ?int;

    public function setEndDate(?int $endDate): RpgPlotArchivInterface;

    public function isActive(): bool;

    /**
     * @return Collection<int, KnPostArchivInterface>
     */
    public function getPosts(): Collection;

    public function getMemberCount(): int;

    public function getPostingCount(): int;

    /**
     * @return Collection<int, RpgPlotMemberArchivInterface>
     */
    public function getMembers(): Collection;
}
