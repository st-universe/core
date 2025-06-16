<?php

namespace Stu\Orm\Entity;

interface UserRefererInterface
{

    public function getId(): int;

    public function getUserRegistration(): UserRegistrationInterface;

    public function setUserRegistration(UserRegistrationInterface $registration): UserRefererInterface;

    public function getReferer(): string;

    public function setReferer(string $referer): UserRefererInterface;
}
