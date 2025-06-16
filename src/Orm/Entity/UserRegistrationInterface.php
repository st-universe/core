<?php

namespace Stu\Orm\Entity;

interface UserRegistrationInterface
{
    public function getLogin(): string;

    public function setLogin(string $login): UserRegistrationInterface;

    public function getPassword(): string;

    public function setPassword(string $password): UserRegistrationInterface;

    public function getSmsCode(): ?string;

    public function setSmsCode(?string $code): UserRegistrationInterface;

    public function getEmail(): string;

    public function setEmail(string $email): UserRegistrationInterface;

    public function getMobile(): ?string;

    public function setMobile(?string $mobile): UserRegistrationInterface;

    public function getCreationDate(): int;

    public function setCreationDate(int $creationDate): UserRegistrationInterface;

    public function getDeletionMark(): int;

    public function setDeletionMark(int $deletionMark): UserRegistrationInterface;

    public function getPasswordToken(): string;

    public function setPasswordToken(string $password_token): UserRegistrationInterface;

    public function getReferer(): ?UserRefererInterface;

    public function setReferer(?UserRefererInterface $referer): UserRegistrationInterface;

    public function getSmsSended(): int;

    public function setSmsSended(int $smsSended): UserRegistrationInterface;
}
