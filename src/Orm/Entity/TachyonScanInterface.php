<?php

namespace Stu\Orm\Entity;

use Stu\Orm\Entity\MapInterface;
use Stu\Orm\Entity\StarSystemMapInterface;
use Stu\Orm\Entity\UserInterface;

interface TachyonScanInterface
{
    public function getId(): int;

    public function getUserId(): int;

    public function getUser(): UserInterface;

    public function setUser(UserInterface $user): TachyonScanInterface;

    public function getScanTime(): int;
    
    public function setScanTime(int $scanTime): TachyonScanInterface;

    public function getMap(): ?MapInterface;

    public function setMap(?MapInterface $map): TachyonScanInterface;

    public function getStarsystemMap(): ?StarSystemMapInterface;

    public function setStarsystemMap(?StarSystemMapInterface $starsystem_map): TachyonScanInterface;
}
