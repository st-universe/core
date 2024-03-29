<?php

namespace Stu\Orm\Entity;

interface BuoyInterface
{
    public function getId(): int;

    public function getUserId(): int;

    public function setUserId(int $user_id): void;

    public function getText(): string;

    public function setText(string $text): void;

    public function getMapId(): ?int;

    public function setMapId(?int $map_id): void;

    public function getSysMapId(): ?int;

    public function setSysMapId(?int $sys_map_id): void;

    public function getMap(): ?MapInterface;

    public function setMap(?MapInterface $map): void;

    public function getSystemMap(): ?StarSystemMapInterface;

    public function setSystemMap(?StarSystemMapInterface $systemMap): void;

    public function getUser(): UserInterface;

    public function setUser(UserInterface $user): void;
}