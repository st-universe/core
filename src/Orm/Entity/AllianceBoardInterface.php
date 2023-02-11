<?php

namespace Stu\Orm\Entity;

use Doctrine\Common\Collections\Collection;

interface AllianceBoardInterface
{
    public function getId(): int;

    public function getAllianceId(): int;

    public function getName(): string;

    public function setName(string $name): AllianceBoardInterface;

    public function getTopicCount(): int;

    public function getPostCount(): int;

    public function getLatestPost(): ?AllianceBoardPostInterface;

    /**
     * @return Collection<int, AllianceBoardTopicInterface>
     */
    public function getTopics(): Collection;

    public function getAlliance(): AllianceInterface;

    public function setAlliance(AllianceInterface $alliance): AllianceBoardInterface;

    /**
     * @return Collection<int, AllianceBoardPostInterface>
     */
    public function getPosts(): Collection;
}
