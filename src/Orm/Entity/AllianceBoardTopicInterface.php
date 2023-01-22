<?php

namespace Stu\Orm\Entity;

use Doctrine\Common\Collections\Collection;

interface AllianceBoardTopicInterface
{
    public function getId(): int;

    public function getBoardId(): int;

    public function setBoardId(int $boardId): AllianceBoardTopicInterface;

    public function getAllianceId(): int;

    public function getName(): string;

    public function setName(string $name): AllianceBoardTopicInterface;

    public function getLastPostDate(): int;

    public function setLastPostDate(int $lastPostDate): AllianceBoardTopicInterface;

    public function getUserId(): int;

    public function getSticky(): bool;

    public function setSticky(bool $sticky): AllianceBoardTopicInterface;

    public function getUser(): UserInterface;

    public function setUser(UserInterface $user): AllianceBoardTopicInterface;

    /**
     * @return null|array<int>
     */
    public function getPages(): ?array;

    public function getPostCount(): int;

    public function getLatestPost(): ?AllianceBoardPostInterface;

    public function getBoard(): AllianceBoardInterface;

    public function setBoard(AllianceBoardInterface $board): AllianceBoardTopicInterface;

    /**
     * @return Collection<int, AllianceBoardPostInterface>
     */
    public function getPosts(): Collection;

    public function getAlliance(): AllianceInterface;

    public function setAlliance(AllianceInterface $alliance): AllianceBoardTopicInterface;
}
