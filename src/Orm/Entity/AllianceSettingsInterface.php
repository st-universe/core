<?php

namespace Stu\Orm\Entity;

interface AllianceSettingsInterface
{
    public function getId(): int;

    public function getAlliance(): AllianceInterface;

    public function setAlliance(AllianceInterface $alliance): AllianceSettingsInterface;

    public function getSetting(): string;

    public function setSetting(string $setting): AllianceSettingsInterface;

    public function getValue(): string;

    public function setValue(string $value): AllianceSettingsInterface;
}
