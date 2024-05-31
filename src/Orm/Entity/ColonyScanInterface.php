<?php

namespace Stu\Orm\Entity;

interface ColonyScanInterface
{
    public function getId(): int;

    public function getColonyId(): int;

    public function setColonyId(int $colonyid): ColonyScanInterface;

    public function getUserId(): int;

    public function setUserId(int $userid): ColonyScanInterface;

    public function getColonyUserId(): int;

    public function setColonyUserId(int $colonyuserid): ColonyScanInterface;

    public function getColonyName(): ?string;

    public function setColonyName(?string $colonyname): ColonyScanInterface;

    public function getColonyUserName(): string;

    public function setColonyUserName(string $colonyusername): ColonyScanInterface;

    public function getFieldData(): string;

    public function setFieldData(string $fieldData): ColonyScanInterface;

    public function getDate(): int;

    public function setDate(int $date): ColonyScanInterface;

    public function getColony(): ColonyInterface;

    public function setColony(ColonyInterface $colony): ColonyScanInterface;

    public function getUser(): UserInterface;

    public function setUser(UserInterface $user): ColonyScanInterface;

    public function isAbandoned(): bool;
}
