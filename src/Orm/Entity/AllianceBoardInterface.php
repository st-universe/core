<?php

namespace Stu\Orm\Entity;

use Doctrine\Common\Collections\Collection;

interface AllianceBoardInterface
{
    public function getId(): int;

    /**
     * @deprecated
     */
    public function getAllianceId(): int;

    /**
     * @deprecated
     */
    public function setAllianceId(int $allianceId): AllianceBoardInterface;

    public function getName(): string;

    public function setName(string $name): AllianceBoardInterface;

    public function getTopicCount(): int;

    public function getPostCount(): int;

    public function getLatestPost(): ?AllianceBoardPostInterface;

    public function getTopics(): Collection;

    public function getAlliance(): AllianceInterface;

    public function setAlliance(AllianceInterface $alliance): AllianceBoardInterface;
}