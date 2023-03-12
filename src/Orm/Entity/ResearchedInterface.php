<?php

namespace Stu\Orm\Entity;

interface ResearchedInterface
{
    public function getId(): int;

    public function getUserId(): int;

    public function getActive(): int;

    public function setActive(int $active): ResearchedInterface;

    public function getFinished(): int;

    public function setFinished(int $finished): ResearchedInterface;

    public function setResearch(ResearchInterface $research): ResearchedInterface;

    public function getResearch(): ResearchInterface;

    public function getUser(): UserInterface;

    public function setUser(UserInterface $user): ResearchedInterface;

    public function getResearchId(): int;

    public function getProgress(): int;
}
