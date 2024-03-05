<?php

namespace Stu\Orm\Entity;

use Doctrine\Common\Collections\Collection;

interface KnPostInterface
{
    public function getId(): int;

    public function getTitle(): ?string;

    public function setTitle(string $title): KnPostInterface;

    public function getText(): string;

    public function setText(string $text): KnPostInterface;

    public function getDate(): int;

    public function setDate(int $date): KnPostInterface;

    public function getUsername(): string;

    public function setUsername(string $username): KnPostInterface;

    public function getUserId(): int;

    public function getdelUserId(): ?int;

    public function setdelUserId(?int $userid): KnPostInterface;

    public function getUser(): UserInterface;

    public function setUser(UserInterface $user): KnPostInterface;

    public function getEditDate(): int;

    public function setEditDate(int $editDate): KnPostInterface;

    public function getPlotId(): ?int;

    public function getRpgPlot(): ?RpgPlotInterface;

    public function setRpgPlot(?RpgPlotInterface $rpgPlot): KnPostInterface;

    /**
     * @return Collection<int, KnCommentInterface>
     */
    public function getComments(): Collection;

    /**
     * @return array<mixed>
     */
    public function getRatings(): array;

    /**
     * @param array<mixed> $ratings
     */
    public function setRatings(array $ratings): KnPostInterface;

    /**
     * Returns the relativ url to this posting
     */
    public function getUrl(): string;

    /**
     * @return Collection<int, KnCharactersInterface>
     */
    public function getKnCharacters(): ?Collection;
}
