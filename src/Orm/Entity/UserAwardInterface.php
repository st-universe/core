<?php

namespace Stu\Orm\Entity;

interface UserAwardInterface
{
    public function getId(): int;

    public function getUserId(): int;

    public function getUser(): UserInterface;

    public function setUser(UserInterface $user): UserAwardInterface;

    public function getAward(): AwardInterface;

    public function setAward(AwardInterface $award): UserAwardInterface;
}
