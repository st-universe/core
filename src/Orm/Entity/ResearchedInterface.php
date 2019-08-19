<?php

namespace Stu\Orm\Entity;

interface ResearchedInterface
{
    public function getId(): int;

    public function getResearchId(): int;

    public function setResearchId(int $reserchId): ResearchedInterface;

    public function getUserId(): int;

    public function setUserId(int $userId): ResearchedInterface;

    public function getActive(): int;

    public function setActive(int $active): ResearchedInterface;

    public function getFinished(): int;

    public function setFinished(int $finished): ResearchedInterface;
}