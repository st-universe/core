<?php

namespace Stu\Orm\Entity;

interface UserRefererInterface
{

    public function getId(): int;

    public function getUser(): UserInterface;

    public function setUser(UserInterface $user): UserRefererInterface;

    public function getReferer(): string;

    public function setReferer(string $referer): UserRefererInterface;
}
