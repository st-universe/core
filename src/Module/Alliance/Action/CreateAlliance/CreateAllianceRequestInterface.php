<?php

namespace Stu\Module\Alliance\Action\CreateAlliance;

interface CreateAllianceRequestInterface
{
    public function getName(): string;

    public function getDescription(): string;

    public function getFactionMode(): int;
}