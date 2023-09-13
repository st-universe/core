<?php

namespace Stu\Orm\Entity;

use Doctrine\Common\Collections\Collection;

interface KnPostArchivInterface
{
    public function getId(): int;

    public function getVersion(): ?string;

    public function getFormerId(): int;

    public function getTitle(): ?string;

    public function setTitle(string $title): KnPostArchivInterface;

    public function getText(): string;

    public function setText(string $text): KnPostArchivInterface;

    public function getDate(): int;

    public function setDate(int $date): KnPostArchivInterface;

    public function getUsername(): string;

    public function setUsername(string $username): KnPostArchivInterface;

    public function getUserId(): ?int;

    public function getdelUserId(): ?int;

    public function setdelUserId(?int $userid): KnPostArchivInterface;

    public function getEditDate(): int;

    public function setEditDate(int $editDate): KnPostArchivInterface;

    public function getPlotId(): ?int;

    public function getRpgPlot(): ?RpgPlotArchivInterface;

    public function setRpgPlot(?RpgPlotArchivInterface $rpgPlot): KnPostArchivInterface;

    /**
     * @return Collection<int, KnCommentArchivInterface>
     */
    public function getComments(): Collection;

    /**
     * @return array<mixed>
     */
    public function getRatings(): array;

    /**
     * @param array<mixed> $ratings
     */
    public function setRatings(array $ratings): KnPostArchivInterface;

    /**
     * Returns the relativ url to this posting
     */
    public function getUrl(): string;
}
