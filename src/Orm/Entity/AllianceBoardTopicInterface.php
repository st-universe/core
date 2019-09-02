<?php

namespace Stu\Orm\Entity;

use Doctrine\Common\Collections\Collection;
use User;

interface AllianceBoardTopicInterface
{
    public function getId(): int;

    public function getBoardId(): int;

    public function setBoardId(int $boardId): AllianceBoardTopicInterface;

    public function getAllianceId(): int;

    public function setAllianceId(int $allianceId): AllianceBoardTopicInterface;

    public function getName(): string;

    public function setName(string $name): AllianceBoardTopicInterface;

    public function getLastPostDate(): int;

    public function setLastPostDate(int $lastPostDate): AllianceBoardTopicInterface;

    public function getUserId(): int;

    public function setUserId(int $userId): AllianceBoardTopicInterface;

    public function getSticky(): bool;

    public function setSticky(bool $sticky): AllianceBoardTopicInterface;

    public function getUser(): User;

    public function getPages(): ?array;

    public function getPostCount(): int;

    public function getLatestPost(): ?AllianceBoardPostInterface;

    public function getBoard(): AllianceBoardInterface;

    public function setBoard(AllianceBoardInterface $board): AllianceBoardTopicInterface;

    public function getPosts(): Collection;
}