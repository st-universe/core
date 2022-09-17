<?php

namespace Stu\Orm\Entity;

interface GameTurnStatsInterface
{
    public function getId(): int;

    public function getUserCount(): int;

    public function setUserCount(int $userCount): GameTurnStatsInterface;

    public function getLogins24h(): int;

    public function setLogins24h(int $logins24h): GameTurnStatsInterface;

    public function getVacationCount(): int;

    public function setVacationCount(int $vacationCount): GameTurnStatsInterface;

    public function getShipCount(): int;

    public function setShipCount(int $shipCount): GameTurnStatsInterface;

    public function getKnCount(): int;

    public function setKnCount(int $knCount): GameTurnStatsInterface;

    public function getFlightSig24h(): int;

    public function setFlightSig24h(int $flightSig24h): GameTurnStatsInterface;
}
