<?php

namespace Stu\Orm\Entity;

use UserData;

interface ResearchedInterface
{
    public function getId(): int;

    public function getUserId(): int;

    public function setUserId(int $userId): ResearchedInterface;

    public function getActive(): int;

    public function setActive(int $active): ResearchedInterface;

    public function getFinished(): int;

    public function setFinished(int $finished): ResearchedInterface;

    public function setResearch(ResearchInterface $research): ResearchedInterface;

    public function getResearch(): ResearchInterface;

    public function getUser(): UserData;
}