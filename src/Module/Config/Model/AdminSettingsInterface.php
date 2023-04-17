<?php

namespace Stu\Module\Config\Model;

interface AdminSettingsInterface
{
    public function getId(): int;

    public function getEmail(): string;
}
