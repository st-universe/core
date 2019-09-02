<?php

namespace Stu\Orm\Entity;

use Doctrine\Common\Collections\Collection;

interface AllianceBoardInterface
{
    public function getId(): int;

    public function getAllianceId(): int;

    public function setAllianceId(int $allianceId): AllianceBoardInterface;

    public function getName(): string;

    public function setName(string $name): AllianceBoardInterface;

    public function getTopicCount(): int;

    public function getPostCount(): int;

    public function getLatestPost(): ?AllianceBoardPostInterface;

    public function getTopics(): Collection;
}