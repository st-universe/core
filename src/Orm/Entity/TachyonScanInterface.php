<?php

namespace Stu\Orm\Entity;

interface TachyonScanInterface
{
    public function getId(): int;

    public function getUserId(): int;

    public function getUser(): UserInterface;

    public function setUser(UserInterface $user): TachyonScanInterface;

    public function getScanTime(): int;

    public function setScanTime(int $scanTime): TachyonScanInterface;

    public function getLocation(): LocationInterface;

    public function setLocation(LocationInterface $location): TachyonScanInterface;
}
